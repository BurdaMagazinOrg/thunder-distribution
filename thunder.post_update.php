<?php

/**
 * @file
 * Post update functions for Thunder.
 */

use Drupal\views\Entity\View;

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
 * Installs the content_content_lock view.
 */
function thunder_post_update_add_content_lock_view() {

  $content_view = View::load('content');
  if (empty($content_view)) {
    return;
  }
  $content_view->setThirdPartySetting('config_selector', 'feature', 'thunder_content_view');
  $content_view->setThirdPartySetting('config_selector', 'priority', 0);

  if (!\Drupal::moduleHandler()->moduleExists('content_lock')) {
    /** @var \Drupal\config_update\ConfigReverter $config_service */
    $config_service = \Drupal::service('config_update.config_update');
    $config_service->import('view', 'content_content_lock');
    $content_lock_view = View::load('content_content_lock');
    $content_lock_view->disable();
    $content_lock_view->save();
  }
  else {
    $view_contains_content_lock = FALSE;
    foreach (array_keys($content_view->get('display')) as $display_name) {
      $display = $content_view->getDisplay($display_name);
      $options = ['fields', 'filters', 'sorts', 'relationships'];
      foreach ($options as $option) {
        foreach ($display['display_options'][$option] as $key => $values) {
          if ($values['table'] == 'content_lock') {
            $view_contains_content_lock = TRUE;
          }
        }
      }
    }

    if ($view_contains_content_lock) {
      // Duplicate content into content_content_lock view and add third party
      // settings.
      $content_lock_view = $content_view->createDuplicate();
      $content_lock_view->set('id', 'content_content_lock');
      $content_lock_view->setThirdPartySetting('config_selector', 'feature', 'thunder_content_view');
      $content_lock_view->setThirdPartySetting('config_selector', 'priority', 1);
      $content_lock_view->save();

      // Delete all content_lock related stuff from the content view.
      foreach (array_keys($content_view->get('display')) as $display_name) {
        $display = &$content_view->getDisplay($display_name);

        // Delete content_lock relationships.
        $deleted_relationships = [];
        if (!empty($display['display_options']['relationships']) && is_array($display['display_options']['relationships'])) {
          foreach ($display['display_options']['relationships'] as $key => $relationship) {
            if ($relationship['table'] == 'content_lock') {
              unset($display['display_options']['relationships'][$key]);
              $deleted_relationships[] = $key;
            }
          }
        }

        // Delete content lock fields, filters and sorts and those that depend
        // on a deleted relationship.
        $options = ['fields', 'filters', 'sorts'];
        foreach ($options as $option) {
          if (!empty($display['display_options'][$option]) && is_array($display['display_options'][$option])) {
            foreach ($display['display_options'][$option] as $key => $values) {
              if ($values['table'] == 'content_lock' || in_array($values['relationship'], $deleted_relationships)) {
                unset($display['display_options'][$option][$key]);
              }
            }
          }
        }
      }
      $content_view->disable();
    }
    else {
      /** @var \Drupal\config_update\ConfigReverter $config_service */
      $config_service = \Drupal::service('config_update.config_update');
      $config_service->import('view', 'content_content_lock');
      $content_lock_view = View::load('content_content_lock');
      $content_lock_view->disable();
      $content_lock_view->save();
    }
  }
  $content_view->save();
}
