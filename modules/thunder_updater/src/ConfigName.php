<?php

namespace Drupal\thunder_updater;

/**
 * Configuration name class for easier handling of configuration references.
 *
 * @package Drupal\thunder_updater
 */
class ConfigName {

  const SYSTEM_SIMPLE_CONFIG = 'system.simple';

  /**
   * Config type.
   *
   * @var string
   */
  protected $type;

  /**
   * Config name.
   *
   * @var string
   */
  protected $name;

  /**
   * Entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Create ConfigName instance from full configuration name.
   *
   * @param string $fullConfigName
   *   Full config name.
   *
   * @return ConfigName
   *   Return instance of ConfigName.
   */
  public static function createByFullName($fullConfigName) {
    $configName = new static();

    $configPair = $configName->parseFullName($fullConfigName);

    $configName->type = $configPair['type'];
    $configName->name = $configPair['name'];

    return $configName;
  }

  /**
   * Create ConfigName instance from configuration type and name.
   *
   * @param string $configType
   *   Config type.
   * @param string $configName
   *   Config name.
   *
   * @return ConfigName
   *   Return instance of ConfigName.
   */
  public static function createByTypeName($configType, $configName) {
    $configNameInstance = new static();

    $configNameInstance->type = $configType;
    $configNameInstance->name = $configName;

    return $configNameInstance;
  }

  /**
   * Parse full config name and create array with config type and name.
   *
   * @param string $fullConfigName
   *   Full config name.
   *
   * @return array
   *   Returns array with config type and name.
   */
  protected function parseFullName($fullConfigName) {
    $result = [
      'type' => static::SYSTEM_SIMPLE_CONFIG,
      'name' => $fullConfigName,
    ];

    $prefix = static::SYSTEM_SIMPLE_CONFIG . '.';
    if (strpos($fullConfigName, $prefix)) {
      $result['name'] = substr($fullConfigName, strlen($prefix));
    }
    else {
      foreach ($this->entityTypeManager()->getDefinitions() as $entityType => $definition) {
        if ($definition->entityClassImplements('Drupal\Core\Config\Entity\ConfigEntityInterface')) {
          $prefix = $definition->getConfigPrefix() . '.';
          if (strpos($fullConfigName, $prefix) === 0) {
            $result['type'] = $entityType;
            $result['name'] = substr($fullConfigName, strlen($prefix));
          }
        }
      }
    }

    return $result;
  }

  /**
   * Create full configuration name from config type and name.
   *
   * @param string $type
   *   Config type.
   * @param string $name
   *   Config name.
   *
   * @return string
   *   Returns full configuration name.
   */
  protected function generateFullName($type, $name) {
    if ($type == 'system.simple' || !$type) {
      return $name;
    }

    $definition = $this->entityTypeManager()->getDefinition($type);
    $prefix = $definition->getConfigPrefix() . '.';

    return $prefix . $name;
  }

  /**
   * Retrieves the entity manager service.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity manager service.
   */
  protected function entityTypeManager() {
    if (!$this->entityTypeManager) {
      $this->entityTypeManager = \Drupal::service('entity_type.manager');
    }

    return $this->entityTypeManager;
  }

  /**
   * Get full configuration name.
   *
   * @return string
   *   Returns full configuration name.
   */
  public function getFullName() {
    return $this->generateFullName($this->type, $this->name);
  }

  /**
   * Get configuration type.
   *
   * @return string
   *   Config type.
   */
  public function getType() {
    return $this->type;
  }

  /**
   * Get configuration name.
   *
   * @return string
   *   Config name.
   */
  public function getName() {
    return $this->name;
  }

}
