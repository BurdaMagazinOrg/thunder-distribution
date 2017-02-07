<?php

namespace Drupal\thunder_updater;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\MissingDependencyException;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\thunder\ThunderUpdateLogger;
use Drupal\thunder_updater\Entity\Update;
use Drupal\user\SharedTempStoreFactory;
use Drupal\Component\Utility\DiffArray;
use Drupal\checklistapi\ChecklistapiChecklist;

/**
 * Helper class to update configuration.
 */
class Updater {

  /**
   * Site configFactory object.
   *
   * @var ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Temp store factory.
   *
   * @var SharedTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Module installer service.
   *
   * @var ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * Constructs the PathBasedBreadcrumbBuilder.
   *
   * @param \Drupal\user\SharedTempStoreFactory $tempStoreFactory
   *   A temporary key-value store service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory service.
   * @param ModuleInstallerInterface $moduleInstaller
   *   Module installer service.
   */
  public function __construct(SharedTempStoreFactory $tempStoreFactory, ConfigFactoryInterface $configFactory, ModuleInstallerInterface $moduleInstaller) {
    $this->tempStoreFactory = $tempStoreFactory;
    $this->configFactory = $configFactory;
    $this->moduleInstaller = $moduleInstaller;
  }

  /**
   * Update entity browser configuration.
   *
   * @param string $browser
   *   Id of the entity browser.
   * @param array $configuration
   *   Configuration array to update.
   * @param array $oldConfiguration
   *   Only if current config is same like old config we are updating.
   *
   * @return bool
   *   Indicates if config was updated or not.
   */
  public function updateEntityBrowserConfig($browser, array $configuration, array $oldConfiguration = []) {

    if ($this->updateConfig('entity_browser.browser.' . $browser, $configuration, $oldConfiguration)) {
      $this->updateTempConfigStorage('entity_browser', $browser, $configuration);

      return TRUE;
    }

    return FALSE;
  }

