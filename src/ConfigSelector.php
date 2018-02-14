<?php

namespace Drupal\thunder;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Selects configuration to enable after a module install or uninstall.
 *
 * Uses the Thunder feature name and priority to select which configuration
 * should be enabled after a module install or uninstall. The Thunder feature
 * name and priority are stored in a configuration entity's third party
 * settings. For example:
 * @code
 * third_party_settings:
 *   thunder:
 *     config_select:
 *       feature: thunder_feature_name
 *       priority: 1000
 * @endcode
 */
class ConfigSelector {
  use StringTranslationTrait;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The config manager.
   *
   * @var \Drupal\Core\Config\ConfigManagerInterface
   */
  protected $configManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * ConfigSelector constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Config\ConfigManagerInterface $config_manager
   *   The config manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The logger.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ConfigManagerInterface $config_manager, EntityTypeManagerInterface $entity_type_manager, LoggerChannelInterface $logger, StateInterface $state) {
    $this->configFactory = $config_factory;
    $this->configManager = $config_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
    $this->state = $state;
  }

  /**
   * Stores a list of active configuration prior to module installation.
   *
   * The list makes it simple to work out what configuration is new and if we
   * have to enable or disable any configuration.
   *
   * @param string $module
   *   The module being installed.
   *
   * @return $this
   *
   * @see thunder_module_preinstall()
   */
  public function setCurrentConfigList($module) {
    if ($module === 'thunder') {
      // If the Thunder install profile is being installed we need to process
      // all existing configuration in
      // \Drupal\thunder\ConfigSelector::selectConfig().
      $list = [];
    }
    else {
      $list = $this->configFactory->listAll();
    }
    $this->state->set('thunder.current_config_list', $list);
    return $this;
  }

  /**
   * Determines if Thunder features might be removed as part of an uninstall.
   *
   * Stores a list of affected features keyed by full configuration object name.
   *
   * @param string $module
   *   The module being uninstalled.
   *
   * @return $this
   *
   * @see thunder_module_preuninstall()
   */
  public function setUninstallConfigList($module) {
    // Get a list of config entities that might be deleted.
    $config_entities = $this->configManager->findConfigEntityDependentsAsEntities('module', [$module]);
    // We need to keep adding to the list since more than one module might be
    // uninstalled at a time.
    $features = $this->state->get('thunder.feature_uninstall_list', []);;
    $default_third_party_settings = ['feature' => FALSE, 'priority' => 0];
    foreach ($config_entities as $config_entity) {
      if (!$config_entity->status()) {
        // We are only interested in enabled configuration entities, ie.
        // functionality a user might lose.
        continue;
      }
      $thunder_settings = $config_entity->getThirdPartySetting('thunder', 'config_select', $default_third_party_settings);
      if ($thunder_settings['feature'] !== FALSE) {
        $features[$config_entity->getConfigDependencyName()] = $thunder_settings['feature'];
      }
    }
    $this->state->set('thunder.feature_uninstall_list', $features);
    return $this;
  }

  /**
   * Selects configuration to enable after uninstalling a module.
   *
   * @return $this
   *
   * @see thunder_modules_uninstalled()
   */
  public function selectConfigOnUninstall() {
    $features = $this->state->get('thunder.feature_uninstall_list', []);
    foreach ($features as $config_entity_id => $feature) {
      $entity_type_id = $this->configManager->getEntityTypeIdByName($config_entity_id);
      if (!$entity_type_id) {
        // The entity type no longer exists there will not be any replacement
        // config.
        continue;
      }

      // Get all the possible configuration for the feature.
      $entity_storage = $this->entityTypeManager->getStorage($entity_type_id);
      $matching_config = $entity_storage
        ->getQuery()
        ->condition('third_party_settings.thunder.config_select.feature', $feature)
        ->execute();
      /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface[] $configs */
      $configs = $entity_storage->loadMultiple($matching_config);
      $this->sortConfigEntities($configs);

      // If any of the configuration is enabled there is nothing to do here.
      foreach ($configs as $config) {
        if ($config->status()) {
          continue 2;
        }
      }

      // No configuration is enabled. Enable the highest priority one.
      $highest_priority_config = array_pop($configs);
      $highest_priority_config->setStatus(TRUE)->save();
      $variables = [
        ':active_config_href' => $highest_priority_config->toUrl('edit-form')->toString(),
        '@active_config_label' => $highest_priority_config->label(),
      ];
      $this->logger->info(
        'Configuration <a href=":active_config_href">@active_config_label</a> has been enabled.',
        $variables
      );
      $this->drupalSetMessage($this->t(
        'Configuration <a href=":active_config_href">@active_config_label</a> has been enabled.',
        $variables
      ));
    }
    // Reset the list.
    $this->state->set('thunder.feature_uninstall_list', []);
    return $this;
  }

  /**
   * Selects configuration to enable and disable after installing a module.
   *
   * @return $this
   *
   * @see thunder_modules_installed()
   */
  public function selectConfig() {
    $default_third_party_settings = ['feature' => FALSE, 'priority' => 0];
    $new_configuration_list = array_diff(
      $this->configFactory->listAll(),
      $this->state->get('thunder.current_config_list', [])
    );
    // Build a list of thunder feature names of the configuration that's been
    // imported.
    $thunder_features = [];
    foreach ($new_configuration_list as $config_name) {
      /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface $config_entity */
      $config_entity = $this->configManager->loadConfigEntityByName($config_name);
      if (!$config_entity) {
        // Simple configuration is ignored.
        continue;
      }
      if (!$config_entity->status()) {
        // Disabled configuration is ignored.
        continue;
      }
      $thunder_settings = $config_entity->getThirdPartySetting('thunder', 'config_select', $default_third_party_settings);
      if ($thunder_settings['feature'] !== FALSE) {
        $thunder_features[] = $thunder_settings['feature'];
      }
    }
    // It is possible that the module or profile installed has multiple
    // configurations for the same feature.
    $thunder_features = array_unique($thunder_features);

    // Process each thunder feature and choose the configuration with the
    // highest priority.
    foreach ($thunder_features as $thunder_feature) {
      $entity_storage = $this->entityTypeManager->getStorage($config_entity->getEntityTypeId());
      $matching_config = $entity_storage
        ->getQuery()
        ->condition('third_party_settings.thunder.config_select.feature', $thunder_feature)
        ->condition('status', FALSE, '<>')
        ->execute();

      /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface[] $configs */
      $configs = $entity_storage->loadMultiple($matching_config);
      $configs = $this->sortConfigEntities($configs);

      // The last member of the array has the highest priority and should remain
      // enabled.
      $active_config = array_pop($configs);
      foreach ($configs as $config) {
        $config->setStatus(FALSE)->save();
        $variables = [
          ':disabled_config_href' => $config->toUrl('edit-form')->toString(),
          '@disabled_config_label' => $config->label(),
          ':active_config_href' => $active_config->toUrl('edit-form')->toString(),
          '@active_config_label' => $active_config->label(),
        ];

        $this->logger->info(
          'Configuration <a href=":disabled_config_href">@disabled_config_label</a> has been disabled in favor of <a href=":active_config_href">@active_config_label</a>.',
          $variables
        );
        $this->drupalSetMessage($this->t(
          'Configuration <a href=":disabled_config_href">@disabled_config_label</a> has been disabled in favor of <a href=":active_config_href">@active_config_label</a>.',
          $variables
        ));
      }
    }
    return $this;
  }

  /**
   * Wraps drupal_set_message().
   *
   * @param string|\Drupal\Component\Render\MarkupInterface $message
   *   (optional) The translated message to be displayed to the user. For
   *   consistency with other messages, it should begin with a capital letter
   *   and end with a period.
   * @param string $type
   *   (optional) The message's type. Defaults to 'status'.
   * @param bool $repeat
   *   (optional) If this is FALSE and the message is already set, then the
   *   message won't be repeated. Defaults to FALSE.
   *
   * @see drupal_set_message()
   */
  protected function drupalSetMessage($message = NULL, $type = 'status', $repeat = FALSE) {
    drupal_set_message($message, $type, $repeat);
  }

  /**
   * Sorts an array of configuration entities by priority then config name.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface[] $configs
   *   Array of configuration entities to sort.
   *
   * @return \Drupal\Core\Config\Entity\ConfigEntityInterface[]
   *   The sorted array of configuration entities.
   */
  protected function sortConfigEntities(array $configs) {
    uksort($configs, function ($a, $b) use ($configs) {
      $a_priority = $configs[$a]->getThirdPartySetting('thunder', 'config_select')['priority'];
      $b_priority = $configs[$b]->getThirdPartySetting('thunder', 'config_select')['priority'];
      if ($a_priority === $b_priority) {
        return strcmp($a, $b);
      }
      return $a_priority < $b_priority ? -1 : 1;
    });
    return $configs;
  }

}
