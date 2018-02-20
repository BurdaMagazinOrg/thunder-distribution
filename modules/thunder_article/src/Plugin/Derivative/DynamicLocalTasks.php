<?php

namespace Drupal\thunder_article\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Generates content view local tasks.
 */
class DynamicLocalTasks extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Creates an DynamicLocalTasks object.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The translation manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(TranslationInterface $string_translation, ModuleHandlerInterface $module_handler) {
    $this->stringTranslation = $string_translation;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('string_translation'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = [];

    $this->derivatives["thunder_article.overview"] = [
      'route_name' => "system.admin_content",
      'title' => $this->t('Overview'),
      'parent_id' => "system.admin_content",
      'weight' => 1,
    ] + $base_plugin_definition;

    if ($this->moduleHandler->moduleExists('content_lock')) {
      $this->derivatives["thunder_article.content_lock"] = [
        'route_name' => "view.locked_content.page_1",
        'title' => $this->t('Locked content'),
        'parent_id' => "system.admin_content",
        'weight' => 2,
      ] + $base_plugin_definition;
    }

    if ($this->moduleHandler->moduleExists('scheduler')) {
      $this->derivatives["thunder_article.scheduler"] = [
        'route_name' => "view.scheduler_scheduled_content.overview",
        'title' => $this->t('Scheduled content'),
        'parent_id' => "system.admin_content",
        'weight' => 3,
      ] + $base_plugin_definition;
    }

    return $this->derivatives;
  }

}
