<?php

namespace Drupal\thunder_search\Plugin\views\area;

use Drupal\views\Plugin\views\area\AreaPluginBase;

/**
 * Views area handler to display some configurable result summary.
 *
 * @ingroup views_area_handlers
 *
 * @ViewsArea("facet_block")
 */
class FacetBlock extends AreaPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE) {

    /** @var \Drupal\facets\FacetManager\DefaultFacetManager $facetManager */
    $facetManager = \Drupal::service('facets.manager');

    /** @var \Drupal\facets\FacetSource\FacetSourcePluginManager $source */
    $sources = \Drupal::service('plugin.manager.facets.facet_source');

    $facet_source = NULL;
    foreach ($sources->getDefinitions() as $definition) {
      /** @var \Drupal\facets\Plugin\facets\facet_source\SearchApiDisplay $source */
      $source = $sources->createInstance($definition['id']);
      if ($this->view->id() == $source->getViewsDisplay()->id() && $this->view->current_display == $source->getViewsDisplay()->current_display) {
        $facet_source = $source;
      }
    }
    if (!$facet_source || !$this->view->result) {
      return [];
    }

    $build = ['#type' => 'container', '#attributes' => ['class' => 'form--inline clearfix']];
    $build['_view'] = $this->view->display_handler->viewExposedFormBlocks();
    foreach ($facetManager->getFacetsByFacetSourceId($facet_source->pluginId) as $id => $facet) {
      // No need to build the facet if it does not need to be visible.
      if (($facet->getOnlyVisibleWhenFacetSourceIsVisible() && !$facet->getFacetSource()->isRenderedInCurrentRequest())) {
        continue;
      }
      $config = $facet->getFacetSource()->getDisplay()->getPluginDefinition();

      if ($config['view_id'] == $this->view->id() && $config['view_display'] == $this->view->current_display) {
        $built_facet = $facetManager->build($facet);
        if (!in_array('facet-empty', $built_facet[0]['#attributes']['class'])) {
          $build['facets'][$id] = [
            '#type' => 'container',
            '#attributes' => ['class' => 'form-item'],
            'label' => [
              '#type' => 'label',
              '#title' => $facet->label(),
              '#title_display' => 'above',
            ],
            'element' => $built_facet,
            '#weight' => $facet->getWeight(),
          ];
        }
      }
    }

    // Return as render array.
    return $build;
  }

}
