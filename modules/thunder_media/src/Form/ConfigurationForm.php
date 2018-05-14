<?php

namespace Drupal\thunder_media\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ConfigurationForm.
 *
 * @package Drupal\thunder_media\Form
 */
class ConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'thunder_media.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'configuration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('thunder_media.settings');

    $form['enable_filefield_remove_button'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable file field remove button'),
      '#description' => $this->t('Enable this checkbox to enable remove buttons for file fields on inline entity forms.'),
      '#default_value' => $config->get('enable_filefield_remove_button'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('thunder_media.settings')
      ->set('enable_filefield_remove_button', $form_state->getValue('enable_filefield_remove_button'))
      ->save();
  }

}
