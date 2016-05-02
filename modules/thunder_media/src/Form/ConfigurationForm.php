<?php

/**
 * @file
 * Contains Drupal\thunder_media\Form\ConfigurationForm.
 */

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

    $form['enable_filename_transliteration'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable filename transliteration'),
      '#description' => t('Enable this checkbox to clean filenames before saving the files.'),
      '#default_value' => $config->get('enable_filename_transliteration')
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('thunder_media.settings')
      ->set('enable_filename_transliteration', $form_state->getValue('enable_filename_transliteration'))
      ->save();
  }

}
