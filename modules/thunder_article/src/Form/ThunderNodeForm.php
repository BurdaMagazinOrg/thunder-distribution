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
 * Base for handler for taxonomy term edit forms.
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
      $user = $this->entityTypeManager->getStorage('user')->load($entity->getRevisionUserId());
      $this->messenger()->addWarning($this->t('This %entity_type has unpublished changes from user %user.', ['%entity_type' => $entity->get('type')->entity->label(), '%user' => $user->label()]));
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
    $element['moderation_state_current'] = [
      '#type' => 'item',
      '#markup' => $state->label(),
      '#weight' => 200,
      '#wrapper_attributes' => [
        'class' => ['status', $state->id()],
      ],
    ];

    if ($this->entity->isNew()) {
      return $element;
    }

    $element['status'] = [
      '#type' => 'item',
      '#markup' => $this->moderationInfo->isDefaultRevisionPublished($entity) ? $this->t('Published') : $this->t('Unpublished'),
      '#weight' => 210,
      '#wrapper_attributes' => [
        'class' => ['status'],
      ],
    ];

    if ($this->moderationInfo->hasPendingRevision($entity)) {
      $route_info = Url::fromRoute('node.revision_delete_confirm', [
        'node' => $entity->id(),
        'node_revision' => $entity->getLoadedRevisionId(),
      ]);
      if ($this->getRequest()->query->has('destination')) {
        $query = $route_info->getOption('query');
        $query['destination'] = $this->getRequest()->query->get('destination');
        $route_info->setOption('query', $query);
      }
      $element['delete_revision'] = [
        '#type' => 'link',
        '#title' => $this->t('Delete revision'),
        '#access' => $this->entity->access('delete'),
        '#weight' => 101,
        '#attributes' => [
          'class' => ['button', 'button--danger'],
        ],
      ];
      $element['delete_revision']['#url'] = $route_info;
    }

    return $element;
  }

}
