<?php

namespace Drupal\thunder\Plugin\views\area;

use Drupal\facets\FacetManager\DefaultFacetManager;
use Drupal\facets\FacetSource\FacetSourcePluginManager;
use Drupal\views\Plugin\views\area\AreaPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Views area handler to display some configurable result summary.
 *
 * @ingroup views_area_handlers
 *
 * @ViewsArea("facet_block")
 */
class FacetBlock extends AreaPluginBase {

  protected $facetManager;

  protected $facetSourceManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, DefaultFacetManager $facetManager, FacetSourcePluginManager $facetSourceManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->facetManager = $facetManager;
    $this->facetSourceManager = $facetSourceManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('facets.manager'), $container->get('plugin.manager.facets.facet_source'));
  }

  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE) {

    $facet_source = NULL;
    foreach ($this->facetSourceManager->getDefinitions() as $definition) {
      /** @var \Drupal\facets\Plugin\facets\facet_source\SearchApiDisplay $source */
      $source = $this->facetSourceManager->createInstance($definition['id']);
      if ($this->view->id() == $source->getViewsDisplay()->id() && $this->view->current_display == $source->getViewsDisplay()->current_display) {
        $facet_source = $source;
      }
    }

    $build = [
      '#type' => 'container',
      '#attributes' => ['class' => 'form--inline clearfix'],
      'exposed_form' => $this->view->display_handler->viewExposedFormBlocks(),
    ];
    $build['exposed_form']['#attributes']['class'][] = 'form-item';

    if (!$facet_source || !$this->view->result) {
      return $build;
    }
    foreach ($this->facetManager->getFacetsByFacetSourceId($facet_source->pluginId) as $id => $facet) {
      // No need to build the facet if it does not need to be visible.
      if (($facet->getOnlyVisibleWhenFacetSourceIsVisible() && !$facet->getFacetSource()->isRenderedInCurrentRequest())) {
        continue;
      }
      $config = $facet->getFacetSource()->getDisplay()->getPluginDefinition();

      if ($config['view_id'] != $this->view->id() || $config['view_display'] != $this->view->current_display) {
        continue;
      }
      $built_facet = $this->facetManager->build($facet);
      if (in_array('facet-empty', $built_facet[0]['#attributes']['class'])) {
        continue;
      }
      $build['facets'][$id] = $built_facet + [
        '#weight' => $facet->getWeight(),
      ];
    }
    // Return as render array.
    return $build;
  }

}
