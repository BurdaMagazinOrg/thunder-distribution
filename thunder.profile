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

/**
 * Implements hook_install_tasks().
 */
function thunder_install_tasks(&$install_state) {

  $tasks = array(
    'thunder_module_configure_form' => array(
      'display_name' => t('Configure additional modules'),
      'type' => 'form',
      'function' => 'Drupal\thunder\Installer\Form\ModuleConfigureForm',
    ),
    'thunder_module_install' => array(
      'display_name' => t('Install additional modules'),
      'type' => 'batch',
    ),
  );

  return $tasks;
}

/**
 * Installs the thunder modules in a batch.
 *
 * @param array $install_state
 *   The install state.
 *
 * @return array
 *   A batch array to execute.
 */
function thunder_module_install(&$install_state) {

  $files = system_rebuild_module_data();

  $modules = $install_state['thunder_additional_modules'];
  $thunder_modules = $modules;
  // Always install required modules first. Respect the dependencies between
  // the modules.
  $required = array();
  $non_required = array();

  // Add modules that other modules depend on.
  foreach ($modules as $module) {
    if ($files[$module]->requires) {
      $module_requires = array_keys($files[$module]->requires);
      // Remove the thunder modules from required modules.
      $module_requires = array_diff_key($module_requires, $thunder_modules);
      $modules = array_merge($modules, $module_requires);
    }
  }
  $modules = array_unique($modules);
  // Remove the thunder modules from to install modules.
  $modules = array_diff_key($modules, $thunder_modules);
  foreach ($modules as $module) {
    if (!empty($files[$module]->info['required'])) {
      $required[$module] = $files[$module]->sort;
    }
    else {
      $non_required[$module] = $files[$module]->sort;
    }
  }
  arsort($required);

  $operations = array();
  foreach ($required + $non_required + $thunder_modules as $module => $weight) {
    $operations[] = array(
      '_thunder_install_module_batch',
      array(array($module), $module, $install_state['form_state_values']),
    );
  }

  $batch = array(
    'operations' => $operations,
    'title' => t('Installing additional modules'),
    'error_message' => t('The installation has encountered an error.'),
  );
  return $batch;

}

/**
 * Implements callback_batch_operation().
 *
 * Performs batch installation of modules.
 */
function _thunder_install_module_batch($module, $module_name, $form_values, &$context) {
  set_time_limit(0);
  \Drupal::service('module_installer')->install($module, $dependencies = TRUE);

  $optionalModulesManager = \Drupal::service('plugin.manager.thunder.optional_modules');

  try {

    $instance = $optionalModulesManager->createInstance($module_name);
    $instance->submitForm($form_values);
  }
  catch (\Exception $e) {

  }

  $context['results'][] = $module;
  $context['message'] = t('Installed %module_name modules.', array('%module_name' => $module_name));
}
