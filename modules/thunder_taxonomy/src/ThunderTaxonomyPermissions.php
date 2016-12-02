<?php

namespace Drupal\thunder_taxonomy;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dynamic permissions of the taxonomy module.
 *
 * @see thunder_taxonomy_access.permissions.yml
 */
class ThunderTaxonomyPermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a TaxonomyPermissions instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'));
  }

  /**
   * Get taxonomy permissions.
   *
   * @return array
   *   Permissions array.
   */
  public function permissions() {
    $permissions = [];
    foreach ($this->entityTypeManager->getStorage('taxonomy_vocabulary')->loadMultiple() as $vocabulary) {
      $permissions += [
        'view published terms in ' . $vocabulary->id() => [
          'title' => $this->t('View published terms in %vocabulary', ['%vocabulary' => $vocabulary->label()]),
        ],
        'view unpublished terms in ' . $vocabulary->id() => [
          'title' => $this->t('View unpublished terms in %vocabulary', ['%vocabulary' => $vocabulary->label()]),
        ],
      ];
    }
    return $permissions;
  }

}
