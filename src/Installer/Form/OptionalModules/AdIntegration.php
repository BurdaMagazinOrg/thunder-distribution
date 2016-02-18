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
class AdIntegration extends AbstractOptionalModule {

  public function getFormId() {

    return 'ad_integration';
  }

  public function getFormName() {
    return 'Ad Integration';
  }

}
