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
    // This function was renamed after Thunder 2.14, because we want to ensure
    // that the config_selector is always enabled.
    // thunder_post_update_install_config_selector was the former name and is
    // still the key for the checklist.
    $thunderUpdater->installModules(['thunder_post_update_install_config_selector' => 'config_selector']);
  }

  // Output logged messages to related channel of update execution.
  return $thunderUpdater->logger()->output();
}

/**
 * Add config_selector settings to content and media view.
 */
function thunder_post_update_add_config_selector_to_content_media() {
  /** @var \Drupal\thunder_updater\Updater $thunderUpdater */
  $thunderUpdater = \Drupal::service('thunder_updater');

  // Execute configuration update definitions with logging of success.
  if ($thunderUpdater->executeUpdates([['thunder', 'thunder_add_config_selector_to_content_media']])) {
    $thunderUpdater->checklist()->markUpdatesSuccessful(['thunder_add_config_selector_to_content_media']);
  }
  else {
    $thunderUpdater->checklist()->markUpdatesFailed(['thunder_add_config_selector_to_content_media']);
  }

  // Output logged messages to related channel of update execution.
  return $thunderUpdater->logger()->output();
}
