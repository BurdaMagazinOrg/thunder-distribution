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
class RiddleIntegration extends AbstractOptionalModule {

  public function getFormId() {

    return 'riddle_marketplace';
  }

  public function getFormName() {
    return 'Riddle Integration';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'riddle_marketplace.settings',
    ];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['riddle_marketplace'] = array(
      '#type' => 'details',
      '#title' => $this->t('Riddle'),
      '#open' => TRUE,
      '#states' => array(
        'visible' => array(
          ':input[name="install_modules[riddle_marketplace]"]' => array('checked' => TRUE),
        ),
      )
    );

    $form['riddle_marketplace']['riddle_token'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Riddle token'),
      '#description' => $this->t('Goto Riddle.com and get a token from the Account->Plugins page (you may need to reset to get the first token)'),
    );

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->config('riddle_marketplace.settings')
      ->set('token', (string) $form_state->getValue('riddle_token'))
      ->save(TRUE);
  }
}
