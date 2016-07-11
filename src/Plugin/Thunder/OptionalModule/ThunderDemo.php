<?php

namespace Drupal\thunder\Plugin\Thunder\OptionalModule;

/**
 * Thunder Demo Content.
 *
 * @ThunderOptionalModule(
 *   id = "thunder_demo",
 *   label = @Translation("Thunder Demo Content"),
 * )
 */
class ThunderDemo extends AbstractOptionalModule {

  /**
   * {@inheritdoc}
   */
  public function isStandardlyEnabled() {
    return TRUE;
  }

}
