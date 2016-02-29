<?php

/**
 * @file
 * Contains \Drupal\thunder\Installer\Form\SiteConfigureForm.
 */

namespace Drupal\thunder\Installer\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\thunder\Installer\Form\OptionalModules\AdIntegration;
use Drupal\thunder\Installer\Form\OptionalModules\BreakpointJsSettings;
use Drupal\thunder\Installer\Form\OptionalModules\FacebookInstantArticles;
use Drupal\thunder\Installer\Form\OptionalModules\GoogleAnalytics;
use Drupal\thunder\Installer\Form\OptionalModules\HierarchicalConfig;
use Drupal\thunder\Installer\Form\OptionalModules\IvwIntegration;
use Drupal\thunder\Installer\Form\OptionalModules\RiddleIntegration;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides the site configuration form.
 */
class ModuleConfigureForm extends ConfigFormBase {


  /**
   * The module installer.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;


  private $provider = [];

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_installer')
    );
  }


  public function __construct(ConfigFactoryInterface $config_factory, ModuleInstallerInterface $module_installer) {
    parent::__construct($config_factory);

    $this->moduleInstaller = $module_installer;

    $this->provider = [
      new GoogleAnalytics($config_factory),
      new IvwIntegration($config_factory),
     # new BreakpointJsSettings($config_factory),
      new FacebookInstantArticles($config_factory),
      new AdIntegration($config_factory),
      new RiddleIntegration($config_factory),
      new HierarchicalConfig($config_factory),
    ];
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

    $configNames = [];

    /** @var ConfigFormBase $provider */
    foreach ($this->provider as $provider) {
      $configNames = array_merge($configNames, $provider->getEditableConfigNames());
    }

    return $configNames;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    drupal_get_messages();

    $installableModules = [];

    /** @var ConfigFormBase $provider */
    foreach ($this->provider as $provider) {

      $installableModules[$provider->getFormId()] = $provider->getFormName();

      $form = $provider->buildForm($form, $form_state);

    }
    $form['#title'] = $this->t('Install & configure modules');

    $form['install_modules'] = array(
      '#type' => 'checkboxes',
      '#options' => $installableModules,
      '#title' => t('Which module do you also like to install?'),
      '#weight' => -1
    );

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

    $installModules = $form_state->getValue('install_modules');

    $installModules = array_filter($installModules);

    $this->moduleInstaller->install($installModules);
    /** @var ConfigFormBase $provider */
    foreach ($this->provider as $provider) {
      if (in_array($provider->getFormId(), $installModules)) {
        $provider->submitForm($form, $form_state);
      }
    }
  }

}
