<?php

namespace Drupal\thunder\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\ConfigInstallerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Installs optional config on config save.
 */
class ConfigImportSubscriber implements EventSubscriberInterface {

  protected $configInstaller;

  /**
   * ConfigImportSubscriber constructor.
   *
   * @param \Drupal\Core\Config\ConfigInstallerInterface $config_installer
   *   Config installer service.
   */
  public function __construct(ConfigInstallerInterface $config_installer) {
    $this->configInstaller = $config_installer;
  }

  /**
   * Installs optional config.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   The Event to process.
   */
  public function onConfigSave(ConfigCrudEvent $event) {
    $this->configInstaller->installOptionalConfig(NULL, ['config' => $event->getConfig()->getName()]);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ConfigEvents::SAVE][] = ['onConfigSave', 40];
    return $events;
  }

}
