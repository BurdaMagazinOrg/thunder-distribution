<?php

namespace Drupal\thunder_updater\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the Contact entity.
 *
 * We left this entity type, to simplify migration of content.
 *
 * @ContentEntityType(
 *   id = "thunder_updater_update",
 *   label = @Translation("Update"),
 *   base_table = "thunder_updater_update",
 *   fieldable = FALSE,
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid"
 *   }
 * )
 */
class Update extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Update entity.'))
      ->setReadOnly(TRUE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 50,
        'text_processing' => 0,
      ]);

    // Standard field, unique outside of the scope of the current project.
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Update entity.'))
      ->setReadOnly(TRUE);

    $fields['successful_by_hook'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Successful by Hook'))
      ->setDescription(t('Indicates if the update hook was successful.'));

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code of Update entity.'));
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
