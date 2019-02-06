<?php

/**
 * @file
 * Post update functions for Thunder.
 */

use Drupal\views\Entity\View;

/**
 * Add config_selector settings to content and media view.
 */
function thunder_post_update_add_config_selector_to_content() {
  /** @var \Drupal\thunder_updater\Updater $thunderUpdater */
  $thunderUpdater = \Drupal::service('thunder_updater');

  // Execute configuration update definitions with logging of success.
  if ($thunderUpdater->executeUpdates([['thunder', 'thunder_add_config_selector_to_content']])) {
    if ($view = View::load('content')) {
      $view->save();
    }
    try {
      /** @var \Drupal\config_update\ConfigReverter $configReverter */
      $configReverter = \Drupal::service('config_update.config_update');
      $configReverter->import('config_selector_feature', 'thunder_content_view');
      $thunderUpdater->checklist()->markUpdatesSuccessful(['thunder_add_config_selector_to_content']);
    }
    catch (\Exception $exception) {
      $thunderUpdater->checklist()->markUpdatesFailed(['thunder_add_config_selector_to_content']);
    }
  }
  else {
    $thunderUpdater->checklist()->markUpdatesFailed(['thunder_add_config_selector_to_content']);
  }

  // Output logged messages to related channel of update execution.
  return $thunderUpdater->logger()->output();
}
