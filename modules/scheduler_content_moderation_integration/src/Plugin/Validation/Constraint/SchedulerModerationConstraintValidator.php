<?php

namespace Drupal\scheduler_content_moderation_integration\Plugin\Validation\Constraint;

use Drupal\content_moderation\StateTransitionValidationInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the SchedulerModeration constraint.
 */
class SchedulerModerationConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  private $account;

  /**
   * The state transition service.
   *
   * @var \Drupal\content_moderation\StateTransitionValidationInterface
   */
  private $stateTransitionValidation;

  /**
   * SchedulerModerationConstraintValidator constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The current user.
   * @param \Drupal\content_moderation\StateTransitionValidationInterface $stateTransitionValidation
   *   The state transition service.
   */
  public function __construct(
    AccountProxyInterface $account,
    StateTransitionValidationInterface $stateTransitionValidation
  ) {
    $this->account = $account;
    $this->stateTransitionValidation = $stateTransitionValidation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('content_moderation.state_transition_validation')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    // If the input parameters is something unexpected lets skip the validation.
    if (!$value instanceof FieldItemListInterface || !$constraint instanceof SchedulerModerationConstraint) {
      return;
    }

    // No need to validate if a moderation state has not ben set.
    if ($value->isEmpty()) {
      return;
    }

    $entity = $value->getEntity();

    // We can only work with content entities so lets skip validation if it's
    // something else.
    if (!$entity instanceof ContentEntityInterface) {
      return;
    }

    /** @var \Drupal\options\Plugin\Field\FieldType\ListStringItem $field */
    $field = $value->first();
    $moderation_state = $field->get('value')->getValue();

    if (!$this->isValidTransition($moderation_state, $entity)) {
      $this->context
        ->buildViolation($constraint->messagePublishModerationInvalid)
        ->atPath($value->getName())
        ->addViolation();
    }
  }

  /**
   * Checks if it's a valid moderation states transition for provided entity.
   *
   * @param string $moderation_state
   *   The moderation state to validate transition for.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to check the state against.
   *
   * @return bool
   *   TRUE if is a valid transition, FALSE otherwise.
   */
  private function isValidTransition($moderation_state, ContentEntityInterface $entity) {
    $valid_transitions = $this->stateTransitionValidation
      ->getValidTransitions($entity, $this->account);

    foreach ($valid_transitions as $valid_transition) {
      if ($moderation_state === $valid_transition->to()->id()) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
