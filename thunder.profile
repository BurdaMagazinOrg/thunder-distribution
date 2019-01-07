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

  $tasks = [
    'thunder_module_configure_form' => [
      'display_name' => t('Configure additional modules'),
      'type' => 'form',
      'function' => 'Drupal\thunder\Installer\Form\ModuleConfigureForm',
    ],
    'thunder_module_install' => [
      'display_name' => t('Install additional modules'),
      'type' => 'batch',
    ],
    'thunder_finish_installation' => [
      'display_name' => t('Finish installation'),
    ],
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
  // Assign user 1 the "administrator" role.
  $user = User::load(1);
  $user->roles[] = 'administrator';
  $user->save();
}

/**
 * Implements hook_themes_installed().
 */
function thunder_themes_installed($theme_list) {

  if (in_array('infinite', $theme_list)) {

    $configFactory = \Drupal::configFactory();

    $configs = $configFactory->listAll('block.block.infinite_');
    foreach ($configs as $config) {
      $configFactory->getEditable($config)->delete();
    }

    \Drupal::service('module_installer')->install(['infinite_article'], TRUE);

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
    $display = entity_get_display('node', 'article', 'default');

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
    $configFactory->getEditable('infinite.settings')
      ->set('logo.use_default', FALSE)
      ->set('logo.path', $profilePath . '/themes/thunder_base/images/Thunder-white_400x90.png')
      ->set('favicon.use_default', FALSE)
      ->set('favicon.path', $profilePath . '/themes/thunder_base/favicon.ico')
      ->save(TRUE);

    // Set default pages.
    $configFactory->getEditable('system.site')
      ->set('page.front', '/taxonomy/term/1')
      ->save(TRUE);

    // Set infinite image styles and gallery view mode.
    $configFactory->getEditable('core.entity_view_display.media.image.default')
      ->set('content.field_image.settings.image_style', 'inline_m')
      ->set('content.field_image.settings.responsive_image_style', '')
      ->save(TRUE);
    $configFactory->getEditable('core.entity_view_display.media.gallery.default')
      ->set('content.field_media_images.settings.view_mode', 'gallery')
      ->save(TRUE);
  }
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
 * Implements hook_modules_installed().
 */
function thunder_modules_installed($modules) {

  if (in_array('content_moderation', $modules)) {
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
    if (\Drupal::service('module_handler')->moduleExists('scheduler')) {
      \Drupal::service('module_installer')->install(['scheduler_content_moderation_integration']);
    }
  }
  if (in_array('scheduler', $modules)) {
    if (\Drupal::service('module_handler')->moduleExists('content_moderation')) {
      \Drupal::service('module_installer')->install(['scheduler_content_moderation_integration']);
    }
  }

  // Move fields into form display.
  if (in_array('ivw_integration', $modules)) {

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

  // Enable riddle paragraph in field_paragraphs.
  if (in_array('thunder_riddle', $modules)) {

    /** @var \Drupal\field\Entity\FieldConfig $field */
    $field = \Drupal::entityTypeManager()->getStorage('field_config')->load('node.article.field_paragraphs');

    $settings = $field->getSetting('handler_settings');

    $settings['target_bundles']['riddle'] = 'riddle';
    $settings['target_bundles_drag_drop']['riddle'] = ['enabled' => TRUE, 'weight' => 10];

    $field->setSetting('handler_settings', $settings);

    $field->save();
  }

  // When enabling password policy, enabled required sub modules.
  if (in_array('password_policy', $modules)) {
    \Drupal::service('module_installer')->install(['password_policy_length']);
    \Drupal::service('module_installer')->install(['password_policy_history']);
    \Drupal::service('module_installer')->install(['password_policy_character_types']);
    \Drupal::service('messenger')->addStatus(t('The Password Character Length, Password Policy History and Password Character Types modules have been additionally enabled, they are required by the default policy configuration.'));
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
