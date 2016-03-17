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
class HierarchicalConfig extends AbstractOptionalModule {

  public function getFormId() {

    return 'hierarchical_config';
  }

  public function getFormName() {
    return 'Hierarchical Config';
  }

}
