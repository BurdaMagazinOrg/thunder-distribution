<?php

/**
 * @file
 * Drush integration for the thunder_media_expire module.
 */

/**
 * Implements hook_drush_command().
 */
function thunder_media_expire_drush_command() {
  $items['thunder-media-expire-check'] = [
    'description' => dt('Checks for expired media.'),
    'aliases' => ['tmec'],
  ];

  return $items;
}

/**
 * Checks for expired media.
 */
function drush_thunder_media_expire_check() {

  /** @var \Drupal\thunder_media_expire\MediaExpireService $service */
  $service = \Drupal::service('thunder_media_expire.service');
  $service->unpublishExpiredMedia();
}
