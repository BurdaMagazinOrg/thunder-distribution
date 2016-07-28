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

  $modules = $install_state['thunder_additional_modules'];

  $operations = array();
  foreach ($modules as $module => $weight) {
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

  $optionalModulesManager = \Drupal::service('plugin.manager.thunder.optional_modules');

  try {
    $definition = $optionalModulesManager->getDefinition($module_name);
    if ($definition['type'] == 'module') {
      \Drupal::service('module_installer')->install($module, TRUE);
    }
    elseif ($definition['type'] == 'theme') {
      \Drupal::service('theme_installer')->install($module, TRUE);
    }

    $instance = $optionalModulesManager->createInstance($module_name);
    $instance->submitForm($form_values);
  }
  catch (\Exception $e) {

  }

  $context['results'][] = $module;
  $context['message'] = t('Installed %module_name modules.', array('%module_name' => $module_name));
}

/**
 * Implements hook_themes_installed().
 */
function thunder_themes_installed($theme_list) {

  if (in_array('infinite', $theme_list)) {

    $configs = Drupal::configFactory()->listAll('block.block.infinite_');
    foreach ($configs as $config) {
      Drupal::configFactory()->getEditable($config)->delete();
    }

    \Drupal::service('module_installer')->install(['infinite_article'], TRUE);
    \Drupal::service('config.installer')->installOptionalConfig();

    // Ensure that footer block is pre-filled with lazy loading block.
    $entityTypeManager = \Drupal::service('entity_type.manager');
    $articles = $entityTypeManager->getStorage('node')->loadByProperties([
      'type' => 'article',
    ]);

    $actionManager = \Drupal::service('plugin.manager.action');
    $resetFooterAction = $actionManager->createInstance('node_reset_footer_blocks_action');
    $resetHeaderAction = $actionManager->createInstance('node_reset_footer_blocks_action');

    foreach ($articles as $article) {
      $resetFooterAction->execute($article);
      $resetHeaderAction->execute($article);
    }

    // Adding header and footer blocks to default article view.
    /** @var \Drupal\Core\Entity\Entity\EntityViewDisplay $display */
    $display = entity_load('entity_view_display', 'node.article.default');

    $display->setComponent('field_header_blocks', [
      'type' => 'entity_reference_entity_view',
      'label' => 'hidden',
      'settings' => [
        'view_mode' => 'default',
      ],
      'weight' => -1,
    ])->setComponent('field_footer_blocks', [
      'type' => 'entity_reference_entity_view',
      'label' => 'hidden',
      'settings' => [
        'view_mode' => 'default',
      ],
      'weight' => 2,
    ])->save();

    $display->save();

    $profilePath = drupal_get_path('profile', 'thunder');
    \Drupal::configFactory()
      ->getEditable('infinite.settings')
      ->set('logo.use_default', 0)
      ->set('logo.path', $profilePath . '/themes/thunder/images/thunder-logo-big.png')
      ->save(TRUE);
  }
}

/**
 * Implements hook_page_attachments().
 */
function thunder_page_attachments(array &$attachments) {

  foreach ($attachments['#attached']['html_head'] as &$html_head) {

    $name = $html_head[1];

    if ($name == 'system_meta_generator') {
      $tag = &$html_head[0];
      $tag['#attributes']['content'] = 'Drupal 8 (Thunder | http://www.thunder.org)';
    }
  }
}