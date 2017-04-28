<?php

namespace Drupal\thunder_taxonomy\Plugin\thunder_ach;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\thunder_ach\Plugin\ThunderAccessControlHandlerBase;

/**
 * Provides a access control handler plugin for taxonomy term.
 *
 * @ThunderAccessControlHandler(
 *   id = "term_status",
 *   entity_type = "taxonomy_term",
 *   weight = 0
 * )
 */
class TermStatus extends ThunderAccessControlHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function applies(EntityInterface $entity, $operation, AccountInterface $account = NULL) {
    // Control access for all entities of type "taxonomy_term" having a status.
    return isset($entity->status);
  }

  /**
   * {@inheritdoc}
   */
  public function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        // Check for status and set 'published' or 'unpublished'.
        $status = ($entity->status->value) ? 'published' : 'unpublished';
        return AccessResult::forbiddenIf(!$account->hasPermission('access content') || !$account->hasPermission('view ' . $status . ' terms in ' . $entity->bundle()));
    }
    // Fallback.
    return parent::checkAccess($entity, $operation, $account);
  }

}
