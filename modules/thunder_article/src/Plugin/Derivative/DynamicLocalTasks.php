<?php

namespace Drupal\thunder_article\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
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
   * The route provider service.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * Creates an DynamicLocalTasks object.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The translation manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider service..
   */
  public function __construct(TranslationInterface $string_translation, ModuleHandlerInterface $module_handler, RouteProviderInterface $route_provider) {
    $this->stringTranslation = $string_translation;
    $this->moduleHandler = $module_handler;
    $this->routeProvider = $route_provider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('string_translation'),
      $container->get('module_handler'),
      $container->get('router.route_provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    if ($this->moduleHandler->moduleExists('content_lock') && $this->routeProvider->getRoutesByNames(['view.locked_content.page_1'])) {
      $this->derivatives["thunder_article.content_lock"] = [
        'route_name' => "view.locked_content.page_1",
        'title' => $this->t('Locked content'),
        'parent_id' => "system.admin_content",
        'weight' => 2,
      ] + $base_plugin_definition;
    }

    if ($this->moduleHandler->moduleExists('scheduler') && $this->routeProvider->getRoutesByNames(['view.scheduler_scheduled_content.overview'])) {
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
