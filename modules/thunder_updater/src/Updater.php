<?php

namespace Drupal\thunder_updater;

use Drupal\config_update\ConfigListInterface;
use Drupal\config_update\ConfigReverter;
use Drupal\config_update\ConfigRevertInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\NullStorage;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\thunder_updater\Diff3\Diff3;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Updater.
 *
 * @package Drupal\thunder_updater
 */
class Updater {

  use StringTranslationTrait;

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
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * Config Storage, that uses default active and install configuration.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $configStorage;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Temporally sub-directory where exports will be saved before applying them.
   *
   * It will be used to generate temporal folder with that prefix.
   *
   * @var string
   */
  protected $exportFolderName = 'thunder_updater_export';

  /**
   * Configuration folder in module, used to store patch files.
   *
   * @var string
   */
  protected $configUpdateFolder = 'config/update';

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
   * Storage used for merged files.
   *
   * It should be access over: mergeFileStorage() method.
   *
   * @var StorageInterface
   */
  protected $mergeFileStorage;

  /**
   * Reverter that will use merged configuration files.
   *
   * @var ConfigReverter
   */
  protected $mergedConfigReverter;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    UpdaterConfigDiffer $config_diff,
    ConfigListInterface $config_list,
    ConfigRevertInterface $config_update,
    ModuleHandlerInterface $module_handler,
    EntityTypeManagerInterface $entity_manager,
    StorageInterface $active_config_storage,
    ConfigFactoryInterface $config_factory,
    EventDispatcherInterface $dispatcher
  ) {
    $this->configDiffer = $config_diff;
    $this->configList = $config_list;
    $this->configReverter = $config_update;
    $this->moduleHandler = $module_handler;
    $this->entityManager = $entity_manager;
    $this->configStorage = $active_config_storage;
    $this->configFactory = $config_factory;
    $this->eventDispatcher = $dispatcher;
  }

  /**
   * Generate patch from changed configuration.
   *
   * It compares Base vs. Active configuration and creates patch with defined
   * name in patch folder.
   *
   * @param string $module_name
   *   Module name that will be used to generate patch for it.
   * @param string $version_name
   *   Suffix name for patch. Usually version number.
   * @param string $patch_type
   *   Defined how diff will be calculated.
   *
   * @return string|bool
   *   Rendering generated patch file name or FALSE if patch is empty.
   */
  public function generatePatch($module_name, $version_name, $patch_type = self::PATCH_TYPE_REVERSE) {
    $updatePatch = [];

    $configurationLists = $this->configList->listConfig('module', $module_name);
    $configNames = $this->getConfigNames($configurationLists[1]);

    foreach ($configNames as $configName) {
      $activeConfig = $this->getConfigFrom(
        $this->configReverter->getFromActive($configName->getType(), $configName->getName())
      );

      $baseConfig = $this->getConfigFrom(
        $this->configReverter->getFromExtension($configName->getType(), $configName->getName())
      );

      if (!$this->configDiffer->same($baseConfig, $activeConfig)) {
        if ($patch_type == static::PATCH_TYPE_NORMAL) {
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

        $updatePatch[$configName->getFullName()] = $updateDiff->getEdits();
      }
    }

    if (!empty($updatePatch)) {
      $patchFilePath = $this->getPatchFileName($module_name, $version_name);
      file_put_contents($patchFilePath, gzencode(serialize($updatePatch)));

      return $patchFilePath;
    }

    return FALSE;
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
   * Create patch file name based on version and module name.
   *
   * @param string $module_name
   *   Module name that will be used to generate patch for it.
   * @param string $version_name
   *   Suffix name for patch. Usually version number.
   *
   * @return string
   *   Returns patch file path with file name.
   */
  protected function getPatchFileName($module_name, $version_name) {
    $modulePath = $this->moduleHandler->getModule($module_name)->getPath();

    return $modulePath . '/' . $this->configUpdateFolder . '/' . urlencode($module_name . '-' . $version_name) . '.gz';
  }

  /**
   * Apply patch for module and version defined.
   *
   * @param string $module_name
   *   Module name that will be used to generate patch for it.
   * @param string $version_name
   *   Suffix name for patch. Usually version number.
   *
   * @return array
   *   Returns array with executed actions for configs.
   *
   * @throws \Exception
   *   When it's not possible to apply patch.
   */
  public function applyPatch($module_name, $version_name) {
    $updateReport = [];

    $conflictedConfigurations = [];
    $configNames = [];

    $reverseEdits = $this->getPatchEdits($module_name, $version_name);
    foreach ($reverseEdits as $configFullName => $updateEdits) {
      $configName = ConfigName::createByFullName($configFullName);
      $configNames[] = $configName;

      $baseConfig = $this->generateBaseConfig($updateEdits);
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
        $conflictedConfigurations[] = $configName->getFullName();
      }

      $ymlNormalized = $this->configDiffer->formatToConfig($mergedResult);

      $this->mergeFileStorage()->write($configFullName, $ymlNormalized);
    }

    if (!empty($conflictedConfigurations)) {
      throw new \Exception('Unable to auto merge configuration. Please make update manually');
    }
    else {
      $mergedConfigReverter = $this->getMergedConfigReverter();
      foreach ($configNames as $configName) {
        $configFullName = $configName->getFullName();

        if (empty($configName->getType())) {
          \Drupal::logger('thunder-updater')
            ->info($this->t('Skipped: :config_name', [':config_name' => $configFullName]));
          $updateReport[] = [
            'config' => $configFullName,
            'action' => $this->t('Skipped'),
          ];

          continue;
        }

        try {
          $existingConfig = $mergedConfigReverter
            ->getFromActive($configName->getType(), $configName->getName());
          if (empty($existingConfig)) {
            $mergedConfigReverter
              ->import($configName->getType(), $configName->getName());

            \Drupal::logger('thunder-updater')
              ->info($this->t('Imported: :config_name', [':config_name' => $configFullName]));
            $updateReport[] = [
              'config' => $configFullName,
              'action' => $this->t('Imported'),
            ];
          }
          else {
            $mergedConfigReverter
              ->revert($configName->getType(), $configName->getName());

            \Drupal::logger('thunder-updater')
              ->info($this->t('Updated: :config_name', [':config_name' => $configFullName]));
            $updateReport[] = [
              'config' => $configFullName,
              'action' => $this->t('Updated'),
            ];
          }
        }
        catch (\Exception $e) {
          \Drupal::logger('thunder-updater')
            ->info($this->t('Not Imported: :config_name', [':config_name' => $configFullName]));
          $updateReport[] = [
            'config' => $configFullName,
            'action' => $this->t('Not Imported'),
          ];
        }
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
   * Get edits provided by patch for module.
   *
   * @param string $module_name
   *   Module name that will be used to generate patch for it.
   * @param string $version_name
   *   Suffix name for patch. Usually version number.
   *
   * @return mixed
   *   Returns list of edits per configuration from patch file.
   *
   * @throws \Exception
   *   When patch file is not available.
   */
  protected function getPatchEdits($module_name, $version_name) {
    $patchFilename = $this->getPatchFileName($module_name, $version_name);

    if (!is_file($patchFilename)) {
      throw new \Exception('Patch file: ' . $patchFilename . ' does not exist.');
    }

    $gzipContent = file_get_contents($patchFilename);

    return unserialize(gzdecode($gzipContent));
  }

  /**
   * Get merge config reverter.
   *
   * @return ConfigReverter
   *   Return config reverter.
   */
  protected function getMergedConfigReverter() {
    if (!$this->mergedConfigReverter) {
      $this->mergedConfigReverter = new ConfigReverter(
        $this->entityManager,
        $this->configStorage,
        $this->mergeFileStorage(),
        new NullStorage(),
        $this->configFactory,
        $this->eventDispatcher
      );
    }

    return $this->mergedConfigReverter;
  }

  /**
   * Get merge file storage.
   *
   * @return StorageInterface
   *   Returns file storage where merged configuration will be exported.
   *
   * @throws \Exception
   *   Throws exception when it's not possible to crate temporally folder.
   */
  protected function mergeFileStorage() {
    if (!$this->mergeFileStorage) {
      $tmpDir = tempnam(sys_get_temp_dir(), $this->exportFolderName);
      if (is_file($tmpDir)) {
        unlink($tmpDir);
      }

      mkdir($tmpDir);
      if (!is_dir($tmpDir)) {
        throw new \Exception('Unable to create temporally folder to export merged files.');
      }

      $this->mergeFileStorage = new FileStorage($tmpDir);
    }

    return $this->mergeFileStorage;
  }

}
