<?php

namespace Drupal\thunder_updater;

use Drupal\config_update\ConfigListInterface;
use Drupal\config_update\ConfigRevertInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\thunder_updater\Diff3\Diff3;
use Psr\Log\LoggerInterface;

/**
 * Thunder Updater service to apply update of distribution modules.
 *
 * @package Drupal\thunder_updater
 */
class Updater {

  use StringTranslationTrait;

  /**
   * Logger for "thunder-updater" channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The config differ.
   *
   * @var UpdaterConfigDiffer
   */
  protected $configDiffer;

  /**
   * The config lister.
   *
   * @var \Drupal\config_update\ConfigListInterface
   */
  protected $configList;

  /**
   * The config reverter.
   *
   * @var \Drupal\config_update\ConfigRevertInterface
   */
  protected $configReverter;

  /**
   * Storage used for merged files.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $mergeStorage;

  /**
   * Reverter that will use merged configuration files.
   *
   * @var \Drupal\config_update\ConfigRevertInterface
   */
  protected $mergedReverter;

  /**
   * Service used to manipulate patch files.
   *
   * @var UpdaterPatchHandler
   */
  protected $patchHandler;

  /**
   * Possible patch options.
   *
   * Types:
   * normal - will make patch between base config vs. active config.
   * reverse - will make patch between active config vs. base config.
   *
   * Base config are actually configuration files in config/install folder of
   * modules that is processed.
   */
  const PATCH_TYPE_NORMAL = 'normal';
  const PATCH_TYPE_REVERSE = 'reverse';

  /**
   * {@inheritdoc}
   */
  public function __construct(
    LoggerInterface $logger,
    UpdaterConfigDiffer $configDiffer,
    ConfigListInterface $configList,
    ConfigRevertInterface $configReverter,
    StorageInterface $mergeStorage,
    ConfigRevertInterface $mergedReverter,
    UpdaterPatchHandler $patchHandler
  ) {
    $this->logger = $logger;
    $this->configDiffer = $configDiffer;
    $this->configList = $configList;
    $this->configReverter = $configReverter;
    $this->mergeStorage = $mergeStorage;
    $this->mergedReverter = $mergedReverter;
    $this->patchHandler = $patchHandler;
  }

  /**
   * Generate patch from changed configuration.
   *
   * It compares Base vs. Active configuration and creates patch with defined
   * name in patch folder.
   *
   * @param string $moduleName
   *   Module name that will be used to generate patch for it.
   * @param string $versionName
   *   Suffix name for patch. Usually version number.
   * @param string $patchType
   *   Defined how diff will be calculated.
   *
   * @return string|bool
   *   Rendering generated patch file name or FALSE if patch is empty.
   */
  public function generateUpdate($moduleName, $versionName, $patchType = self::PATCH_TYPE_REVERSE) {
    $updatePatch = [];

    $configurationLists = $this->configList->listConfig('module', $moduleName);

    // Get required and optional configuration names.
    $configNames = $this->getConfigNames(array_merge($configurationLists[1], $configurationLists[2]));

    foreach ($configNames as $configName) {
      $configDiff = $this->getConfigDiff($configName, $patchType);
      if (!empty($configDiff)) {
        $updatePatch[$configName->getFullName()] = $configDiff;
      }
    }

    if (!empty($updatePatch)) {
      $patchFilePath = $this->patchHandler->savePatch($moduleName, $versionName, $updatePatch);

      return $patchFilePath;
    }

    return FALSE;
  }

  /**
   * Get diff for single configuration.
   *
   * @param \Drupal\thunder_updater\ConfigName $configName
   *   Configuration name.
   * @param string $patchType
   *   Defined how diff will be calculated.
   *
   * @return \Drupal\Component\Diff\Engine\DiffOp[]
   *   Return diff edits.
   */
  protected function getConfigDiff(ConfigName $configName, $patchType = self::PATCH_TYPE_REVERSE) {
    $activeConfig = $this->getConfigFrom(
      $this->configReverter->getFromActive($configName->getType(), $configName->getName())
    );

    $baseConfig = $this->getConfigFrom(
      $this->configReverter->getFromExtension($configName->getType(), $configName->getName())
    );

    if (!$this->configDiffer->same($baseConfig, $activeConfig)) {
      if ($patchType == static::PATCH_TYPE_NORMAL) {
        $updateDiff = $this->configDiffer->diff(
          $baseConfig,
          $activeConfig
        );
      }
      else {
        $updateDiff = $this->configDiffer->diff(
          $activeConfig,
          $baseConfig
        );
      }

      return $updateDiff->getEdits();
    }

    return [];
  }

  /**
   * Get list of ConfigName instances from list of config names.
   *
   * @param array $configList
   *   List of config names (string).
   *
   * @return array
   *   List of ConfigName instances crated from string config name.
   */
  protected function getConfigNames($configList) {
    $configNames = [];

    foreach ($configList as $configFile) {
      $configNames[] = ConfigName::createByFullName($configFile);
    }

    return $configNames;
  }

  /**
   * Ensure that configuration is always array.
   *
   * @param mixed $configData
   *   Configuration data that should be checked.
   *
   * @return array
   *   Returns configuration data array if it's not empty configuration,
   *   otherwise returns empty array.
   */
  protected function getConfigFrom($configData) {
    if (empty($configData)) {
      return [];
    }

    return $configData;
  }

