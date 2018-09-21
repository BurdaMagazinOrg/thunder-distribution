<?php

namespace Drupal\thunder_article\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\node\NodeForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base for handler for node add/edit forms.
 */
class ThunderNodeForm extends NodeForm {

  /**
   * The moderation information service.
   *
   * @var \Drupal\content_moderation\ModerationInformationInterface
   */
  protected $moderationInfo;

  /**
   * Constructs a NodeForm object.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The factory for the temp store object.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\content_moderation\ModerationInformationInterface $moderationInfo
   *   The moderation info service.
   */
  public function __construct(EntityRepositoryInterface $entity_repository, PrivateTempStoreFactory $temp_store_factory, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL, AccountInterface $current_user, ModerationInformationInterface $moderationInfo = NULL) {
    parent::__construct($entity_repository, $temp_store_factory, $entity_type_bundle_info, $time, $current_user);
    $this->moderationInfo = $moderationInfo;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('tempstore.private'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('current_user'),
      $container->has('content_moderation.moderation_information') ? $container->get('content_moderation.moderation_information') : NULL
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Entity\ContentEntityFormInterface $form_object */
    $form_object = $form_state->getFormObject();
    /** @var \Drupal\node\NodeInterface $entity */
    $entity = $form_object->getEntity();

    if ($this->moderationInfo->hasPendingRevision($entity)) {
      $this->messenger()->addWarning($this->t('This %entity_type has unpublished changes from user %user.', ['%entity_type' => $entity->get('type')->entity->label(), '%user' => $entity->getRevisionUser()->label()]));
    }

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $element = parent::actions($form, $form_state);

    /** @var \Drupal\Core\Entity\ContentEntityFormInterface $form_object */
    $form_object = $form_state->getFormObject();
    /** @var \Drupal\node\NodeInterface $entity */
    $entity = $form_object->getEntity();

    if (!$this->moderationInfo || !$this->moderationInfo->isModeratedEntity($entity)) {
      return $element;
    }

    $state = $this->moderationInfo->getWorkflowForEntity($entity)->getTypePlugin()->getState($entity->moderation_state->value);
    $element['status'] = [
      '#type' => 'item',
      '#markup' => $this->entity->isNew() || !$this->moderationInfo->isDefaultRevisionPublished($entity) ? $this->t('of unpublished @entity_type', ['@entity_type' => strtolower($entity->type->entity->label())]) : $this->t('of published @entity_type', ['@entity_type' => strtolower($entity->type->entity->label())]),
      '#weight' => 200,
      '#wrapper_attributes' => [
        'class' => ['status'],
      ],
      '#access' => !$state->isDefaultRevisionState(),
    ];

    $element['moderation_state_current'] = [
      '#type' => 'item',
      '#markup' => $state->label(),
      '#weight' => 210,
      '#wrapper_attributes' => [
        'class' => ['status', $state->id()],
      ],
    ];

    if ($this->moderationInfo->hasPendingRevision($entity)) {
      $route_info = Url::fromRoute('node.revision_revert_default_confirm', [
        'node' => $entity->id(),
        'node_revision' => $entity->getRevisionId(),
      ]);
      if ($this->getRequest()->query->has('destination')) {
        $query = $route_info->getOption('query');
        $query['destination'] = $this->getRequest()->query->get('destination');
        $route_info->setOption('query', $query);
      }
      $element['revert_to_default'] = [
        '#type' => 'link',
        '#title' => $this->t('Revert to default revision'),
        '#access' => $this->entity->access('update'),
        '#weight' => 101,
        '#attributes' => [
          'class' => ['button', 'button--danger'],
        ],
      ];
      $element['revert_to_default']['#url'] = $route_info;
    }

    return $element;
  }

}
