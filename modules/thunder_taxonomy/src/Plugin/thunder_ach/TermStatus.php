<?php

namespace Drupal\thunder_taxonomy\Plugin\thunder_ach;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\thunder_ach\Plugin\thunder_ach\Term;

/**
 * Provides a access control handler plugin for taxonomy term.
 *
 * @ThunderAccessControlHandler(
 *   id = "term_status",
 *   type = "taxonomy_term",
 *   weight = -10
 * )
 */
class TermStatus extends Term {

  /**
   * {@inheritdoc}
   */
  public function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if (!isset($entity->status)) {
      return parent::checkAccess($entity, $operation, $account);
    }
    switch ($operation) {
      case 'view':
        // Check for status and set 'published' or 'unpublished'.
        $status = ($entity->status->value) ? 'published' : 'unpublished';
        return AccessResult::allowedIf($account->hasPermission('access content') && $account->hasPermission('view ' . $status . ' terms in ' . $entity->bundle()));
    }
    // Fallback.
    return parent::checkAccess($entity, $operation, $account);
  }

}
