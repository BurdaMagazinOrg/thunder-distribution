<?php

namespace Drupal\thunder\Installer\Form\OptionalModules;

/**
 * Class ThunderDemo.
 *
 * @package Drupal\thunder\Installer\Form\OptionalModules
 */
class ThunderDemo extends AbstractOptionalModule {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {

    return 'thunder_demo';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormName() {
    return 'Thunder Demo Content';
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled() {
    return TRUE;
  }

}
