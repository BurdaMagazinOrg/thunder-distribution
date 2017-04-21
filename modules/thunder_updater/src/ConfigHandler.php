<?php

namespace Drupal\thunder_updater;

use Drupal\Component\Serialization\SerializationInterface;
use Drupal\config_update\ConfigListInterface;
use Drupal\config_update\ConfigRevertInterface;
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
   * @var ConfigDiffer
   */
  protected $configDiffer;

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
   * Config handler constructor.
   *
   * @param \Drupal\config_update\ConfigListInterface $configList
   *   Config list service.
   * @param \Drupal\config_update\ConfigRevertInterface $configReverter
   *   Config reverter service.
   * @param \Drupal\thunder_updater\ConfigDiffer $configDiffer
   *   Config differ service.
   * @param \Drupal\Component\Serialization\SerializationInterface $yamlSerializer
   *   Array serializer service.
   */
  public function __construct(ConfigListInterface $configList, ConfigRevertInterface $configReverter, ConfigDiffer $configDiffer, SerializationInterface $yamlSerializer) {
    $this->configList = $configList;
    $this->configReverter = $configReverter;
    $this->configDiffer = $configDiffer;
    $this->serializer = $yamlSerializer;
  }

  /**
   * Set serializer.
   *
   * @param \Drupal\Component\Serialization\SerializationInterface $serializer
   *   Serializer that will be used to serialize arrays.
   */
  public function setSerializer(SerializationInterface $serializer) {
    $this->serializer = $serializer;
  }

  /**
   * Generate patch from changed configuration.
   *
   * It compares Base vs. Active configuration and creates patch with defined
   * name in patch folder.
   *
   * @param string $moduleName
   *   Module name that will be used to generate patch for it.
   * @param string $rawFilename
   *   File name where raw patch data will be saved.
   *
   * @return string|bool
   *   Rendering generated patch file name or FALSE if patch is empty.
   */
  public function generateRawData($moduleName, $rawFilename) {
    $updatePatch = [];
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

    if (!empty($updatePatch)) {
      file_put_contents($rawFilename, $this->serializer->encode($updatePatch));

      return TRUE;
    }

    return FALSE;
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

    return $this->configDiffer->formatToConfig($listExpected);
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
    $listUpdate = [];

    foreach ($diffs as $diffOp) {
      if (!empty($diffOp->closing)) {
        if (!isset($listUpdate[$diffOp->type])) {
          $listUpdate[$diffOp->type] = [];
        }

        $listUpdate[$diffOp->type] = array_merge($listUpdate[$diffOp->type], $diffOp->closing);
      }
    }

    foreach ($listUpdate as $action => $edits) {
      $listUpdate[$action] = $this->configDiffer->formatToConfig($edits);
    }

    return $listUpdate;
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

}
