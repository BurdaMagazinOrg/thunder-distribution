<?php

namespace Drupal\thunder_ach;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages access control handlers.
 *
 * @see hook_featured_content_processor_info_alter()
 * @see \Drupal\thunder_ach\Annotation\ThunderAccessControlHandler
 * @see \Drupal\thunder_ach\Plugin\ThunderAccessControlHandlerInterface
 * @see \Drupal\thunder_ach\Plugin\ThunderAccessControlHandlerBase
 * @see plugin_api
 */
class ThunderAccessControlHandlerManager extends DefaultPluginManager {

  /**
   * Constructs a ThunderAccessControlHandlerManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/thunder_ach', $namespaces, $module_handler, 'Drupal\thunder_ach\Plugin\ThunderAccessControlHandlerInterface', 'Drupal\thunder_ach\Annotation\ThunderAccessControlHandler');
    $this->alterInfo('thunder_ach_handler_info');
    $this->setCacheBackend($cache_backend, 'thunder_ach_handlers');
  }

  /**
   * Get a list of all registered handler instances sorted by weight.
   *
   * @param string $entity_type
   *   (Optional) Limit handlers to the given entity type.
   *
   * @return \Drupal\thunder_ach\Plugin\ThunderAccessControlHandlerInterface[]
   *   List of processor plugin instances, optionally limited to an entity type.
   */
  public function getHandlers($entity_type = NULL) {
    $instances = &drupal_static(__FUNCTION__, []);
    if (!empty($instances)) {
      if (empty($entity_type)) {
        return $instances;
      }
      return $this->limitHandlers($instances, $entity_type);
    }
    /* @var $handlers \Drupal\thunder_ach\Plugin\ThunderAccessControlHandlerInterface[] */
    $handlers = $this->getDefinitions();
    if (!empty($entity_type)) {
      $handlers = $this->limitHandlers($handlers, $entity_type);
    }
    uasort($handlers, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
    foreach ($handlers as $plugin_id => $handler) {
      // Execute the processor plugin.
      $instances[$plugin_id] = $this->createInstance($plugin_id, $handler);
    }

    return $instances;
  }

  /**
   * Limit access control handlers to a single entity type.
   *
   * @param \Drupal\thunder_ach\Plugin\ThunderAccessControlHandlerInterface[] $handlers
   *   List of access control handlers.
   * @param string $entity_type
   *   Name of entity type.
   *
   * @return \Drupal\thunder_ach\Plugin\ThunderAccessControlHandlerInterface[]
   *   List of access control handlers for the given entity type.
   */
  protected function limitHandlers(array $handlers, $entity_type) {
    return array_filter($handlers, function ($handler) use ($entity_type) {
      if (is_array($handler)) {
        return $entity_type === $handler['entity_type'];
      }
      /* @var $handler \Drupal\thunder_ach\Plugin\ThunderAccessControlHandlerInterface */
      return $entity_type === $handler->getEntityType();
    });
  }

}
