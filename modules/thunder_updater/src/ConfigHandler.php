<?php

namespace Drupal\thunder_updater;

use Drupal\Component\Serialization\SerializationInterface;
use Drupal\config_update\ConfigDiffInterface;
use Drupal\config_update\ConfigListInterface;
use Drupal\config_update\ConfigRevertInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Configuration handler.
 *
 * TODO: Create UpdateDefinition class to handle configuration update entry.
 *
 * @package Drupal\thunder_updater
 */
class ConfigHandler {

  use StringTranslationTrait;

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
   * The config differ.
   *
   * @var ReversibleConfigDiffer
   */
  protected $configDiffer;

  /**
   * Config diff transformer service.
   *
   * @var \Drupal\thunder_updater\ConfigDiffTransformer
   */
  protected $configDiffTransformer;

  /**
   * Module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Yaml serializer.
   *
   * @var \Drupal\Component\Serialization\SerializationInterface
   */
  protected $serializer;

  /**
   * List of configuration parameters that will be stripped out.
   *
   * @var array
   */
  protected $stripConfigParams = ['dependencies'];

  /**
   * Default path for configuration update files.
   *
   * @var string
   */
  protected $baseUpdatePath = '/config/update';

  /**
   * Config handler constructor.
   *
   * @param \Drupal\config_update\ConfigListInterface $configList
   *   Config list service.
   * @param \Drupal\config_update\ConfigRevertInterface $configReverter
   *   Config reverter service.
   * @param \Drupal\config_update\ConfigDiffInterface $configDiffer
   *   Config differ service.
   * @param \Drupal\thunder_updater\ConfigDiffTransformer $configDiffTransformer
   *   Configuration transformer for diffing.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module handler service.
   * @param \Drupal\Component\Serialization\SerializationInterface $yamlSerializer
   *   Array serializer service.
   */
  public function __construct(ConfigListInterface $configList, ConfigRevertInterface $configReverter, ConfigDiffInterface $configDiffer, ConfigDiffTransformer $configDiffTransformer, ModuleHandlerInterface $moduleHandler, SerializationInterface $yamlSerializer) {
    $this->configList = $configList;
    $this->configReverter = $configReverter;
    $this->configDiffer = $configDiffer;
    $this->configDiffTransformer = $configDiffTransformer;
    $this->moduleHandler = $moduleHandler;
    $this->serializer = $yamlSerializer;
  }

  /**
   * Generate patch from changed configuration.
   *
   * It compares Base vs. Active configuration and creates patch with defined
   * name in patch folder.
   *
   * @param string[] $moduleNames
   *   Module name that will be used to generate patch for it.
   *
   * @return string|bool
   *   Rendering generated patch file name or FALSE if patch is empty.
   */
  public function generatePatchFile(array $moduleNames = []) {
    $updatePatch = [];

    foreach ($moduleNames as $moduleName) {
      $configurationLists = $this->configList->listConfig('module', $moduleName);

      // Get required and optional configuration names.
      $moduleConfigNames = array_merge($configurationLists[1], $configurationLists[2]);

      $configNames = $this->getConfigNames(array_intersect($moduleConfigNames, $configurationLists[0]));
      foreach ($configNames as $configName) {
        $configDiff = $this->getConfigDiff($configName);
        $configDiff = $this->filterDiff($configDiff);
        if (!empty($configDiff)) {
          $updatePatch[$configName->getFullName()] = [
            'expected_config' => $this->getExpectedConfig($configDiff),
            'update_actions' => $this->getUpdateConfig($configDiff),
          ];
        }
      }
    }

    return $updatePatch ? $this->serializer->encode($updatePatch) : FALSE;
  }

  /**
   * Get diff for single configuration.
   *
   * @param \Drupal\thunder_updater\ConfigName $configName
   *   Configuration name.
   *
   * @return \Drupal\Component\Diff\Engine\DiffOp[]
   *   Return diff edits.
   */
  protected function getConfigDiff(ConfigName $configName) {
    $oldConfig = $this->getConfigFrom(
      $this->configReverter->getFromActive($configName->getType(), $configName->getName())
    );

    $newConfig = $this->getConfigFrom(
      $this->configReverter->getFromExtension($configName->getType(), $configName->getName())
    );

    if (!$this->configDiffer->same($newConfig, $oldConfig)) {
      $updateDiff = $this->configDiffer->diff(
        $oldConfig,
        $newConfig
      );

      /** @var \Drupal\Component\Diff\Engine\DiffOp[] $edits */
      return $updateDiff->getEdits();
    }

    return [];
  }

