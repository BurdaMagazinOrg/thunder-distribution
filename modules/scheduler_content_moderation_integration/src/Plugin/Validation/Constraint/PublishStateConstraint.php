<?php

namespace Drupal\scheduler_content_moderation_integration\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Validates scheduler publish state.
 *
 * @Constraint(
 *   id = "SchedulerPublishState",
 *   label = @Translation("Scheduler publish state validation", context = "Validation"),
 *   type = "entity:node"
 * )
 */
class PublishStateConstraint extends Constraint {

  /**
   * Publish state invalid transition message.
   *
   * Message to display on invalid publishing transition between the nodes
   * current moderation state to the specified publishing state.
   *
   * @var string
   */
  public $invalidTransitionMessage = 'The scheduled publishing state of %publish_state is not a valid transition from the current moderation state of %content_state for this content.';

}
