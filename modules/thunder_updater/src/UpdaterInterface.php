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
   * @param array $deleteKeys
   *   List of parent keys to remove. @see NestedArray::unsetValue()
   *
   * @return bool
   *   Returns TRUE if update of configuration was successful.
   */
  public function updateConfig($configName, array $configuration, array $expectedConfiguration = [], array $deleteKeys = []);

  /**
   * Execute update of configuration from update definitions.
   *
   * @param array $updateDefinitions
   *   List of configuration definitions.
   */
  public function executeUpdate(array $updateDefinitions);

  /**
   * Installs a module, checks updater checkbox and works with logger.
   *
   * @param array $modules
   *   Key is name of the checkbox, value name of the module.
   */
  public function installModules(array $modules);

  /**
   * Get update logger service.
   *
   * @return \Drupal\thunder_updater\UpdateLogger
   *   Returns update logger.
   */
  public function logger();

  /**
   * Returns update checklist service.
   *
   * @return \Drupal\thunder_updater\UpdateChecklist
   *   Update checklist service.
   */
  public function checklist();

}
