<?php

namespace Drupal\thunder\Installer\Form\OptionalModules;

use Drupal\Core\Form\FormStateInterface;

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

    $this->addField('node', 'article', 'field_ivw', 'IVW settings', 'ivw_integration_settings', 'ivw_integration_widget');
    $this->addField('taxonomy_term', 'channel', 'field_ivw', 'IVW settings', 'ivw_integration_settings', 'ivw_integration_widget');

  }

}
