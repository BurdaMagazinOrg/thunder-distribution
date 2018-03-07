<?php

/**
 * @file
 * Post update functions for Thunder.
 */

/**
 * Move scheduler view tab to local task on content list.
 */
function thunder_article_post_update_move_scheduler_to_content_list() {
  /** @var \Drupal\config_update\ConfigReverter $config_update */
  $config_update = \Drupal::service('config_update.config_update');
  $config_update->import('system.simple', 'thunder_article.settings');
}
