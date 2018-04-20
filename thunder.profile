<?php

/**
 * @file
 * Enables modules and site configuration for a thunder site installation.
 */

use Drupal\Core\Extension\Extension;
use Drupal\Core\Form\FormStateInterface;
use Drupal\block\Entity\Block;

/**
 * Implements hook_system_info_alter().
 */
function thunder_system_info_alter(array &$info, Extension $file, $type) {
  // Thunder can not work properly without these modules. So they are enforced
  // to be enabled.
  $required_modules = ['config_selector', 'views', 'media', 'node'];
  if ($type == 'module' && in_array($file->getName(), $required_modules)) {
    $info['required'] = TRUE;
  }
}

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

  // Move fields into form display.
  if (in_array('ivw_integration', $modules)) {

    $fieldWidget = 'ivw_integration_widget';

    entity_get_form_display('node', 'article', 'default')
      ->setComponent('field_ivw', [
        'type' => $fieldWidget,
      ])->save();

    entity_get_form_display('taxonomy_term', 'channel', 'default')
      ->setComponent('field_ivw', [
        'type' => $fieldWidget,
      ])->save();
  }

  // Enable riddle paragraph in field_paragraphs.
  if (in_array('thunder_riddle', $modules)) {

    /** @var \Drupal\field\Entity\FieldConfig $field */
    $field = entity_load('field_config', 'node.article.field_paragraphs');

    $settings = $field->getSetting('handler_settings');

    $settings['target_bundles']['riddle'] = 'riddle';
    $settings['target_bundles_drag_drop']['riddle'] = ['enabled' => TRUE, 'weight' => 10];

    $field->setSetting('handler_settings', $settings);

    $field->save();
  }

  $configs = Drupal::configFactory()->loadMultiple(\Drupal::configFactory()->listAll());
  foreach ($configs as $config) {
    $dependencies = $config->get('dependencies.module');
    $enforced_dependencies = $config->get('dependencies.enforced.module');
    $dependencies = $dependencies ?: [];
    $enforced_dependencies = $enforced_dependencies ?: [];
    if (array_intersect($modules, $dependencies) || array_intersect($modules, $enforced_dependencies)) {
      \Drupal::service('config.installer')->installOptionalConfig(NULL, ['config' => $config->getName()]);
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
