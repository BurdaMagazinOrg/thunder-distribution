<?php

namespace Drupal\thunder_article\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Drupal\system\PathBasedBreadcrumbBuilder;
use Drupal\Core\Access\AccessManagerInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\Routing\RequestContext;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

/**
 * Class to define the menu_link breadcrumb builder.
 */
class ThunderArticleBreadcrumbBuilder extends PathBasedBreadcrumbBuilder {

  /**
   * The entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The taxonomy storage.
   *
   * @var \Drupal\Taxonomy\TermStorageInterface
   */
  protected $termStorage;

  /**
   * Constructs the ThunderArticleBreadcrumbBuilder.
   *
   * @param \Drupal\Core\Routing\RequestContext $context
   *   The router request context.
   * @param \Drupal\Core\Access\AccessManagerInterface $access_manager
   *   The menu link access service.
   * @param \Symfony\Component\Routing\Matcher\RequestMatcherInterface $router
   *   The dynamic router service.
   * @param \Drupal\Core\PathProcessor\InboundPathProcessorInterface $path_processor
   *   The inbound path processor.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Controller\TitleResolverInterface $title_resolver
   *   The title resolver service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user object.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path matcher service.
   *    * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   The entity repository service.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(RequestContext $context, AccessManagerInterface $access_manager, RequestMatcherInterface $router, InboundPathProcessorInterface $path_processor, ConfigFactoryInterface $config_factory, TitleResolverInterface $title_resolver, AccountInterface $current_user, CurrentPathStack $current_path, PathMatcherInterface $path_matcher = NULL, EntityTypeManagerInterface $entityTypeManager, EntityRepositoryInterface $entityRepository) {
    $path_matcher = $path_matcher ?: \Drupal::service('path.matcher');
    parent::__construct($context, $access_manager, $router, $path_processor, $config_factory, $title_resolver, $current_user, $current_path, $path_matcher);

    $this->entityRepository = $entityRepository;
    $this->termStorage = $entityTypeManager->getStorage('taxonomy_term');
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    // This breadcrumb apply only for all articles.
    /** @var \Drupal\node\NodeInterface $node */
    $node = $route_match->getParameter('node');
    if ($route_match->getRouteName() == 'entity.node.canonical' && $node instanceof NodeInterface) {
      return $node->getType() == 'article';
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addCacheContexts(['route']);

    // Add all parent forums to breadcrumbs.
    /** @var \Drupal\node\NodeInterface $node */
    $node = $route_match->getParameter('node');
    $breadcrumb->addCacheableDependency($node);

    // Add all parent forums to breadcrumbs.
    /** @var \Drupal\Taxonomy\TermInterface $term */
    $term = !(empty($node->field_channel)) ? $node->field_channel->entity : '';

    $links = [];
    if ($term) {
      $breadcrumb->addCacheableDependency($term);

      $channels = $this->termStorage->loadAllParents($term->id());
      foreach (array_reverse($channels) as $term) {
        $term = $this->entityRepository->getTranslationFromContext($term);
        $breadcrumb->addCacheableDependency($term);
        $links[] = Link::createFromRoute($term->getName(), 'entity.taxonomy_term.canonical', ['taxonomy_term' => $term->id()]);
      }
    }
    if (!$links || '/' . $links[0]->getUrl()->getInternalPath() != $this->config->get('page.front')) {
      array_unshift($links, Link::createFromRoute($this->t('Home'), '<front>'));
    }

    return $breadcrumb->setLinks($links);
  }

}
