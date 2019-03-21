<?php

/**
 * @file
 * Enables modules and site configuration for a thunder site installation.
 */

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\block\Entity\Block;
use Drupal\user\Entity\User;
use Drupal\user\Entity\Role;

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
  $tasks = [];
  if (empty($install_state['config_install_path'])) {
    $tasks['thunder_module_configure_form'] = [
      'display_name' => t('Configure additional modules'),
      'type' => 'form',
      'function' => 'Drupal\thunder\Installer\Form\ModuleConfigureForm',
    ];
    $tasks['thunder_module_install'] = [
      'display_name' => t('Install additional modules'),
      'type' => 'batch',
    ];
  }
  $tasks['thunder_finish_installation'] = [
    'display_name' => t('Finish installation'),
  ];
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
function thunder_module_install(array &$install_state) {

  $modules = $install_state['thunder_additional_modules'];

  $batch = [];
  if ($modules) {
    $operations = [];
    foreach ($modules as $module) {
      $operations[] = [
        '_thunder_install_module_batch',
        [[$module], $module, $install_state['form_state_values']],
      ];
    }

    $batch = [
      'operations' => $operations,
      'title' => t('Installing additional modules'),
      'error_message' => t('The installation has encountered an error.'),
    ];
  }

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
  $context['message'] = t('Installed %module_name modules.', ['%module_name' => $module_name]);
}

/**
 * Finish Thunder installation process.
 *
 * @param array $install_state
 *   The install state.
 *
 * @throws \Drupal\Core\Entity\EntityStorageException
 */
function thunder_finish_installation(array &$install_state) {
  \Drupal::service('config.installer')->installOptionalConfig();

  // Assign user 1 the "administrator" role.
  $user = User::load(1);
  $user->roles[] = 'administrator';
  $user->save();
}

/**
 * Implements hook_themes_installed().
 */
function thunder_themes_installed($theme_list) {

  if (in_array('thunder_amp', $theme_list)) {
    // Install AMP module.
    \Drupal::service('module_installer')->install(['amp'], TRUE);

    \Drupal::configFactory()
      ->getEditable('amp.settings')
      ->set('amp_library_process_full_html', TRUE)
      ->save(TRUE);

    // Set AMP theme to thunder_amp,
    // if not set, or is one of the included themes.
    $ampThemeConfig = \Drupal::configFactory()->getEditable('amp.theme');
    $ampTheme = $ampThemeConfig->get('amptheme');
    if (empty($ampTheme) || $ampTheme == 'ampsubtheme_example' || $ampTheme == 'amptheme') {
      $ampThemeConfig->set('amptheme', 'thunder_amp')
        ->save(TRUE);
    }

    // Disable unused blocks.
    /** @var \Drupal\block\Entity\Block[] $blocks */
    $blocks = Block::loadMultiple([
      'thunder_amp_account_menu',
      'thunder_amp_breadcrumbs',
      'thunder_amp_footer',
      'thunder_amp_local_actions',
      'thunder_amp_local_tasks',
      'thunder_amp_main_menu',
      'thunder_amp_messages',
      'thunder_amp_tools',
    ]);
    foreach ($blocks as $block) {
      $block->disable()->save();
    }

  }

  if (in_array('amptheme', $theme_list)) {
    \Drupal::service('module_installer')->install(['amp'], TRUE);
  }
}

/**
 * Check if provided triggering modules are one of the newly installed modules.
 *
 * This function is helper for thunder_modules_installed(). Using it in another
 * context is not recommended. @see hook_modules_installed()
 *
 * @param array $modules
 *   The list of the modules that were newly installed.
 * @param array $triggering_modules
 *   The list of triggering modules required for executing some action.
 *
 * @return bool
 *   Returns if triggering module is newly installed.
 */
function _thunder_check_triggering_modules(array $modules, array $triggering_modules) {
  // Check that at least one triggering module is in list of the modules that
  // were newly installed.
  $triggering_not_installed_modules = array_diff($triggering_modules, $modules);
  if (count($triggering_not_installed_modules) === count($triggering_modules)) {
    return FALSE;
  }

  // All required triggering modules are in the list of the modules that were
  // newly installed.
  if (empty($triggering_not_installed_modules)) {
    return TRUE;
  }

  /** @var \Drupal\Core\Extension\ModuleHandlerInterface $module_handler */
  $module_handler = Drupal::moduleHandler();
  $active_modules = array_keys($module_handler->getModuleList());

  // Ensure that all triggering modules modules are installed on system.
  $required_not_active_modules = array_diff($triggering_not_installed_modules, $active_modules);

  return empty($required_not_active_modules);
}

/**
 * Check if enabling of a module is executed.
 *
 * This function is helper for thunder_modules_installed(). Using it in another
 * context is not recommended. @see hook_modules_installed()
 *
 * @return bool
 *   Returns if enabling of a module is currently running.
 */
function _thunder_is_enabling_module() {
  return !drupal_installation_attempted() && !Drupal::isConfigSyncing();
}

/**
 * Implements hook_modules_installed().
 */
