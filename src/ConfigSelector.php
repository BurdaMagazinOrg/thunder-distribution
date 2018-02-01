<?php

namespace Drupal\thunder;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

class ConfigSelector {
  use StringTranslationTrait;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var \Drupal\Core\Config\ConfigManagerInterface
   */
  protected $configManager;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;


  public function __construct(ConfigFactoryInterface $config_factory, ConfigManagerInterface $config_manager, EntityTypeManagerInterface $entity_type_manager, LoggerChannelInterface $logger, StateInterface $state) {
    $this->configFactory = $config_factory;
    $this->configManager = $config_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
    $this->state = $state;
  }

  /**
   * @return $this
   */
  public function setCurrentConfigList() {
    $this->state->set('thunder.current_config_list', $this->configFactory->listAll());
    return $this;
  }

  public function selectConfig() {
    $default_third_party_settings = ['feature' => FALSE, 'priority' => 0];
    $new_configuration_list = array_diff(
      $this->configFactory->listAll(),
      $this->state->get('thunder.current_config_list', [])
    );
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
      if ($thunder_settings['feature'] === FALSE) {
        // Configuration without a thunder config_select third party settings is
        // ignored.
        continue;
      }

      $entity_storage = $this->entityTypeManager->getStorage($config_entity->getEntityTypeId());
      $matching_config = $entity_storage
        ->getQuery()
        ->condition('third_party_settings.thunder.config_select.feature', $thunder_settings['feature'])
        ->condition('id', $config_entity->id(), '<>')
        ->condition('status', FALSE, '<>')
        ->execute();

      if (empty($matching_config)) {
        // No matches. Ignore.
        continue;
      }

      /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface[] $configs */
      $configs = $entity_storage->loadMultiple($matching_config);
      $configs[$config_entity->id()] = $config_entity;
      // Predictably sort the array by priority then config name.
      uksort($configs, function ($a, $b) use ($configs) {
        $a_priority = $configs[$a]->getThirdPartySetting('thunder', 'config_select')['priority'];
        $b_priority = $configs[$b]->getThirdPartySetting('thunder', 'config_select')['priority'];
        if ($a_priority === $b_priority) {
          return strcmp($a, $b);
        }
        return $a_priority < $b_priority ? -1 : 1;
      });
      // The last member of the array stay enabled.
      $active_config = array_pop($configs);
      foreach ($configs as $config) {
        $config->setStatus(FALSE)->save();
        $variables = [
          ':disabled_config_href' => $config->toUrl('edit-form')->toString(),
          '@disabled_config_label' => $config->label(),
          ':active_config_href' => $active_config->toUrl('edit-form')->toString(),
          '@active_config_label' => $active_config->label(),
        ];

        $this->logger->notice(
          'Configuration <a href=":disabled_config_href">@disabled_config_label</a> has been disabled in favor of <a href=":active_config_href">@active_config_label</a>',
          $variables
        );
        $this->drupalSetMessage($this->t(
          'Configuration <a href=":disabled_config_href">@disabled_config_label</a> has been disabled in favor of <a href=":active_config_href">@active_config_label</a>',
          $variables
        ));
      }
    }
    return $this;
  }

  protected function drupalSetMessage($message = NULL, $type = 'status', $repeat = FALSE) {
    drupal_set_message($message, $type, $repeat);
  }

}