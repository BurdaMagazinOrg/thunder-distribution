<?php

namespace Drupal\thunder\Installer\Form\OptionalModules;

use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * @file
 * Contains
 */
class BreakpointJsSettings extends AbstractOptionalModule {

  public function getFormId() {

    return 'breakpoint_js_settings';
  }

  public function getFormName() {
    return 'Breakpoint js settings';
  }
}
