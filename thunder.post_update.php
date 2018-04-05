<?php

/**
 * @file
 * Post update functions for Thunder.
 */

use Drupal\views\Entity\View;

/**
 * Add config_selector settings to content and media view.
 */
function thunder_post_update_add_config_selector_to_content_media() {
  /** @var \Drupal\thunder_updater\Updater $thunderUpdater */
  $thunderUpdater = \Drupal::service('thunder_updater');

  // Execute configuration update definitions with logging of success.
  if ($thunderUpdater->executeUpdates([['thunder', 'thunder_add_config_selector_to_content_media']])) {
    View::load('content')->save();
    View::load('thunder_media')->save();
    /** @var \Drupal\config_update\ConfigReverter $configReverter */
    $configReverter = \Drupal::service('config_update.config_update');
    $configReverter->import('config_selector_feature', 'thunder_content_view');
    $configReverter->import('config_selector_feature', 'thunder_media_view');
    $thunderUpdater->checklist()->markUpdatesSuccessful(['thunder_add_config_selector_to_content_media']);
  }
  else {
    $thunderUpdater->checklist()->markUpdatesFailed(['thunder_add_config_selector_to_content_media']);
  }

  // Output logged messages to related channel of update execution.
  return $thunderUpdater->logger()->output();
}

