<?php

namespace Drupal\thunder_article\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\metatag\MetatagTagPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ConfigurationForm.
 */
class ConfigurationForm extends ConfigFormBase {

  /**
   * The metatag tag plugin manager.
   *
   * @var \Drupal\metatag\MetatagTagPluginManager
   */
  protected $metatagTagManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, MetatagTagPluginManager $metatag_tag_manager = NULL) {
    parent::__construct($config_factory);

    $this->metatagTagManager = $metatag_tag_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->has('plugin.manager.metatag.tag') ? $container->get('plugin.manager.metatag.tag') : NULL
    );
  }

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

    if ($this->metatagTagManager) {
      $tags = [];
      foreach ($this->metatagTagManager->getDefinitions() as $definition) {
        $tags[$definition['id']] = $definition['group'] . ': ' . $definition['label'];
      }

      $form['thunder_article_metatags'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t("Move metatag tags into the 'Thunder Article' group"),
        '#description' => $this->t("The 'Thunder Article' group is displayed on the article edit page."),
        '#default_value' => $config->get('thunder_article_metatags') ? $config->get('thunder_article_metatags') : [],
        '#options' => $tags,
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('thunder_article.settings')
      ->set('move_scheduler_local_task', $form_state->getValue('move_scheduler_local_task'))
      ->set('thunder_article_metatags', array_keys(array_filter($form_state->getValue('thunder_article_metatags'))))
      ->save();

    $this->metatagTagManager->clearCachedDefinitions();
  }

}
