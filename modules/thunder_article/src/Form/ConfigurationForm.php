<?php

namespace Drupal\thunder_article\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ConfigurationForm.
 */
class ConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'thunder_article.settings',
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
    $config = $this->config('thunder_article.settings');

    $form['move_scheduler_local_task'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Move scheduler to local task of content list'),
      '#description' => $this->t('Disable this checkbox to get the scheduler default behavior.'),
      '#default_value' => $config->get('move_scheduler_local_task'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('thunder_article.settings')
      ->set('move_scheduler_local_task', $form_state->getValue('move_scheduler_local_task'))
      ->save();
  }

}
