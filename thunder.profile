<?php
/**
 * @file
 * Enables modules and site configuration for a thunder site installation.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_FORM_ID_alter() for install_configure_form().
 *
 * Allows the profile to alter the site configuration form.
 */
function thunder_form_install_configure_form_alter(&$form, FormStateInterface $form_state) {
  // Add a value as example that one can choose an arbitrary site name.
  $form['site_information']['site_name']['#placeholder'] = t('Thunder');
}

function thunder_install_tasks(&$install_state) {

  $tasks = array(
    'thunder_module_configure_form' => array(
      'display_name' => t('Configure modules'),
      'type' => 'form',
      'function' => 'Drupal\thunder\Installer\Form\ModuleConfigureForm',
    ),
  );

  return $tasks;
}

