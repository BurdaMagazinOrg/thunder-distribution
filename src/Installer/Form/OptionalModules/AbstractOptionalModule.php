<?php
/**
 * @file
 * Contains
 */

namespace Drupal\thunder\Installer\Form\OptionalModules;


use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

abstract class AbstractOptionalModule extends ConfigFormBase{

  abstract public function getFormName();

  public function buildForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [];
  }

}