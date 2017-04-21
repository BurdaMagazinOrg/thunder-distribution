<?php

namespace Drupal\thunder_updater;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\MissingDependencyException;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\user\SharedTempStoreFactory;
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
   * @var \Drupal\user\SharedTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Module installer service.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

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
   * @param \Drupal\user\SharedTempStoreFactory $tempStoreFactory
   *   A temporary key-value store service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory service.
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $moduleInstaller
   *   Module installer service.
   * @param \Drupal\thunder_updater\UpdateLogger $logger
   *   Update logger.
   * @param \Drupal\thunder_updater\UpdateChecklist $checklist
   *   Update Checklist service.
   */
  public function __construct(SharedTempStoreFactory $tempStoreFactory, ConfigFactoryInterface $configFactory, ModuleInstallerInterface $moduleInstaller, UpdateLogger $logger, UpdateChecklist $checklist) {
    $this->tempStoreFactory = $tempStoreFactory;
    $this->configFactory = $configFactory;
    $this->moduleInstaller = $moduleInstaller;
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
  public function updateConfig($configName, array $configuration, array $expectedConfiguration = []) {
    $config = $this->configFactory->getEditable($configName);

    $configData = $config->get();

    // Check that configuration exists before executing update.
    if (empty($configData)) {
      return FALSE;
    }

    // Config already in new state.
    $mergedData = NestedArray::mergeDeep($expectedConfiguration, $configuration);
    if (empty(DiffArray::diffAssocRecursive($mergedData, $configData))) {
      return TRUE;
    }

    if (!empty($expectedConfiguration) && DiffArray::diffAssocRecursive($expectedConfiguration, $configData)) {
      return FALSE;
    }

    $config->setData(NestedArray::mergeDeep($configData, $configuration));
    $config->save();

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function executeUpdate(array $updateDefinitions, UpdateLogger $updateLogger) {
    $successfulUpdate = TRUE;

    foreach ($updateDefinitions as $configName => $configChange) {
      $expectedConfig = $configChange['expected_config'];
      $updateActions = $configChange['update_actions'];

      $newConfig = [];
      // Add configuration that is changed.
      if (isset($updateActions['change'])) {
        $newConfig = array_merge($newConfig, $updateActions['change']);
      }

      // Add configuration that is added.
      if (isset($updateActions['add'])) {
        $newConfig = array_merge($newConfig, $updateActions['add']);
      }

      if ($this->updateConfig($configName, $newConfig, $expectedConfig)) {
        $updateLogger->info($this->t('Configuration @configName has been successfully updated.', ['@configName' => $configName]));
      }
      else {
        $successfulUpdate = FALSE;
        $updateLogger->warning($this->t('Unable to update configuration for @configName.', ['@configName' => $configName]));
      }
    }

    return $successfulUpdate;
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

    foreach ($modules as $update => $module) {
      try {
        if ($this->moduleInstaller->install([$module])) {
          $successful[] = $update;

          $this->logger->info($this->t('Module @module is successfully enabled.', ['@module' => $module]));
        }
        else {
          $this->checklist->markUpdatesFailed([$update]);
          $this->logger->warning($this->t('Unable to enable @module.', ['@module' => $module]));
        }
      }
      catch (MissingDependencyException $e) {
        $this->checklist->markUpdatesFailed([$update]);
        $this->logger->warning($this->t('Unable to enable @module because of missing dependencies.', ['@module' => $module]));
      }
    }

    $this->checklist->markUpdatesSuccessful($successful);
  }

}
