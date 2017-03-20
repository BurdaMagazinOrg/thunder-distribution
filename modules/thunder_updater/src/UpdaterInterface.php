<?php

namespace Drupal\thunder_updater;

/**
 * Interface for the Update entity.
 */
interface UpdaterInterface {

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
  public function updateEntityBrowserConfig($browser, array $configuration, array $oldConfiguration = []);

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
  public function updateConfig($configName, array $configuration, array $expectedConfiguration = []);

  /**
   * Marks a list of updates as successful.
   *
   * @param array $names
   *   Array of update ids.
   * @param bool $checkListPoints
   *   Indicates the corresponding checkbox should be checked.
   */
  public function markUpdatesSuccessful(array $names, $checkListPoints = TRUE);

  /**
   * Marks a list of updates as failed.
   *
   * @param array $names
   *   Array of update ids.
   */
  public function markUpdatesFailed(array $names);

  /**
   * Marks a list of updates.
   *
   * @param bool $status
   *   Checkboxes enabled or disabled.
   */
  public function markAllUpdates($status = TRUE);

  /**
   * Installs a module, checks updater checkbox and works with logger.
   *
   * @param array $modules
   *   Key is name of the checkbox, value name of the module.
   * @param \Drupal\thunder_updater\UpdateLogger $updateLogger
   *   Logger service.
   */
  public function installModules(array $modules, UpdateLogger $updateLogger);

}
