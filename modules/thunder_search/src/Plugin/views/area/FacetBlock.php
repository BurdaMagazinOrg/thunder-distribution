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

    /** @var \Drupal\facets\FacetManager\DefaultFacetManager $facetManager */
    $facetManager = \Drupal::service('facets.manager');
    $build = ['#type' => 'container', '#attributes' => ['class' => 'form--inline clearfix']];
    $build['_view'] = $this->view->display_handler->viewExposedFormBlocks();
    foreach ($facets as $id => $facet) {
      // No need to build the facet if it does not need to be visible.
      if (($facet->getOnlyVisibleWhenFacetSourceIsVisible() && !$facet->getFacetSource()->isRenderedInCurrentRequest())) {
        continue;
      }

      $config = $facet->getFacetSource()->getDisplay()->getPluginDefinition();

      if ($config['view_id'] == $this->view->id() && $config['view_display'] == $this->view->current_display) {
        $built_facet = $facetManager->build($facet);
        if (!in_array('facet-empty', $built_facet[0]['#attributes']['class'])) {
          $build[$id] = [
            '#type' => 'container',
            '#attributes' => ['class' => 'form-item'],
            'label' => [
              '#type' => 'label',
              '#title' => $facet->label(),
              '#title_display' => 'above',
            ],
            'element' => $built_facet,
          ];
        }
      }
    }

    // Return as render array.
    return $build;
  }

}
