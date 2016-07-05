<?php

namespace Drupal\thunder\Installer\Form\OptionalModules;


use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AbstractOptionalModule.
 *
 * @package Drupal\thunder\Installer\Form\OptionalModules
 */
abstract class AbstractOptionalModule extends ConfigFormBase {

  /**
   * Returns name of the form.
   *
   * @return string
   *   Form name.
   */
  abstract public function getFormName();

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [];
  }

  /**
   * Add custom field for form.
   *
   * @param string $entityType
   *   Entity type.
   * @param string $entityBundle
   *   Entity type.
   * @param string $fieldName
   *   Field name.
   * @param string $fieldLabel
   *   Field label.
   * @param string $fieldType
   *   Field type.
   * @param string $fieldWidget
   *   Field widget.
   */
  protected function addField($entityType, $entityBundle, $fieldName, $fieldLabel, $fieldType, $fieldWidget) {

    $fieldStorage = \Drupal::entityTypeManager()
      ->getStorage('field_storage_config')
      ->load("$entityType.$fieldName");
    if (empty($fieldStorage)) {
      $fieldStorageDefinition = array(
        'field_name' => $fieldName,
        'entity_type' => $entityType,
        'type' => $fieldType,
      );
      $fieldStorage = \Drupal::entityTypeManager()
        ->getStorage('field_storage_config')
        ->create($fieldStorageDefinition);
      $fieldStorage->save();
    }
    $fieldDefinition = array(
      'label' => $fieldLabel,
      'field_name' => $fieldStorage->getName(),
      'entity_type' => $entityType,
      'bundle' => $entityBundle,
      'settings' => [
        'display_default' => '1',
        'display_field' => '1',
      ],
    );
    $field = entity_create('field_config', $fieldDefinition);
    $field->save();
    entity_get_form_display($entityType, $entityBundle, 'default')
      ->setComponent($fieldName, array(
        'type' => $fieldWidget,
      ))
      ->save();
  }

  /**
   * Check is optional module enabled.
   *
   * @return int
   *   Return status as int 0|1.
   */
  public function isEnabled() {
    return 0;
  }

}
