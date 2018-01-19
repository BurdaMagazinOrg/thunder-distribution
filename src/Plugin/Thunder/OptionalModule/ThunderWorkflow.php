<?php

namespace Drupal\thunder\Plugin\Thunder\OptionalModule;

/**
 * Thunder Workflow.
 *
 * @ThunderOptionalModule(
 *   id = "thunder_workflow",
 *   label = @Translation("Thunder Workflow"),
 *   description = @Translation("Activates content moderation and pre-configures it."),
 *   type = "module",
 *   standardlyEnabled = 0,
 *   weight = -1
 * )
 */
class ThunderWorkflow extends AbstractOptionalModule {}
