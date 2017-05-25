<?php

namespace Drupal\thunder_ach\Plugin;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the interface for featured content processor plugins.
 *
 * @see \Drupal\thunder_ach\Annotation\ThunderAccessControlHandler
 * @see \Drupal\thunder_ach\AccessControlHandlerManager
 * @see \Drupal\thunder_ach\Plugin\AccessControlHandlerBase
 * @see plugin_api
 */
interface ThunderAccessControlHandlerInterface extends ConfigurablePluginInterface, PluginInspectionInterface {

  /**
   * Whether the handler is applicable.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for which to check access.
   * @param string $operation
   *   The operation access should be checked for.
   *   Usually one of "view", "view label", "update" or "delete".
   * @param \Drupal\Core\Session\AccountInterface $account
   *   (optional) The user session for which to check access, or NULL to check
   *   access for the current user. Defaults to NULL.
   *
   * @return bool
   *   TRUE if the handler should control access.
   */
  public function applies(EntityInterface $entity, $operation, AccountInterface $account = NULL);

  /**
   * Get the entity type the handler controls access for.
   *
   * @return string
   *   Name of entity type (i.e. "node").
   */
  public function getEntityType();

  /**
   * Performs access checks.
   *
   * This method is supposed to be implemented by plugins that do their own
   * custom access checking.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for which to check access.
   * @param string $operation
   *   The entity operation. Usually one of 'view', 'view label', 'update' or
   *   'delete'.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user for which to check access.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function checkAccess(EntityInterface $entity, $operation, AccountInterface $account);

  /**
   * Performs create access checks.
   *
   * This method is supposed to be overwritten by plugins that do their own
   * custom access checking.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user for which to check access.
   * @param array $context
   *   An array of key-value pairs to pass additional context when needed.
   * @param string|null $entity_bundle
   *   (optional) The bundle of the entity. Required if the entity supports
   *   bundles, defaults to NULL otherwise.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL);

  /**
   * Default field access as determined by this access control handler.
   *
   * @param string $operation
   *   The operation access should be checked for.
   *   Usually one of "view" or "edit".
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user session for which to check access.
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   (optional) The field values for which to check access, or NULL if access
   *   is checked for the field definition, without any specific value
   *   available. Defaults to NULL.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function checkFieldAccess($operation, FieldDefinitionInterface $field_definition, AccountInterface $account, FieldItemListInterface $items = NULL);

}
