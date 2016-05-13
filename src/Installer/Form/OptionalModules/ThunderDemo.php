<?php

namespace Drupal\thunder\Installer\Form\OptionalModules;

/**
 * @file
 * Contains
 */
class ThunderDemo extends AbstractOptionalModule {

  public function getFormId() {

    return 'thunder_demo';
  }

  public function getFormName() {
    return 'Thunder Demo Content';
  }

  public function isEnabled() {
    return TRUE;
  }
}
