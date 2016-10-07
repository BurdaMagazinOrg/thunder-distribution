<?php

/**
 * @file
 * Drush integration for the thunder_media_expire module.
 */

use \Drupal\media_entity\Entity\MediaBundle;
use \Drupal\media_entity\Entity\Media;

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

  /** @var MediaBundle[] $bundles */
  $bundles = MediaBundle::loadMultiple();

  foreach ($bundles as $bundle) {
    if ($bundle->getThirdPartySetting('thunder_media_expire', 'enable_expiring')) {

      $query = \Drupal::entityQuery('media');
      $query->condition('status', 1);
      $query->condition('bundle', $bundle->id());
      $query->condition($bundle->getThirdPartySetting('thunder_media_expire', 'expire_field'), date("Y-m-d\TH:i:s"), '<');
      $entityIds = $query->execute();

      /** @var Media[] $medias */
      $medias = Media::loadMultiple($entityIds);
      foreach ($medias as $media) {
        $media->setPublished(Media::NOT_PUBLISHED);
        $media->save();
      }
    }
  }
}
