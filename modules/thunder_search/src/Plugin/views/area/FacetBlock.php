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

    /** @var \Drupal\facets\Entity\Facet[] $facets */
    $facets = \Drupal::entityTypeManager()
      ->getStorage('facets_facet')
      ->loadMultiple();

    $facetManager = \Drupal::service('facets.manager');
    $build = NULL;
    foreach ($facets as $id => $facet) {
      // No need to build the facet if it does not need to be visible.
      if ($facet->getOnlyVisibleWhenFacetSourceIsVisible() && !$facet->getFacetSource()->isRenderedInCurrentRequest()) {
        continue;
      }

      $config = $facet->getFacetSource()->getDisplay()->getPluginDefinition();

      if ($config['view_id'] == $this->view->id() && $config['view_display'] == $this->view->current_display) {
        $built_facet = $facetManager->build($facet);
        if ($built_facet) {
          $build[$id] = ['#type' => 'container'];
          $build[$id]['label'] = ['#markup' => $facet->label()];
          $build[$id]['element'] = $built_facet;
        }
      }
    }
    $build['_view'] = $this->view->display_handler->viewExposedFormBlocks();

    // Return as render array.
    return $build;
  }

}
