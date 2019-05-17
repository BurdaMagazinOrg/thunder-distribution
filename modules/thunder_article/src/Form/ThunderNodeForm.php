<?php

namespace Drupal\thunder_article\Form;

use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\node\Access\NodeRevisionAccessCheck;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Base for handler for node add/edit forms.
 */
class ThunderNodeForm implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The node revision access check service.
   *
   * @var \Drupal\node\Access\NodeRevisionAccessCheck
   */
  protected $nodeRevisionAccess;

  /**
   * The moderation information service.
   *
   * @var \Drupal\content_moderation\ModerationInformationInterface
   */
  protected $moderationInfo;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a NodeForm object.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack service.
   * @param \Drupal\node\Access\NodeRevisionAccessCheck $node_revision_access
   *   The node revision access check service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\content_moderation\ModerationInformationInterface $moderationInfo
   *   (optional) The moderation info service. The optionality is important
   *   otherwise this form becomes dependent on the content_moderation module.
   */
  public function __construct(AccountInterface $current_user, MessengerInterface $messenger, RequestStack $requestStack, NodeRevisionAccessCheck $node_revision_access, EntityTypeManagerInterface $entity_type_manager, ModerationInformationInterface $moderationInfo = NULL) {
    $this->currentUser = $current_user;
    $this->messenger = $messenger;
    $this->request = $requestStack->getCurrentRequest();
    $this->nodeRevisionAccess = $node_revision_access;
    $this->entityTypeManager = $entity_type_manager;
    $this->moderationInfo = $moderationInfo;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('messenger'),
      $container->get('request_stack'),
      $container->get('access_check.node.revision'),
      $container->get('entity_type.manager'),
      $container->has('content_moderation.moderation_information') ? $container->get('content_moderation.moderation_information') : NULL
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formAlter(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Entity\ContentEntityFormInterface $form_object */
    $form_object = $form_state->getFormObject();
    /** @var \Drupal\node\NodeInterface $entity */
    $entity = $form_object->getEntity();

    $storage = $this->entityTypeManager->getStorage($entity->getEntityTypeId());
    $latest_revision_id = $storage->getLatestTranslationAffectedRevisionId($entity->id(), $entity->language()->getId());
    if ($latest_revision_id !== NULL && $this->moderationInfo && $this->moderationInfo->hasPendingRevision($entity)) {
      $this->messenger->addWarning($this->t('This %entity_type has unpublished changes from user %user.', ['%entity_type' => $entity->get('type')->entity->label(), '%user' => $entity->getRevisionUser()->label()]));
    }

    $form['actions'] = array_merge($form['actions'], $this->actions($entity));

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions($entity) {
    $storage = $this->entityTypeManager->getStorage($entity->getEntityTypeId());
    $latest_revision_id = $storage->getLatestTranslationAffectedRevisionId($entity->id(), $entity->language()->getId());

    if ($latest_revision_id == NULL || !$this->moderationInfo || !$this->moderationInfo->isModeratedEntity($entity)) {
      return [];
    }

    $state = $this->moderationInfo->getWorkflowForEntity($entity)->getTypePlugin()->getState($entity->moderation_state->value);
    $element['status'] = [
      '#type' => 'item',
      '#markup' => $entity->isNew() || !$this->moderationInfo->isDefaultRevisionPublished($entity) ? $this->t('of unpublished @entity_type', ['@entity_type' => strtolower($entity->type->entity->label())]) : $this->t('of published @entity_type', ['@entity_type' => strtolower($entity->type->entity->label())]),
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
      if ($this->request->query->has('destination')) {
        $query = $route_info->getOption('query');
        $query['destination'] = $this->request->query->get('destination');
        $route_info->setOption('query', $query);
      }

      $element['revert_to_default'] = [
        '#type' => 'link',
        '#title' => $this->t('Revert to default revision'),
        '#access' => $this->nodeRevisionAccess->checkAccess($entity, $this->currentUser, 'update'),
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
