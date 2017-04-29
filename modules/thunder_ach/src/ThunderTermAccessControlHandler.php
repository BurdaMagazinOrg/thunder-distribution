<?php

namespace Drupal\thunder_ach;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\taxonomy\TermAccessControlHandler;

/**
 * Defines an extensible access control handler for taxonomy term entities.
 *
 * @see Term
 */
class ThunderTermAccessControlHandler extends TermAccessControlHandler {

  /**
   * The access control handler manager.
   *
   * @var \Drupal\thunder_ach\ThunderAccessControlHandlerManager
   */
  protected $manager;

  /**
   * List of applicable access control handlers.
   *
   * @var \Drupal\thunder_ach\Plugin\ThunderAccessControlHandlerInterface[]
   */
  protected $handlers = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeInterface $entity_type) {
    parent::__construct($entity_type);
    $this->manager = \Drupal::service('plugin.manager.thunder_ach');
    $this->handlers = $this->manager->getHandlers('taxonomy_term');
  }

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    $result = AccessResult::neutral();
    /* @var $handler \Drupal\thunder_ach\Plugin\ThunderAccessControlHandlerInterface */
    foreach ($this->handlers as $handler) {
      if (!$handler->applies($entity, $operation, $account)) {
        continue;
      }
      $result = $result->orIf($handler->checkAccess($entity, $operation, $account));
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    $result = AccessResult::neutral();
    /* @var $handler \Drupal\thunder_ach\Plugin\ThunderAccessControlHandlerInterface */
    foreach ($this->handlers as $handler) {
      $result = $result->orIf($handler->checkCreateAccess($account, $context, $entity_bundle));
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  protected function checkFieldAccess($operation, FieldDefinitionInterface $field_definition, AccountInterface $account, FieldItemListInterface $items = NULL) {
    $result = AccessResult::allowed();
    /* @var $handler \Drupal\thunder_ach\Plugin\ThunderAccessControlHandlerInterface */
    foreach ($this->handlers as $handler) {
      $result = $result->orIf($handler->checkFieldAccess($operation, $field_definition, $account, $items));
    }
    return $result;
  }

}
