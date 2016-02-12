<?php

/**
 * @file
 * Contains \Drupal\thunder\Installer\Form\SiteConfigureForm.
 */

namespace Drupal\thunder\Installer\Form;

use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Drupal\user\UserStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the site configuration form.
 */
class ModuleConfigureForm extends ConfigFormBase {

  /**
   * The site path.
   *
   * @var string
   */
  protected $sitePath;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The module installer.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;



  /**
   * The app root.
   *
   * @var string
   */
  protected $root;

  /**
   * Constructs a new SiteConfigureForm.
   *
   * @param string $root
   *   The app root.
   * @param string $site_path
   *   The site path.
   * @param \Drupal\user\UserStorageInterface $user_storage
   *   The user storage.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $module_installer
   *   The module installer.
   * @param \Drupal\Core\Locale\CountryManagerInterface $country_manager
   *   The country manager.
   */
  public function __construct($root, $site_path, UserStorageInterface $user_storage, StateInterface $state, ModuleInstallerInterface $module_installer) {
    $this->root = $root;
    $this->sitePath = $site_path;
    $this->userStorage = $user_storage;
    $this->state = $state;
    $this->moduleInstaller = $module_installer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('app.root'),
      $container->get('site.path'),
      $container->get('entity.manager')->getStorage('user'),
      $container->get('state'),
      $container->get('module_installer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'thunder_module_configure_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'acquia_lift.settings',
      'ivw_integration.settings',
      'google_analytics.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    drupal_get_messages();

    $form['#title'] = $this->t('Configure modules');

    $form['google_analytics'] = array(
      '#type' => 'details',
      '#title' => $this->t('Google Analytics'),
      '#open' => FALSE,
    );
    $form['google_analytics']['ga_account'] = array(
      '#description' => t('This ID is unique to each site you want to track separately, and is in the form of UA-xxxxxxx-yy. To get a Web Property ID, <a href=":analytics">register your site with Google Analytics</a>, or if you already have registered your site, go to your Google Analytics Settings page to see the ID next to every site profile. <a href=":webpropertyid">Find more information in the documentation</a>.', [':analytics' => 'http://www.google.com/analytics/', ':webpropertyid' => Url::fromUri('https://developers.google.com/analytics/resources/concepts/gaConceptsAccounts', ['fragment' => 'webProperty'])->toString()]),
      '#maxlength' => 20,
      '#placeholder' => 'UA-',
      '#size' => 15,
      '#title' => t('Web Property ID'),
      '#type' => 'textfield',
    );


    $form['ivw'] = array(
      '#type' => 'details',
      '#title' => $this->t('IVW'),
      '#open' => FALSE,
    );
    $form['ivw']['ivw_site'] = array(
      '#type' => 'textfield',
      '#title' => t('IVW Site name'),
      '#description' => t('Site name as given by IVW, this is used as default for the "st" parameter in the iam_data object')
    );


    $form['acquia_lift'] = array(
      '#type' => 'details',
      '#title' => $this->t('Acquia Lift'),
      '#open' => FALSE,
    );
    $form['acquia_lift']['al_account_name'] = [
      '#type' => 'textfield',
      '#title' => t('Account Name'),
    ];
    $form['acquia_lift']['al_customer_site'] = [
      '#type' => 'textfield',
      '#title' => t('Customer Site'),
    ];
    $form['acquia_lift']['al_api_url'] = [
      '#type' => 'textfield',
      '#title' => t('API URL'),
      '#field_prefix' => 'http(s)://',
    ];
    $form['acquia_lift']['al_access_key'] = [
      '#type' => 'textfield',
      '#title' => t('API Access Key'),
    ];
    $form['acquia_lift']['al_secret_key'] = [
      '#type' => 'password',
      '#title' => t('API Secret Key'),
      '#description' => !empty($credential_settings['secret_key']) ? t('Only necessary if updating') : '',
    ];
    $form['acquia_lift']['al_js_path'] = [
      '#type' => 'textfield',
      '#title' => t('JavaScript Path'),
      '#field_prefix' => 'http(s)://',
    ];


    $form['actions'] = array('#type' => 'actions');
    $form['actions']['save'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save and continue'),
      '#button_type' => 'primary',

      '#submit' => array('::submitForm'),
    );

    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->config('google_analytics.settings')
      ->set('account', (string) $form_state->getValue('ga_account'))
      ->save(TRUE);

    $this->config('ivw_integration.settings')
      ->set('site', (string) $form_state->getValue('ivw_site'))
      ->save(TRUE);

    $this->config('acquia_lift.settings')
      ->set('credential.account_name', (string) $form_state->getValue('al_account_name'))
      ->set('credential.customer_site', (string) $form_state->getValue('al_customer_site'))
      ->set('credential.api_url', (string) $form_state->getValue('al_api_url'))
      ->set('credential.access_key', (string) $form_state->getValue('al_access_key'))
      ->set('credential.secret_key', (string) $form_state->getValue('al_secret_key'))
      ->set('credential.js_path', (string) $form_state->getValue('al_js_path'))
      ->save(TRUE);

  }

}
