<?php

namespace Drupal\thunder_demo\EventSubscriber;

use Drupal\default_content\Event\DefaultContentEvents;
use Drupal\default_content\Event\ImportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ImportSubscriber.
 */
class ImportSubscriber implements EventSubscriberInterface {

  /**
   * Publish imported articles.
   *
   * @param \Drupal\default_content\Event\ImportEvent $event
   *   The event entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function onImport(ImportEvent $event) {
    $uuids = [
      '0bd5c257-2231-450f-b4c2-ab156af7b78d',
      '36b2e2b2-3df0-43eb-a282-d792b0999c07',
      '94ad928b-3ec8-4bcb-b617-ab1607bf69cb',
      'bbb1ee17-15f8-46bd-9df5-21c58040d741',
    ];

    foreach ($event->getImportedEntities() as $entity) {
      if (in_array($entity->uuid(), $uuids)) {
        $entity->moderation_state->value = 'published';
        $entity->save();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[DefaultContentEvents::IMPORT][] = ['onImport'];
    return $events;
  }

}
