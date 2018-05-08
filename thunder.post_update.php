<?php

/**
 * @file
 * Post update functions for Thunder.
 */

/**
 * Install content_moderation and scheduler integration.
 */
function thunder_post_update_enable_content_moderation() {
  /** @var \Drupal\thunder_updater\Updater $thunderUpdater */
  $thunderUpdater = \Drupal::service('thunder_updater');
  $thunderUpdater->installModules(['thunder_enable_content_moderation' => 'scheduler_content_moderation_integration']);
  // Output logged messages to related channel of update execution.
  return $thunderUpdater->logger()->output();
}
