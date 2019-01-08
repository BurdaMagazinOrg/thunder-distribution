<?php

namespace Drupal\scheduler_content_moderation_integration\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Validator for the UnPublishStateConstraint.
 */
class UnPublishStateConstraintValidator extends ConstraintValidatorBase {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {

    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $value->getEntity();

    // No need to validate entities that are not moderated.
    if (!$this->moderationInformation->isModeratedEntity($entity)) {
      return;
    }

    // No need to validate if a moderation state has not ben set.
    if ($value->isEmpty()) {
      return;
    }
    // No need to validate when there is no time set.
    if (!isset($entity->unpublish_on->value)) {
      return;
    }

    $publish_state = $entity->publish_state->value === '_none' ? NULL : $entity->publish_state->value;
    $unpublish_state = $entity->unpublish_state->value;
    $moderation_state = $entity->moderation_state->value;

    // If the publish state has been set then we need to validate that the
    // transition from the set published state to the un-publish state is
    // a valid transition.
    if ($publish_state && !$this->isValidTransition($entity, $publish_state, $unpublish_state)) {
      $this->context
        ->buildViolation($constraint->invalidPublishToUnPublishTransitionMessage, [
          '%publish_state' => $publish_state,
          '%unpublish_state' => $unpublish_state,
        ])
        ->atPath('publish_state')
        ->addViolation();
    }

    // If a publishing state has not been set then we need to validate that
    // the un-publish state is a valid transition based on the entity's
    // current moderation state.
    if (!$publish_state && !$this->isValidTransition($entity, $moderation_state, $unpublish_state)) {
      $this->context
        ->buildViolation($constraint->invalidUnPublishTransitionMessage, [
          '%unpublish_state' => $unpublish_state,
          '%content_state' => $moderation_state,
        ])
        ->atPath('publish_state')
        ->addViolation();
    }
  }

}
