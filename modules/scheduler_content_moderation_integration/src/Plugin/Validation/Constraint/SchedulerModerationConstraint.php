<?php

namespace Drupal\scheduler_content_moderation_integration\Plugin\Validation\Constraint;

use Drupal\Core\Entity\Plugin\Validation\Constraint\CompositeConstraintBase;

/**
 * Validates publish on values.
 *
 * @Constraint(
 *   id = "SchedulerModeration",
 *   label = @Translation("Scheduler publish state transition validation", context = "Validation"),
 *   type = "entity:node"
 * )
 */
class SchedulerModerationConstraint extends CompositeConstraintBase {

  /**
   * Message for invalid publishing/un-publishing to a moderation state.
   *
   * @var string
   */
  public $messagePublishModerationInvalid = "Invalid moderation transition, adjust the node's current state.";

  /**
   * {@inheritdoc}
   */
  public function coversFields() {
    return ['publish_state', 'unpublish_state'];
  }

}
