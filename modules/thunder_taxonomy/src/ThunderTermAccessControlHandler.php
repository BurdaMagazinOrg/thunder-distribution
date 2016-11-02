<?php

namespace Drupal\thunder_taxonomy;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\taxonomy\TermAccessControlHandler;

/**
 * Defines the access control handler for the taxonomy term entity type.
 *
 * @see \Drupal\taxonomy\Entity\Term
 */
class ThunderTermAccessControlHandler extends TermAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        // Check for status and set 'published' or 'unpublished'.
        $status = ($entity->status->value) ? 'published' : 'unpublished';
        return AccessResult::allowedIf($account->hasPermission('access content') && $account->hasPermission('view ' . $status . ' terms in ' . $entity->bundle()));

      default:
        return parent::checkAccess($entity, $operation, $account);

    }
  }

}
