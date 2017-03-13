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
   * @param \Drupal\thunder\OptionalModulesManager $optionalModulesManager
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

    $form['description'] = array(
      '#type' => 'item',
      '#markup' => $this->t('Keep calm. You can install all the modules later, too.'),
    );

    $form['install_modules'] = array(
      '#type' => 'container',
    );

    $providers = $this->optionalModulesManager->getDefinitions();

    uasort($providers, static::sortWeights());

    foreach ($providers as $provider) {

      $instance = $this->optionalModulesManager->createInstance($provider['id']);

      $form['install_modules_' . $provider['id']] = array(
        '#type' => 'checkbox',
        '#title' => $provider['label'],
        '#description' => isset($provider['description']) ? $provider['description'] : '',
        '#default_value' => isset($provider['standardlyEnabled']) ? $provider['standardlyEnabled'] : 0,
      );

      $form = $instance->buildForm($form, $form_state);

    }
    $form['#title'] = $this->t('Install & configure modules');

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
    $installModules = [];

    foreach ($form_state->getValues() as $key => $value) {

      if (strpos($key, 'install_modules') !== FALSE && $value) {
        preg_match('/install_modules_(?P<name>\w+)/', $key, $values);
        $installModules[] = $values['name'];
      }
    }

    $buildInfo = $form_state->getBuildInfo();

    $install_state = $buildInfo['args'];

    $install_state[0]['thunder_additional_modules'] = $installModules;
    $install_state[0]['form_state_values'] = $form_state->getValues();

    $buildInfo['args'] = $install_state;

    $form_state->setBuildInfo($buildInfo);

  }

  /**
   * Returns a sorting function to sort an array by weights.
   *
   * If an array element doesn't provide a weight, it will be set to 0.
   * If two elements have the same weight, they are sorted by label.
   *
   * @return \Closure
   *    The sorting function
   */
  private static function sortWeights() {
    return function ($a, $b) {
      $a_weight = isset($a['weight']) ? $a['weight'] : 0;
      $b_weight = isset($b['weight']) ? $b['weight'] : 0;

      if ($a_weight == $b_weight) {
        return ($a['label'] > $b['label']) ? 1 : -1;
      }
      return ($a_weight > $b_weight) ? 1 : -1;
    };
  }

}
