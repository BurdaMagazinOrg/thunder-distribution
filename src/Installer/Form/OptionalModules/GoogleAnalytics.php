<?php

namespace Drupal\thunder\Installer\Form\OptionalModules;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * @file
 * Contains
 */
class GoogleAnalytics extends AbstractOptionalModule {

  public function getFormId() {

    return 'google_analytics';
  }

  public function getFormName() {
    return 'Google Analytics';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'google_analytics.settings',
    ];
  }


  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['google_analytics'] = array(
      '#type' => 'details',
      '#title' => $this->t('Google Analytics'),
      '#open' => TRUE,
      '#states' => array(
        'visible' => array(
          ':input[name="install_modules[google_analytics]"]' => array('checked' => TRUE),
        ),
      ),
    );
    $form['google_analytics']['ga_account'] = array(
      '#description' => t('This ID is unique to each site you want to track separately, and is in the form of UA-xxxxxxx-yy. To get a Web Property ID, <a href=":analytics" target="_blank">register your site with Google Analytics</a>, or if you already have registered your site, go to your Google Analytics Settings page to see the ID next to every site profile. <a href=":webpropertyid"  target="_blank">Find more information in the documentation</a>.', [
        ':analytics' => 'http://www.google.com/analytics/',
        ':webpropertyid' => Url::fromUri('https://developers.google.com/analytics/resources/concepts/gaConceptsAccounts', ['fragment' => 'webProperty'])
          ->toString()
      ]),
      '#maxlength' => 20,
      '#placeholder' => 'UA-',
      '#size' => 15,
      '#title' => t('Web Property ID'),
      '#type' => 'textfield',
    );

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->config('google_analytics.settings')
      ->set('account', (string) $form_state->getValue('ga_account'))
      ->save(TRUE);
  }

}
