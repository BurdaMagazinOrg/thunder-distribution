<?php

namespace Drupal\thunder_media\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\views\Entity\View;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ConfigEventsSubscriber.
 *
 * @package Drupal\thunder_media\EventSubscriber
 */
class ConfigEventsSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   *
   * @return array
   *   The event names to listen for, and the methods that should be executed.
   */
  public static function getSubscribedEvents() {
    return [
      ConfigEvents::DELETE => 'configDelete',
    ];
  }

  /**
   * React to a config object being deleted.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   Config crud event.
   */
  public function configDelete(ConfigCrudEvent $event) {
    $config = $event->getConfig();
    if ($config->getName() == 'views.view.thunder_media' && ($media_view = View::load('media'))) {
      $media_view->setStatus(TRUE)->save();
    }
  }

}
