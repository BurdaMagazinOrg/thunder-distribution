<?php

namespace Drupal\thunder_updater\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the Contact entity.
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
class Update extends ContentEntityBase implements UpdateInterface {

  /**
   * {@inheritdoc}
   *
   * When a new entity instance is added, set the user_id entity reference to
   * the current user as the creator of the instance.
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getChangedTime() {
    return $this->get('changed')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setChangedTime($timestamp) {
    $this->set('changed', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getChangedTimeAcrossTranslations() {
    $changed = $this->getUntranslated()->getChangedTime();
    foreach ($this->getTranslationLanguages(FALSE) as $language) {
      $translation_changed = $this->getTranslation($language->getId())
        ->getChangedTime();
      $changed = max($translation_changed, $changed);
    }
    return $changed;
  }

  /**
   * {@inheritdoc}
   */
  public function wasSuccessfulByHook() {
    return $this->get('successful_by_hook')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setSuccessfulByHook($success) {
    $this->set('successful_by_hook', $success);
    return $this;
  }

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
