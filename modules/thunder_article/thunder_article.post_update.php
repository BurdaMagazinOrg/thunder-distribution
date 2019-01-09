<?php

/**
 * @file
 * Post update functions for Thunder.
 */

use Drupal\Core\Entity\EntityStorageException;
use Drupal\views\Entity\View;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Move scheduler view tab to local task on content list.
 */
function thunder_article_post_update_move_scheduler_to_content_list() {
  /** @var \Drupal\update_helper\Updater $updater */
  $updater = \Drupal::service('update_helper.updater');
  $updateLogger = $updater->logger();

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

      try {
        /** @var \Drupal\update_helper_checklist\UpdateChecklist $updateChecklist */
        $updateChecklist = \Drupal::service('update_helper_checklist.update_checklist');
        $updateChecklist->markUpdatesSuccessful(['thunder' => ['thunder_article_move_scheduler_to_content_list']]);
      }
      catch (EntityStorageException $ee) {
        $updateLogger->warning(t('Unable to mark update in checklist.'));
      }
      catch (ServiceNotFoundException $se) {
        // If service is not available, we will just ignore it.
      }
    }
  }

  // Output logged messages to related channel of update execution.
  return $updateLogger->output();
}
