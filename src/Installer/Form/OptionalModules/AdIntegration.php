<?php

namespace Drupal\thunder\Installer\Form\OptionalModules;

use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * @file
 * Contains
 */
class AdIntegration extends AbstractOptionalModule {

  public function getFormId() {

    return 'ad_integration';
  }

  public function getFormName() {
    return 'Ad Integration';
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $fieldStorage = \Drupal::entityTypeManager()
      ->getStorage('field_storage_config')
      ->load('node.field_ad_integration');
    if (empty($fieldStorage)) {
      $fieldStorageDefinition = array(
        'field_name' => 'field_ad_integration',
        'entity_type' => 'node',
        'type' => 'ad_integration_settings',
      );
      $fieldStorage = \Drupal::entityTypeManager()
        ->getStorage('field_storage_config')
        ->create($fieldStorageDefinition);
      $fieldStorage->save();
    }
    $fieldDefinition = array(
      'label' => 'Ad settings',
      'field_name' => $fieldStorage->getName(),
      'entity_type' => 'node',
      'bundle' => 'article',
      'settings' => [
        'display_default' => '1',
        'display_field' => '1',
      ]
    );
    $field = entity_create('field_config', $fieldDefinition);
    $field->save();
    entity_get_form_display('node', 'article', 'default')
      ->setComponent('field_ad_integration', array(
        'type' => 'ad_integration_widget',
      ))
      ->save();

  }
}