  /**
   * Filter diffs that are not relevant, where configuration is equal.
   *
   * @param array $diffs
   *   List of diff edits.
   *
   * @return array
   *   Return list of filtered diffs.
   */
  protected function filterDiff(array $diffs) {
    return array_filter(
      $diffs,
      function ($diffOp) {
        return $diffOp->type != 'copy';
      }
    );
  }

  /**
   * Get list of expected configuration on not updated system.
   *
   * @param array $diffs
   *   List of diff edits.
   *
   * @return array
   *   Return configuration array that is expected on old system.
   */
  protected function getExpectedConfig(array $diffs) {
    $listExpected = [];

    foreach ($diffs as $diffOp) {
      if (!empty($diffOp->orig)) {
        $listExpected = array_merge($listExpected, $diffOp->orig);
      }
    }

    return $this->configDiffTransformer->reverseTransform($listExpected);
  }

  /**
   * Get list of configuration changes with change action (add, delete, change).
   *
   * @param array $diffs
   *   List of diff edits.
   *
   * @return array
   *   Return configuration array that should be applied.
   */
  protected function getUpdateConfig(array $diffs) {
    $listUpdate = [
      'add' => [],
      'change' => [],
      'delete' => [],
    ];

    foreach ($diffs as $diffOp) {
      if (!empty($diffOp->closing)) {
        if ($diffOp->type === 'change') {
          $removableEdits = $this->getRemovableEdits($diffOp->orig, $diffOp->closing);
          if (!empty($removableEdits)) {
            $listUpdate['delete'] = array_merge($listUpdate['delete'], $removableEdits);
          }
        }

        $listUpdate[$diffOp->type] = array_merge($listUpdate[$diffOp->type], $diffOp->closing);
      }
      elseif ($diffOp->type === 'delete' && !empty($diffOp->orig)) {
        $listUpdate[$diffOp->type] = array_merge($listUpdate[$diffOp->type], $diffOp->orig);
      }
    }

    $listUpdate = array_filter($listUpdate);
    foreach ($listUpdate as $action => $edits) {
      $listUpdate[$action] = $this->configDiffTransformer->reverseTransform($edits);
    }

    return $listUpdate;
  }

  /**
   * Get edits that should be removed before applying change action.
   *
   * @param array $originalEdits
   *   Original list of edits for compare.
   * @param array $newEdits
   *   New list of edits for compare.
   *
   * @return array
   *   Returns list of edits that should be removed.
   */
  protected function getRemovableEdits(array $originalEdits, array $newEdits) {
    $additionalEdits = array_udiff($originalEdits, $newEdits, function ($ymlRow1, $ymlRow2) {
      $key1 = explode(' : ', $ymlRow1);
      $key2 = explode(' : ', $ymlRow2);

      // Values from flat array will be marked for removal.
      if (substr($key1[0], -3) === '::-' && substr($key2[0], -3) === '::-') {
        return -1;
      }

      return strcmp($key1[0], $key2[0]);
    });

    return $additionalEdits;
  }

  /**
   * Ensure that configuration is always array and cleaned up.
   *
   * Not needed configuration parameters will be stripped.
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

    // Strip params that are not needed.
    foreach ($this->stripConfigParams as $param) {
      if (isset($configData[$param])) {
        unset($configData[$param]);
      }
    }

    return $configData;
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
  protected function getConfigNames(array $configList) {
    $configNames = [];
    foreach ($configList as $configFile) {
      $configNames[] = ConfigName::createByFullName($configFile);
    }

    return $configNames;
  }

  /**
   * Get full path for update patch file.
   *
   * @param string $moduleName
   *   Module name.
   * @param string $updateName
   *   Update name.
   * @param bool $createDirectory
   *   Flag if directory should be created.
   *
   * @return string
   *   Returns full path file name for update patch.
   */
  public function getPatchFile($moduleName, $updateName, $createDirectory = FALSE) {
    $updateDir = $this->moduleHandler->getModule($moduleName)->getPath() . $this->baseUpdatePath;

    // Ensure that directory exists.
    if (!is_dir($updateDir) && $createDirectory) {
      mkdir($updateDir, 0755, TRUE);
    }

    return $updateDir . '/' . $updateName . '.yml';
  }

  /**
   * Load update definition from file.
   *
   * @param string $moduleName
   *   Module name.
   * @param string $updateName
   *   Update name.
   *
   * @return mixed
   *   Returns update definition.
   */
  public function loadUpdate($moduleName, $updateName) {
    return $this->serializer->decode(file_get_contents($this->getPatchFile($moduleName, $updateName)));
  }

}
