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
class FacebookInstantArticles extends AbstractOptionalModule {

  public function getFormId() {

    return 'facebook_instant_articles';
  }

  public function getFormName() {
    return 'Facebook Instant Articles';
  }
}