  /**
   * Update configuration.
   *
   * It's possible to provide expected configuration that should be checked,
   * before new configuration is applied in order to ensure existing
   * configuration is expected one.
   *
   * @param string $configName
   *   Configuration name that should be updated.
   * @param array $configuration
   *   Configuration array to update.
   * @param array $expectedConfiguration
   *   Only if current config is same like old config we are updating.
   *
   * @return bool
   *   Returns TRUE if update of configuration was successful.
   */
  public function updateConfig($configName, array $configuration, array $expectedConfiguration = []) {
    $config = $this->configFactory->getEditable($configName);

    $configData = $config->get();

    // Check that configuration exists before executing update.
    if (empty($configData)) {
      return FALSE;
    }

    if (!empty($expectedConfiguration) && DiffArray::diffAssocRecursive($expectedConfiguration, $configData)) {
      return FALSE;
    }

    $config->setData(NestedArray::mergeDeep($configData, $configuration));
    $config->save();

    return TRUE;
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
   * Marks a list of updates as successful.
   *
   * @param array $names
   *   Array of update ids.
   */
  public function markUpdatesSuccessful(array $names) {

    foreach ($names as $name) {

      if ($update = Update::load($name)) {
        $update->setSuccessfulByHook(TRUE)
          ->save();
      }
      else {
        Update::create([
          'id' => $name,
          'successful_by_hook' => TRUE,
        ])->save();
      }
    }

    $this->checkListPoints($names);
  }

  /**
   * Marks a list of updates as failed.
   *
   * @param array $names
   *   Array of update ids.
   */
  public function markUpdatesFailed(array $names) {

    foreach ($names as $name) {

      if ($update = Update::load($name)) {
        $update->setSuccessfulByHook(FALSE)
          ->save();
      }
      else {
        Update::create([
          'id' => $name,
          'successful_by_hook' => FALSE,
        ])->save();
      }
    }
  }

  /**
   * Marks a list of updates.
   *
   * @param bool $status
   *   Checkboxes enabled or disabled.
   */
  public function markAllUpdates($status = TRUE) {

    $checklist = checklistapi_checklist_load('thunder_updater');

    foreach ($checklist->items as $versionItems) {
      foreach ($versionItems as $key => $item) {

        if (!is_array($item)) {
          continue;
        }

        if ($update = Update::load($key)) {
          $update->setSuccessfulByHook($status)
            ->save();
        }
        else {
          Update::create([
            'id' => $key,
            'successful_by_hook' => $status,
          ])->save();
        }
      }
    }

    $this->checkAllListPoints($status);
  }

  /**
   * Checks an array of bulletpoints on a checklist.
   *
   * @param array $names
   *   Array of the bulletpoints.
   */
  protected function checkListPoints(array $names) {

    /** @var Drupal\Core\Config\Config $thunderUpdaterConfig */
    $thunderUpdaterConfig = $this->configFactory
      ->getEditable('checklistapi.progress.thunder_updater');

    $user = \Drupal::currentUser()->id();
    $time = time();

    foreach ($names as $name) {
      if ($thunderUpdaterConfig && !$thunderUpdaterConfig->get(ChecklistapiChecklist::PROGRESS_CONFIG_KEY . ".#items.$name")) {

        $thunderUpdaterConfig
          ->set(ChecklistapiChecklist::PROGRESS_CONFIG_KEY . ".#items.$name", [
            '#completed' => time(),
            '#uid' => $user,
          ]);

      }
    }

    $thunderUpdaterConfig
      ->set(ChecklistapiChecklist::PROGRESS_CONFIG_KEY . '.#completed_items', count($thunderUpdaterConfig->get(ChecklistapiChecklist::PROGRESS_CONFIG_KEY . ".#items")))
      ->set(ChecklistapiChecklist::PROGRESS_CONFIG_KEY . '.#changed', $time)
      ->set(ChecklistapiChecklist::PROGRESS_CONFIG_KEY . '.#changed_by', $user)
      ->save();
  }

  /**
   * Checks all the bulletpoints on a checklist.
   *
   * @param bool $status
   *   Checkboxes enabled or disabled.
   */
  protected function checkAllListPoints($status = TRUE) {

    /** @var Drupal\Core\Config\Config $thunderUpdaterConfig */
    $thunderUpdaterConfig = $this->configFactory
      ->getEditable('checklistapi.progress.thunder_updater');

    $user = \Drupal::currentUser()->id();
    $time = time();

    $thunderUpdaterConfig
      ->set(ChecklistapiChecklist::PROGRESS_CONFIG_KEY . '.#changed', $time)
      ->set(ChecklistapiChecklist::PROGRESS_CONFIG_KEY . '.#changed_by', $user);

    $checklist = checklistapi_checklist_load('thunder_updater');

    $exclude = [
      '#title',
      '#description',
      '#weight',
    ];

    foreach ($checklist->items as $versionItems) {
      foreach ($versionItems as $itemName => $item) {
        if (!in_array($itemName, $exclude)) {
          if ($status) {
            $thunderUpdaterConfig
              ->set(ChecklistapiChecklist::PROGRESS_CONFIG_KEY . ".#items.$itemName", [
                '#completed' => $time,
                '#uid' => $user,
              ]);
          }
          else {
            $thunderUpdaterConfig
              ->clear(ChecklistapiChecklist::PROGRESS_CONFIG_KEY . ".#items.$itemName");
          }
        }
      };
    }

    $thunderUpdaterConfig
      ->set(ChecklistapiChecklist::PROGRESS_CONFIG_KEY . '.#completed_items', count($thunderUpdaterConfig->get(ChecklistapiChecklist::PROGRESS_CONFIG_KEY . ".#items")))
      ->save();
  }

  /**
   * Installs a module, checks updater checkbox and works with logger.
   *
   * @param array $modules
   *   Key is name of the checkbox, value name of the module.
   * @param ThunderUpdateLogger $updateLogger
   *   Logger service.
   */
  public function installModules(array $modules, ThunderUpdateLogger $updateLogger) {

    $successful = [];

    foreach ($modules as $update => $module) {
      try {
        if ($this->moduleInstaller->install([$module])) {
          $successful[] = $update;
        }
        else {
          $updateLogger->warning(t('Unable to enable @module.', ['@module' => $module]));
          $this->markUpdatesFailed([$update]);
        }
      }
      catch (MissingDependencyException $e) {
        $this->markUpdatesFailed([$update]);
        $updateLogger->warning(t('Unable to enable @module because of missing dependencies.', ['@module' => $module]));
      }
    }
    $this->markUpdatesSuccessful($successful);
  }

}
