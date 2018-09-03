<?php

namespace Drupal\thunder_updater;

use Drupal\Component\Utility\NestedArray;
use Drupal\config_update\ConfigRevertInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\MissingDependencyException;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TempStore\SharedTempStoreFactory;
use Drupal\Component\Utility\DiffArray;

/**
 * Helper class to update configuration.
 */
class Updater implements UpdaterInterface {

  use StringTranslationTrait;

  /**
   * Site configFactory object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Temp store factory.
   *
   * @var \Drupal\Core\TempStore\SharedTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Module installer service.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * Config reverter service.
   *
   * @var \Drupal\config_update\ConfigRevertInterface
   */
  protected $configReverter;

  /**
   * Configuration handler service.
   *
   * @var \Drupal\thunder_updater\ConfigHandler
   */
  protected $configHandler;

  /**
   * Logger service.
   *
   * @var \Drupal\thunder_updater\UpdateLogger
   */
  protected $logger;

  /**
   * Update Checklist service.
   *
   * @var \Drupal\thunder_updater\UpdateChecklist
   */
  protected $checklist;

  /**
   * Constructs the PathBasedBreadcrumbBuilder.
   *
   * @param \Drupal\Core\TempStore\SharedTempStoreFactory $tempStoreFactory
   *   A temporary key-value store service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory service.
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $moduleInstaller
   *   Module installer service.
   * @param \Drupal\config_update\ConfigRevertInterface $configReverter
   *   Config reverter service.
   * @param \Drupal\thunder_updater\ConfigHandler $configHandler
   *   Configuration handler service.
   * @param \Drupal\thunder_updater\UpdateLogger $logger
   *   Update logger.
   * @param \Drupal\thunder_updater\UpdateChecklist $checklist
   *   Update Checklist service.
   */
  public function __construct(SharedTempStoreFactory $tempStoreFactory, ConfigFactoryInterface $configFactory, ModuleInstallerInterface $moduleInstaller, ConfigRevertInterface $configReverter, ConfigHandler $configHandler, UpdateLogger $logger, UpdateChecklist $checklist) {
    $this->tempStoreFactory = $tempStoreFactory;
    $this->configFactory = $configFactory;
    $this->moduleInstaller = $moduleInstaller;
    $this->configReverter = $configReverter;
    $this->configHandler = $configHandler;
    $this->logger = $logger;
    $this->checklist = $checklist;
  }

  /**
   * {@inheritdoc}
   */
  public function logger() {
    return $this->logger;
  }

  /**
   * {@inheritdoc}
   */
  public function checklist() {
    return $this->checklist;
  }

