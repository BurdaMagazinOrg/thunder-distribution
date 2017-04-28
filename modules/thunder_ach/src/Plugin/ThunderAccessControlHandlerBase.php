<?php

namespace Drupal\thunder_ach\Plugin;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\thunder_ach\Plugin\ThunderAccessControlHandlerInterface;

/**
 * Provides a base class for ThunderAccessControlHandler plugins.
 *
 * @see \Drupal\thunder_ach\Annotation\ThunderAccessControlHandler
 * @see \Drupal\thunder_ach\ThunderAccessControlHandlerPluginManager
 * @see \Drupal\thunder_ach\ThunderAccessControlHandlerInterface
 * @see plugin_api
 */
abstract class ThunderAccessControlHandlerBase extends PluginBase implements ThunderAccessControlHandlerInterface {

  /**
   * The plugin ID of this processor.
   *
   * @var string
   */
  protected $plugin_id;

  /**
   * Name of the entity type the handler controls access for.
   *
   * @var string
   */
  public $entity_type;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    if (isset($configuration['entity_type'])) {
      $this->entity_type = $configuration['entity_type'];
    }
    if (isset($configuration['settings'])) {
      $this->settings = (array) $configuration['settings'];
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return [
      'id' => $this->getPluginId(),
      'entity_type' => $this->entity_type,
      'settings' => $this->settings,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'entity_type' => $this->pluginDefinition['entity_type'],
      'settings' => $this->pluginDefinition['settings'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityType() {
    return $this->entity_type;
  }

  /**
   * {@inheritdoc}
   */
  public function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  public function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  public function checkFieldAccess($operation, FieldDefinitionInterface $field_definition, AccountInterface $account, FieldItemListInterface $items = NULL) {
    return AccessResult::neutral();
  }

}
