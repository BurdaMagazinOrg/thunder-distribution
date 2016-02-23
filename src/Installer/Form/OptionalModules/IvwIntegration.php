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
class IvwIntegration extends AbstractOptionalModule {

  public function getFormId() {

    return 'ivw_integration';
  }

  public function getFormName() {
    return 'IVW Integration';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ivw_integration.settings',
    ];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['ivw_integration'] = array(
      '#type' => 'details',
      '#title' => $this->t('IVW'),
      '#open' => TRUE,
      '#states' => array(
        'visible' => array(
          ':input[name="install_modules[ivw_integration]"]' => array('checked' => TRUE),
        ),
      )
    );
    $form['ivw_integration']['ivw_site'] = array(
      '#type' => 'textfield',
      '#title' => t('IVW Site name'),
      '#description' => t('Site name as given by IVW, this is used as default for the "st" parameter in the iam_data object')
    );

    $form['ivw_integration']['mobile_site'] = array(
      '#type' => 'textfield',
      '#title' => t('IVW Mobile Site name'),
      '#description' => t('Mobile site name as given by IVW, this is used as default for the "st" parameter in the iam_data object')
    );

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {


    $this->config('ivw_integration.settings')
      ->set('site', (string) $form_state->getValue('ivw_site'))
      ->set('mobile_site', (string) $form_state->getValue('mobile_site'))
      ->save(TRUE);

    $fieldStorage = \Drupal::entityTypeManager()
      ->getStorage('field_storage_config')
      ->load('node.field_ivw');
    if (empty($fieldStorage)) {
      $fieldStorageDefinition = array(
        'field_name' => 'field_ivw',
        'entity_type' => 'node',
        'type' => 'ivw_integration_settings',
      );
      $fieldStorage = \Drupal::entityTypeManager()
        ->getStorage('field_storage_config')
        ->create($fieldStorageDefinition);
      $fieldStorage->save();
    }
    $fieldDefinition = array(
      'label' => 'IVW settings',
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
      ->setComponent('field_ivw', array(
        'type' => 'ivw_integration_widget',
      ))
      ->save();
  }

}