  /**
   * Apply patch for module and version defined.
   *
   * @param string $moduleName
   *   Module name that will be used to generate patch for it.
   * @param string $versions
   *   Suffix names for patch. Usually version number. Comma separated.
   *
   * @return array
   *   Returns array with executed actions for configs.
   *
   * @throws \Exception
   *   When it's not possible to apply patch.
   */
  public function executeUpdate($moduleName, $versions) {
    $conflictedConfigs = [];
    $configNames = [];

    $reverseEdits = $this->patchHandler->getPatches($moduleName, $versions);
    foreach ($reverseEdits as $configFullName => $baseEdits) {
      $configName = ConfigName::createByFullName($configFullName);
      $configNames[] = $configName;

      $baseConfig = $this->generateBaseConfig($baseEdits);
      $activeConfig = $this->getConfigFrom(
        $this->configReverter->getFromActive($configName->getType(), $configName->getName())
      );
      $updateConfig = $this->getConfigFrom(
        $this->configReverter->getFromExtension($configName->getType(), $configName->getName())
      );

      $liveDiff = $this->configDiffer->diff(
        $baseConfig,
        $activeConfig
      );

      $updateDiff = $this->configDiffer->diff(
        $baseConfig,
        $updateConfig
      );

      // Apply 3-way Diff.
      $diff3 = new Diff3();
      $diff3Result = $diff3->doDiff3($liveDiff->getEdits(), $updateDiff->getEdits());
      $mergedResult = $diff3->mergedOutput(
        $diff3Result,
        $this->t('Live Configuration'),
        $this->t('Thunder Update Configuration')
      );

      if (!$diff3->isCleanlyMerged()) {
        $conflictedConfigs[] = $configName->getFullName();
      }

      $ymlNormalized = $this->configDiffer->formatToConfig($mergedResult);

      $this->mergeStorage->write($configFullName, $ymlNormalized);
    }

    if (!empty($conflictedConfigs)) {
      throw new \Exception('Unable to auto merge configuration. Please make update manually');
    }

    $updateReport = $this->importConfigurations($this->mergedReverter, $configNames, $moduleName);

    // CleanUp temporally merged files after apply is finished.
    foreach (array_keys($reverseEdits) as $configFullName) {
      $this->mergeStorage->delete($configFullName);
    }

    return $updateReport;
  }

  /**
   * Import listed configurations for module.
   *
   * If configuration is new it will be imported, if it's existing will be
   * updated with configuration from file.
   *
   * @param ConfigRevertInterface $configReverter
   *   Return config reverter.
   * @param ConfigName[] $configNames
   *   List of configuration names.
   * @param string $moduleName
   *   Module name that will be used to generate patch for it.
   *
   * @return array
   *   Returns status of configuration import.
   */
  public function importConfigurations(ConfigRevertInterface $configReverter, array $configNames, $moduleName) {
    $updateReport = [];

    foreach ($configNames as $configName) {
      $configFullName = $configName->getFullName();

      if (empty($configName->getType())) {
        $this->logger->info($this->t('Skipped: :config_name', [':config_name' => $configFullName]));
        $updateReport[] = [
          'config' => $configFullName,
          'action' => $this->t('Skipped'),
        ];

        continue;
      }

      try {
        $existingConfig = $configReverter
          ->getFromActive($configName->getType(), $configName->getName());

        if (empty($existingConfig)) {

          if ($this->isOptionalConfig($configName, $moduleName)) {
            $this->logger->info($this->t('Skipped optional configuration: :config_name', [':config_name' => $configFullName]));
            $updateReport[] = [
              'config' => $configFullName,
              'action' => $this->t('Skipped optional config'),
            ];

            continue;
          }

          $configReverter
            ->import($configName->getType(), $configName->getName());

          $this->logger->info($this->t('Imported: :config_name', [':config_name' => $configFullName]));
          $updateReport[] = [
            'config' => $configFullName,
            'action' => $this->t('Imported'),
          ];
        }
        else {
          $configReverter
            ->revert($configName->getType(), $configName->getName());

          $this->logger->info($this->t('Updated: :config_name', [':config_name' => $configFullName]));
          $updateReport[] = [
            'config' => $configFullName,
            'action' => $this->t('Updated'),
          ];
        }
      }
      catch (\Exception $e) {
        $this->logger->info($this->t('Not Imported: :config_name', [':config_name' => $configFullName]));
        $updateReport[] = [
          'config' => $configFullName,
          'action' => $this->t('Not Imported'),
        ];
      }
    }

    return $updateReport;
  }

  /**
   * Generate base configuration from provided patch edits.
   *
   * @param array $reverseEdits
   *   Patch edits generated during patch export.
   *
   * @return array
   *   Returns generated base configuration.
   */
  protected function generateBaseConfig($reverseEdits) {
    $storeEdits = serialize($reverseEdits);

    $diff3 = new Diff3();
    $diff3Result = $diff3->doDiff3(unserialize($storeEdits), unserialize($storeEdits));
    $mergedResult = $diff3->mergedOutput(
      $diff3Result,
      $this->t('Base Configuration'),
      $this->t('Base Configuration')
    );

    return $this->configDiffer->formatToConfig($mergedResult);
  }

  /**
   * Checks if provided configuration name is optional or not.
   *
   * @param \Drupal\thunder_updater\ConfigName $configName
   *   Configuration name object.
   * @param string $moduleName
   *   Module name.
   *
   * @return bool
   *   Returns if configuration is optional for defined module.
   */
  protected function isOptionalConfig(ConfigName $configName, $moduleName) {
    $configurationLists = $this->configList->listConfig('module', $moduleName);

    // 2nd list in configuration lists is list of optional configurations.
    return in_array($configName->getFullName(), $configurationLists[2]);
  }

}
