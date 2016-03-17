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

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->addField('node', 'article', 'field_ad_integration', 'Ad settings', 'ad_integration_settings', 'ad_integration_widget');
    $this->addField('taxonomy_term', 'channel', 'field_ad_integration', 'Ad settings', 'ad_integration_settings', 'ad_integration_widget');

  }
}
