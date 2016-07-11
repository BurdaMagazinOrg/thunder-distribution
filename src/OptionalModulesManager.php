<?php

namespace Drupal\thunder;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides an optional modules plugin manager.
 */
class OptionalModulesManager extends DefaultPluginManager {

  /**
   * Constructs a OptionalModulesManager object.
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
    parent::__construct(
      'Plugin/Thunder/OptionalModule',
      $namespaces,
      $module_handler,
      'Drupal\thunder\Plugin\Thunder\OptionalModule\AbstractOptionalModule',
      'Drupal\thunder\Annotation\ThunderOptionalModule'
    );
    $this->alterInfo('thunder_optional_module_info');
    $this->setCacheBackend($cache_backend, 'thunder_optional_module_plugins');

  }

}
