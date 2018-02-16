<?php

/**
 * @file
 * Post update functions for Thunder.
 */

/**
 * Install the Configuration Selector module.
 */
function thunder_post_update_install_config_selector() {
  /** @var \Drupal\thunder_updater\Updater $thunderUpdater */
  $thunderUpdater = \Drupal::service('thunder_updater');
  $thunderUpdater->installModules(['thunder_post_update_install_config_selector' => 'config_selector']);

  // Output logged messages to related channel of update execution.
  return $thunderUpdater->logger()->output();
}
