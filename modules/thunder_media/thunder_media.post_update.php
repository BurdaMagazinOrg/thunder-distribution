<?php

/**
 * @file
 * Post update functions for Thunder.
 */

/**
 * Move to core's filename transliteration functionality.
 */
function thunder_media_post_update_move_to_core_filename_transliteration() {

  /** @var \Drupal\thunder_updater\Updater $thunderUpdater */
  $thunderUpdater = \Drupal::service('thunder_updater');

  \Drupal::config('system.file')->set('filename_transliteration', \Drupal::config('thunder_media.settings')->get('enable_filename_transliteration'));
  \Drupal::config('thunder_media.settings')->clear('enable_filename_transliteration');

  // Output logged messages to related channel of update execution.
  return $thunderUpdater->logger()->output();
}
