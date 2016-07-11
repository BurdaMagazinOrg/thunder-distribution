<?php

namespace Drupal\thunder\Installer\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\thunder\OptionalModulesManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the site configuration form.
 */
class ModuleConfigureForm extends ConfigFormBase {

  /**
   * The plugin manager.
   *
   * @var \Drupal\thunder\OptionalModulesManager
   */
  protected $optionalModulesManager;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param OptionalModulesManager $optionalModulesManager
   *   The factory for configuration objects.
   */
  public function __construct(ConfigFactoryInterface $config_factory, OptionalModulesManager $optionalModulesManager) {

    parent::__construct($config_factory);

    $this->optionalModulesManager = $optionalModulesManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.thunder.optional_modules')
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

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    drupal_get_messages();

    $installableModules = [];
    $defaultValues = [];

    foreach ($this->optionalModulesManager->getDefinitions() as $provider) {

      $instance = $this->optionalModulesManager->createInstance($provider['id']);

      $installableModules[$provider['id']] = $provider['label'];

      if ($instance->isStandardlyEnabled()) {
        $defaultValues[] = $provider['id'];
      }

      $form = $instance->buildForm($form, $form_state);

    }
    $form['#title'] = $this->t('Install & configure modules');

    $form['install_modules'] = array(
      '#type' => 'checkboxes',
      '#options' => $installableModules,
      '#title' => t('Which module do you also like to install?'),
      '#weight' => -1,
      '#default_value' => $defaultValues,
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
    $buildInfo = $form_state->getBuildInfo();

    $install_state = $buildInfo['args'];

    $install_state[0]['thunder_additional_modules'] = array_filter($installModules);
    $install_state[0]['form_state_values'] = $form_state->getValues();

    $form_state->setBuildInfo($buildInfo);

  }

}
