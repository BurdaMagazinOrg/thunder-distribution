<?php

/**
 * @file
 * Contains \Drupal\thunder_taxonomy_access\ThunderTaxonomyAccessPermissions.
 */

namespace Drupal\thunder_taxonomy_access;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dynamic permissions of the taxonomy module.
 *
 * @see thunder_taxonomy_access.permissions.yml
 */
class ThunderTaxonomyAccessPermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a TaxonomyPermissions instance.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity.manager'));
  }

  /**
   * Get taxonomy permissions.
   *
   * @return array
   *   Permissions array.
   */
  public function permissions() {
    $permissions = [];
    foreach ($this->entityManager->getStorage('taxonomy_vocabulary')->loadMultiple() as $vocabulary) {
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
