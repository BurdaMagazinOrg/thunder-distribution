<?php

/**
 * @file
 * Post update functions for Thunder.
 */

/**
 * Install the Configuration Selector module if necessary.
 */
function thunder_post_update_ensure_config_selector_installed() {
  /** @var \Drupal\thunder_updater\Updater $thunderUpdater */
  $thunderUpdater = \Drupal::service('thunder_updater');
  if (!\Drupal::moduleHandler()->moduleExists('config_selector')) {
    $thunderUpdater->installModules(['thunder_post_update_install_config_selector' => 'config_selector']);
  }

  // Output logged messages to related channel of update execution.
  return $thunderUpdater->logger()->output();
}
