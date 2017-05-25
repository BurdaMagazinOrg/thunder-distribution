<?php

namespace Drupal\thunder_ach\Plugin\thunder_ach;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\thunder_ach\Plugin\ThunderAccessControlHandlerBase;

/**
 * Default access control handler plugin for taxonomy terms.
 *
 * @see \Drupal\taxonomy\TermAccessControlHandler
 *
 * @ThunderAccessControlHandler(
 *   id = "term",
 *   type = "taxonomy_term",
 *   weight = -10
 * )
 */
class Term extends ThunderAccessControlHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function applies(EntityInterface $entity, $operation, AccountInterface $account = NULL) {
    // Applies to all terms.
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'access content');

      case 'update':
        return AccessResult::allowedIfHasPermissions($account, ["edit terms in {$entity->bundle()}", 'administer taxonomy'], 'OR');

      case 'delete':
        return AccessResult::allowedIfHasPermissions($account, ["delete terms in {$entity->bundle()}", 'administer taxonomy'], 'OR');

      default:
        // No opinion.
        return AccessResult::neutral();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'administer taxonomy');
  }

}
