<?php

/**
 * @file
 * Post update functions for Thunder.
 */

use Drupal\views\Entity\View;

/**
 * Move scheduler view tab to local task on content list.
 */
function thunder_article_post_update_move_scheduler_to_content_list() {
  /** @var \Drupal\update_helper\Updater $updater */
  $updater = \Drupal::service('update_helper.updater');

  /** @var \Drupal\update_helper_checklist\UpdateChecklist $updater */
  $updateChecklist = \Drupal::service('update_helper_checklist.update_checklist');

  /** @var \Drupal\config_update\ConfigReverter $config_update */
  $config_update = \Drupal::service('config_update.config_update');
  if ($config_update->import('system.simple', 'thunder_article.settings')) {
    $view = View::load('locked_content');
    if ($view) {
      $view->set('id', 'locked_content_old');
      $view->save();
      $updater->logger()->alert('The locked content view was renamed to views.view.locked_content_old.');
    }
    if ($config_update->import('view', 'locked_content')) {
      $updater->logger()->info('The new locked content view was imported as views.view.locked_content.');
      $updateChecklist->markUpdatesSuccessful(['thunder_article_move_scheduler_to_content_list']);
    }
  }

  // Output logged messages to related channel of update execution.
  return $updater->logger()->output();
}
