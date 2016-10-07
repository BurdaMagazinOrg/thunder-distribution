<?php

namespace Drupal\thunder_media_expire;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\media_entity\Entity\Media;
use Drupal\media_entity\Entity\MediaBundle;

/**
 * Contains the media unpublish logic.
 *
 * @package Drupal\thunder_media_expire
 */
class MediaExpireService {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The query factory.
   *
   * @var QueryFactory
   */
  protected $queryFactory;

  /**
   * Constructs the MediaExpireService.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param QueryFactory $queryFactory
   *   The query factory service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, QueryFactory $queryFactory) {

    $this->entityTypeManager = $entity_type_manager;
    $this->queryFactory = $queryFactory;

  }

  /**
   * Unpublishes already expired media elements.
   */
  public function unpublishExpiredMedia() {

    /** @var MediaBundle[] $bundles */
    $bundles = $this->entityTypeManager->getStorage('media_bundle')
      ->loadMultiple();

    foreach ($bundles as $bundle) {
      if ($bundle->getThirdPartySetting('thunder_media_expire', 'enable_expiring')) {

        $expireField = $bundle->getThirdPartySetting('thunder_media_expire', 'expire_field');
        $query = $this->queryFactory->get('media', 'AND');
        $query->condition('status', 1);
        $query->condition('bundle', $bundle->id());
        $query->condition($expireField, date("Y-m-d\TH:i:s"), '<');
        $entityIds = $query->execute();

        /** @var Media[] $medias */
        $medias = $this->entityTypeManager->getStorage('media')
          ->loadMultiple($entityIds);

        foreach ($medias as $media) {
          $media->setPublished(Media::NOT_PUBLISHED);
          $media->$expireField->removeItem(0);
          $media->save();
        }
      }
    }
  }

}