function thunder_modules_installed($modules) {
  if (
    _thunder_is_enabling_module()
    && _thunder_check_triggering_modules($modules, ['content_moderation', 'config_update'])
  ) {
    if (!Role::load('restricted_editor')) {
      /** @var Drupal\config_update\ConfigRevertInterface $configReverter */
      $configReverter = \Drupal::service('config_update.config_update');
      $configReverter->import('user_role', 'restricted_editor');
    }

    // Granting permissions only for "editor" and "seo" user roles.
    $roles = Role::loadMultiple(['editor', 'seo']);
    foreach ($roles as $role) {
      try {
        $role->grantPermission('use editorial transition create_new_draft');
        $role->grantPermission('use editorial transition publish');
        $role->grantPermission('use editorial transition unpublish');
        $role->grantPermission('use editorial transition unpublished_draft');
        $role->grantPermission('use editorial transition unpublished_published');
        $role->grantPermission('view any unpublished content');
        $role->grantPermission('view latest version');
        $role->save();
      }
      catch (EntityStorageException $storageException) {
      }
    }
  }

  if (
    _thunder_is_enabling_module()
    && _thunder_check_triggering_modules($modules, ['content_moderation', 'scheduler'])
  ) {
    \Drupal::service('module_installer')->install(['scheduler_content_moderation_integration']);
  }

  // When enabling password policy, enabled required sub modules.
  if (
    _thunder_is_enabling_module()
    && _thunder_check_triggering_modules($modules, ['password_policy'])
  ) {
    \Drupal::service('module_installer')->install(['password_policy_length']);
    \Drupal::service('module_installer')->install(['password_policy_history']);
    \Drupal::service('module_installer')->install(['password_policy_character_types']);
    \Drupal::service('messenger')->addStatus(t('The Password Character Length, Password Policy History and Password Character Types modules have been additionally enabled, they are required by the default policy configuration.'));
  }

  // Move fields into form display.
  if (_thunder_check_triggering_modules($modules, ['ivw_integration'])) {
    $fieldWidget = 'ivw_integration_widget';

    // Attach field if channel vocabulary and article node type is
    // present in the distribution.
    try {
      entity_get_form_display('node', 'article', 'default')
        ->setComponent(
          'field_ivw', [
            'type' => $fieldWidget,
          ])->save();
    }
    catch (Exception $e) {
      \Drupal::logger('thunder')->info(t('Could not add ivw field to article node: "@message"', ['@message' => $e->getMessage()]));
    }

    try {
      entity_get_form_display('taxonomy_term', 'channel', 'default')
        ->setComponent('field_ivw', [
          'type' => $fieldWidget,
        ])->save();
    }
    catch (Exception $e) {
      \Drupal::logger('thunder')->info(t('Could not add ivw field to channel taxonomy: "@message"', ['@message' => $e->getMessage()]));
    }
  }
}

/**
 * Implements hook_modules_uninstalled().
 */
function thunder_modules_uninstalled($modules) {
  // Import the content view if it was deleted during module uninstalling.
  // This could happen if content_lock was uninstalled and the content view
  // contained content_lock fields at that time.
  if (in_array('content_lock', $modules, TRUE)) {
    /** @var \Drupal\Core\Routing\RouteProviderInterface $route_provider */
    $route_provider = \Drupal::service('router.route_provider');
    $found_routes = $route_provider->getRoutesByPattern('admin/content');
    $view_found = FALSE;
    foreach ($found_routes->getIterator() as $route) {
      if (!empty($route->getDefault('view_id'))) {
        $view_found = TRUE;
        break;
      }
    }
    if (!$view_found) {
      $config_service = \Drupal::service('config_update.config_update');
      $config_service->import('view', 'content');
    }
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

/**
 * Implements hook_library_info_alter().
 */
function thunder_toolbar_alter(&$items) {
  if (!empty($items['admin_toolbar_tools'])) {
    $items['admin_toolbar_tools']['#attached']['library'][] = 'thunder/toolbar.icon';
  }
}

/**
 * Implements hook_entity_base_field_info_alter().
 */
function thunder_entity_base_field_info_alter(&$fields, EntityTypeInterface $entity_type) {
  if (\Drupal::config('system.theme')->get('admin') == 'thunder_admin' && \Drupal::hasService('content_moderation.moderation_information')) {
    /** @var \Drupal\content_moderation\ModerationInformationInterface $moderation_info */
    $moderation_info = \Drupal::service('content_moderation.moderation_information');
    if ($moderation_info->canModerateEntitiesOfEntityType($entity_type) && isset($fields['moderation_state'])) {
      $fields['moderation_state']->setDisplayOptions('form', [
        'type' => 'thunder_moderation_state_default',
        'weight' => 100,
        'settings' => [],
      ]);
    }
  }
}

/**
 * Implements hook_field_widget_info_alter().
 */
function thunder_field_widget_info_alter(array &$info) {
  if (!\Drupal::moduleHandler()->moduleExists('content_moderation')) {
    unset($info['thunder_moderation_state_default']);
  }
}