  /**
   * {@inheritdoc}
   */
  public function updateEntityBrowserConfig($browser, array $configuration, array $oldConfiguration = []) {

    if ($this->updateConfig('entity_browser.browser.' . $browser, $configuration, $oldConfiguration)) {
      $this->updateTempConfigStorage('entity_browser', $browser, $configuration);

      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function updateConfig($configName, array $configuration, array $expectedConfiguration = [], array $deleteKeys = []) {
    $config = $this->configFactory->getEditable($configName);

    $configData = $config->get();

    // Check that configuration exists before executing update.
    if (empty($configData)) {
      return FALSE;
    }

    // Check if configuration is already in new state.
    $mergedData = NestedArray::mergeDeep($expectedConfiguration, $configuration);
    if (empty(DiffArray::diffAssocRecursive($mergedData, $configData))) {
      return TRUE;
    }

    if (!empty($expectedConfiguration) && DiffArray::diffAssocRecursive($expectedConfiguration, $configData)) {
      return FALSE;
    }

    // Delete configuration keys from config.
    if (!empty($deleteKeys)) {
      foreach ($deleteKeys as $keyPath) {
        NestedArray::unsetValue($configData, $keyPath);
      }
    }

    $config->setData(NestedArray::mergeDeep($configData, $configuration));
    $config->save();

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function executeUpdate(array $updateDefinitions) {
    $successfulUpdate = TRUE;

    foreach ($updateDefinitions as $configName => $configChange) {
      $expectedConfig = $configChange['expected_config'];
      $updateActions = $configChange['update_actions'];

      // Define configuration keys that should be deleted.
      $deleteKeys = [];
      if (isset($updateActions['delete'])) {
        $deleteKeys = $this->getFlatKeys($updateActions['delete']);
      }

      $newConfig = [];
      // Add configuration that is changed.
      if (isset($updateActions['change'])) {
        $newConfig = NestedArray::mergeDeep($newConfig, $updateActions['change']);
      }

      // Add configuration that is added.
      if (isset($updateActions['add'])) {
        $newConfig = NestedArray::mergeDeep($newConfig, $updateActions['add']);
      }

      if ($this->updateConfig($configName, $newConfig, $expectedConfig, $deleteKeys)) {
        $this->logger->info($this->t('Configuration @configName has been successfully updated.', ['@configName' => $configName]));
      }
      else {
        $successfulUpdate = FALSE;
        $this->logger->warning($this->t('Unable to update configuration for @configName.', ['@configName' => $configName]));
      }
    }

    return $successfulUpdate;
  }

  /**
   * Execute list of updates.
   *
   * @param array $updateList
   *   List of modules and updates that should be executed.
   *
   * @return bool
   *   Returns if update execution was successful.
   */
  public function executeUpdates(array $updateList) {
    $updateDefinitions = [];

    foreach ($updateList as $updateEntry) {
      $updateDefinitions = array_merge($updateDefinitions, $this->configHandler->loadUpdate($updateEntry[0], $updateEntry[1]));
    }

    return $this->executeUpdate($updateDefinitions);
  }

  /**
   * Get flatten array keys as list of paths.
   *
   * Example:
   *   $nestedArray = [
   *      'a' => [
   *          'b' => [
   *              'c' => 'c1',
   *          ],
   *          'bb' => 'bb1'
   *      ],
   *      'aa' => 'aa1'
   *   ]
   *
   * Result: [
   *   ['a', 'b', 'c'],
   *   ['a', 'bb']
   *   ['aa']
   * ]
   *
   * @param array $nestedArray
   *   Array with nested keys.
   *
   * @return array
   *   List of flattened keys.
   */
  public function getFlatKeys(array $nestedArray) {
    $keys = [];
    foreach ($nestedArray as $key => $value) {
      if (is_array($value) && !empty($value)) {
        $listOfSubKeys = $this->getFlatKeys($value);

        foreach ($listOfSubKeys as $subKeys) {
          $keys[] = array_merge([$key], $subKeys);
        }
      }
      else {
        $keys[] = [$key];
      }
    }

    return $keys;
  }

  /**
   * Update CTools edit form state.
   *
   * @param string $configType
   *   Configuration type.
   * @param string $configName
   *   Configuration name.
   * @param array $configuration
   *   Configuration what should be set for CTools form.
   */
  protected function updateTempConfigStorage($configType, $configName, array $configuration) {
    $entityBrowserConfig = $this->tempStoreFactory->get($configType . '.config');

    $storage = $entityBrowserConfig->get($configName);

    if (!empty($storage)) {
      foreach ($configuration as $key => $value) {
        $part = $storage[$configType]->getPluginCollections()[$key];

        $part->setConfiguration(NestedArray::mergeDeep($part->getConfiguration(), $value));
      }

      $entityBrowserConfig->set($configName, $storage);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function installModules(array $modules) {

    $successful = [];
    $modulesInstalledSuccessfully = TRUE;
    foreach ($modules as $update => $module) {
      try {
        if ($this->moduleInstaller->install([$module])) {
          $successful[] = $update;

          $this->logger->info($this->t('Module @module is successfully enabled.', ['@module' => $module]));
        }
        else {
          $this->checklist->markUpdatesFailed([$update]);
          $this->logger->warning($this->t('Unable to enable @module.', ['@module' => $module]));
          $modulesInstalledSuccessfully = FALSE;
        }
      }
      catch (MissingDependencyException $e) {
        $this->checklist->markUpdatesFailed([$update]);
        $this->logger->warning($this->t('Unable to enable @module because of missing dependencies.', ['@module' => $module]));
        $modulesInstalledSuccessfully = FALSE;
      }
    }

    $this->checklist->markUpdatesSuccessful($successful);
    return $modulesInstalledSuccessfully;
  }

  /**
   * List of full configuration names to import.
   *
   * @param array $configList
   *   List of configurations.
   *
   * @return bool
   *   Returns if import was successful.
   */
  public function importConfigs(array $configList) {
    $successfulImport = TRUE;

    // Import configurations.
    foreach ($configList as $fullConfigName) {
      try {
        $configName = ConfigName::createByFullName($fullConfigName);

        if (!$this->configReverter->import($configName->getType(), $configName->getName())) {
          throw new \Exception('Config not found');
        }
        $this->logger->info($this->t('Configuration @full_name has been successfully imported.', [
          '@full_name' => $fullConfigName,
        ]));
      }
      catch (\Exception $e) {
        $successfulImport = FALSE;

        $this->logger->warning($this->t('Unable to import @full_name config.', [
          '@full_name' => $fullConfigName,
        ]));
      }
    }

    return $successfulImport;
  }

}
