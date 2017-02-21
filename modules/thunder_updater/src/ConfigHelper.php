<?php

namespace Drupal\thunder_updater;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Config\ConfigNameException;
use Drupal\Core\Config\StorageInterface;

/**
 * Contains methods to work with config objects.
 */
class ConfigHelper {

  /**
   * The active configuration storage.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $activeStorage;

  /**
   * Creates ConfigComparator objects.
   *
   * @param \Drupal\Core\Config\StorageInterface $active_storage
   *   The active configuration storage.
   */
  public function __construct(StorageInterface $active_storage) {
    $this->activeStorage = $active_storage;
  }

  /**
   * {@inheritdoc}
   */
  public function isModified($config_name, $hash = NULL) {
    $active = $this->activeStorage->read($config_name);

    if (!$active) {
      throw new ConfigNameException(
        sprintf('Configuration does not exist for "%s".', $config_name)
      );
    }

    if (!$hash) {
      // Get the hash created when the config was installed.
      $original_hash = $active['_core']['default_config_hash'];
    }
    else {
      $original_hash = $hash;
    }

    // Remove export keys not used to generate default config hash.
    unset($active['uuid']);
    unset($active['_core']);
    $active_hash = Crypt::hashBase64(serialize($active));

    return $original_hash !== $active_hash;

  }

}
