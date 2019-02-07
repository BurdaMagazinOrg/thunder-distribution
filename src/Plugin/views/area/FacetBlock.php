<?php

namespace Drupal\thunder\Plugin\views\area;

use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Session\AccountInterface;
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

  protected $blockManager;

  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, DefaultFacetManager $facetManager, FacetSourcePluginManager $facetSourceManager, BlockManagerInterface $blockManager, AccountInterface $account) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->facetManager = $facetManager;
    $this->facetSourceManager = $facetSourceManager;
    $this->blockManager = $blockManager;
    $this->currentUser = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('facets.manager'), $container->get('plugin.manager.facets.facet_source'), $container->get('plugin.manager.block'), $container->get('current_user'));
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
        break;
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
