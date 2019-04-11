<?php

namespace Drupal\thunder\Installer\Form;

use Drupal\Core\Extension\ThemeInstallerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the site configuration form.
 */
class ModuleConfigureForm extends FormBase {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The theme installer service.
   *
   * @var \Drupal\Core\Extension\ThemeInstallerInterface
   */
  protected $themeInstaller;

  /**
   * ModuleConfigureForm constructor.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Core\Extension\ThemeInstallerInterface $installer
   *   The theme installer service.
   */
  public function __construct(StateInterface $state, ThemeInstallerInterface $installer) {
    $this->state = $state;
    $this->themeInstaller = $installer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('state'), $container->get('theme_installer'));
  }

  protected $providers = [
    'adsense' => [
      'type' => 'module',
      'label' => 'AdSense',
      'description' => 'With Google AdSense, you can earn money from your online content',
    ],
    'thunder_amp' => [
      'type' => 'theme',
      'label' => 'AMP',
      'description' => 'The Google AMP project strives for better performance, especially on mobile devices.',
    ],
    'thunder_help' => [
      'type' => 'module',
      'label' => 'Help',
      'description' => 'Provides a tour to learn more about Thunder',
    ],
    'thunder_fia' => [
      'type' => 'module',
      'label' => 'Facebook Instant Articles',
      'description' => 'A new way for any publisher to create fast, interactive articles on Facebook.',
    ],
    'google_analytics' => [
      'type' => 'module',
      'label' => "Google Analytics",
      'description' => "Google Analytics lets you measure your advertising ROI as well as track your video, and social networking sites and applications.",
    ],
    'harbourmaster' => [
      'type' => 'module',
      'label' => "Harbourmaster SSO connector",
      'description' => "Harbourmaster is providing a single sign-on solution.",
    ],
    'ivw_integration' => [
      'type' => 'module',
      'label' => "IVW Integration",
      'description' => "Integration module for the German audience measurement organisation IVW.",
    ],
    'thunder_liveblog' => [
      'type' => 'module',
      'label' => "Liveblog",
      'description' => "The Liveblog module allows you to distribute blog posts to thousands of users in realtime.",
    ],
    'nexx_integration' => [
      'type' => 'module',
      'label' => "Nexx video integration",
      'description' => "nexx.tv offers end-to-end online video platform solutions.",
    ],
    'thunder_riddle' => [
      'type' => 'module',
      'label' => "Riddle integration",
      'description' => "Riddle makes it easy to quickly create beautiful and highly shareable quizzes, tests, lists, polls, and more.",
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'thunder_module_configure_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['description'] = [
      '#type' => 'item',
      '#markup' => $this->t('Keep calm. You can install all the modules later, too.'),
    ];

    $options = $descriptions = [];
    foreach ($this->providers as $id => $provider) {
      $options[$id] = $provider['label'];
      $descriptions[$id] = ['#description' => $provider['description']];
    }

    $form['install_modules'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Select modules'),
      '#options' => $options,
    ] + $descriptions;

    $form['#title'] = $this->t('Select additional modules');

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save and continue'),
      '#button_type' => 'primary',
      '#submit' => ['::submitForm'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $system_modules = $this->state->get('install_profile_modules');

    $themes = [];
    foreach (array_filter($form_state->getValue('install_modules')) as $item) {
      if ($this->providers[$item]['type'] === 'module') {
        $system_modules[] = $item;
      }
      elseif ($this->providers[$item]['type'] === 'theme') {
        $themes[] = $item;
      }
    }

    $this->themeInstaller->install($themes);
    $this->state->set('install_profile_modules', $system_modules);
  }

}
