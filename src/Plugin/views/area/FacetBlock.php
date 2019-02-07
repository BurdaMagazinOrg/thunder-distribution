<?php

namespace Drupal\thunder\Plugin\views\area;

use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\facets\FacetManager\DefaultFacetManager;
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

  protected $blockManager;

  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, DefaultFacetManager $facetManager, BlockManagerInterface $blockManager, AccountInterface $account) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->facetManager = $facetManager;
    $this->blockManager = $blockManager;
    $this->currentUser = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('facets.manager'), $container->get('plugin.manager.block'), $container->get('current_user'));
  }

  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE) {

    $build = [
      '#type' => 'container',
      '#attributes' => ['class' => 'form--inline clearfix'],
      'exposed_form' => $this->view->display_handler->viewExposedFormBlocks(),
    ];
    $build['exposed_form']['#attributes']['class'][] = 'form-item';

    if (!$this->view->result) {
      return $build;
    }

    $facets_source_plugin_id = 'search_api:views_' . $this->view->getDisplay()->getPluginId() . '__' . $this->view->id() . '__' . $this->view->current_display;
    foreach ($this->facetManager->getFacetsByFacetSourceId($facets_source_plugin_id) as $id => $facet) {
      /** @var \Drupal\Core\Block\BlockPluginInterface $plugin_block */
      $plugin_block = $this->blockManager->createInstance('facet_block:' . $facet->id(), []);

      // Some blocks might implement access check.
      $access_result = $plugin_block->access($this->currentUser);

      // Return empty render array if user doesn't have access. $access_result
      // can be boolean or an AccessResult class.
      if (is_object($access_result) && $access_result->isForbidden() || is_bool($access_result) && !$access_result) {
        return [];
      }
      $build['facets'][$id] = $plugin_block->build() + [
        '#weight' => $facet->getWeight(),
      ];
    }
    // Return as render array.
    return $build;
  }

}
