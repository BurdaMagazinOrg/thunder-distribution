<?php

namespace Drupal\Tests\thunder\Functional;

use Drupal\Component\Utility\NestedArray;
use Drupal\Tests\BrowserTestBase;
use Drupal\thunder\ThunderTestTrait;

/**
 * Test for checking of configuration after install of thunder profile.
 *
 * TODO:
 * - optional configuration test generalization, because of dependencies.
 * - check config against schema.
 *     That can be later used to verify update hooks.
 * - check configuration for other our modules (fe. facebook, amp, riddle, etc.)
 *
 * @package Drupal\Tests\thunder\Kernel
 *
 * @group Thunder
 */
class InstalledConfigurationTest extends BrowserTestBase {

  use ThunderTestTrait;

  /**
   * Modules to enable.
   *
   * The test runner will merge the $modules lists from this class, the class
   * it extends, and so on up the class hierarchy. It is not necessary to
   * include modules in your list that a parent class has already declared.
   *
   * @var string[]
   *
   * @see \Drupal\Tests\BrowserTestBase::installDrupal()
   */
  protected static $modules = ['config_update', 'thunder_demo'];

  /**
   * The profile to install as a basis for testing.
   *
   * @var string
   */
  protected $profile = 'thunder';

  /**
   * Ignore list of Core related configurations.
   *
   * @var array
   */
  protected static $ignoreCoreSettings = [
    'checklistapi.progress.thunder_updater',
    'thunder_base.settings',
    'system.site',
    'core.extension',
    'system.performance',

    // Configs created by User module.
    'system.action.user_add_role_action.administrator',
    'system.action.user_add_role_action.editor',
    'system.action.user_add_role_action.seo',
    'system.action.user_remove_role_action.administrator',
    'system.action.user_remove_role_action.editor',
    'system.action.user_remove_role_action.seo',

    // Configs created by Token module.
    'core.entity_view_mode.access_token.token',
    'core.entity_view_mode.block.token',
    'core.entity_view_mode.crop.token',
    'core.entity_view_mode.file.token',
    'core.entity_view_mode.menu_link_content.token',
    'core.entity_view_mode.node.token',
    'core.entity_view_mode.paragraph.token',
    'core.entity_view_mode.taxonomy_term.token',
    'core.entity_view_mode.user.token',

    // Core Tour/Language.
    'tour.tour.language',
    'tour.tour.language-add',
    'tour.tour.language-edit',
  ];

  /**
   * Ignore custom keys that are changed during installation process.
   *
   * @var array
   */
  protected static $ignoreConfigKeys = [
    // Node settings is changed by Thunder Install hook.
    'node.settings' => [
      'use_admin_theme' => TRUE,
    ],

    // It's not exported in Yaml, so that new key is generated.
    'scheduler.settings' => [
      'lightweight_cron_access_key' => TRUE,
    ],

    // Changed on installation.
    'system.date' => [
      'timezone' => [
        'default' => TRUE,
      ],
    ],

    // Changed on installation.
    'system.file' => [
      'path' => [
        'temporary' => TRUE,
      ],
    ],

    // Changed on installation.
    'update.settings' => [
      'notification' => [
        'emails' => TRUE,
      ],
    ],

    // Changed on Testing.
    'system.logging' => [
      'error_level' => TRUE,
    ],

    // Changed on Testing.
    'system.mail' => [
      'interface' => ['default' => TRUE],
    ],

    // User register is changed by Thunder Install hook.
    'user.settings' => [
      'register' => TRUE,
    ],

    // Media view status is changed by Thunder Install hook.
    'views.view.media' => [
      'dependencies' => [
        'config' => TRUE,
      ],
      'status' => TRUE,
    ],

    // Pathauto module, optional settings.
    'system.action.pathauto_update_alias_user' => [
      'dependencies' => [
        'module' => TRUE,
      ],
    ],
    'system.action.pathauto_update_alias_node' => [
      'dependencies' => [
        'module' => TRUE,
      ],
    ],


    // Changed on installation.
    'views.view.glossary' => [
      'dependencies' => [
        'config' => TRUE,
      ],
    ],

    // Changed on installation.
    'views.view.content_recent' => [
      'display' => [
        'block_1' => ['cache_metadata' => ['max-age' => TRUE]],
        'default' => ['cache_metadata' => ['max-age' => TRUE]],
      ],
    ],
  ];

  /**
   * List of contribution settings that should be ignored.
   *
   * TODO: Add list of Drupal tickets.
   *
   * All these settings exists in module configuration Yaml files, but they are
   * not in sync with configuration that is set after installation.
   *
   * @var array
   */
  protected static $ignoreContribSettings = [
    // Slick media module.
    'core.entity_view_mode.media.slick',

    // Paragraphs module.
    'core.entity_view_mode.paragraph.preview',

    // Focal Point module. Issue: https://www.drupal.org/node/2851587
    'crop.type.focal_point',

    // Metatag module. Issue: https://www.drupal.org/node/2851582.
    'metatag.metatag_defaults.403',
    'metatag.metatag_defaults.404',
    'metatag.metatag_defaults.front',
    'metatag.metatag_defaults.global',
    'metatag.metatag_defaults.node',
    'metatag.metatag_defaults.taxonomy_term',
    'metatag.metatag_defaults.user',
  ];

  /**
   * Compare active configuration with configuration Yaml files.
   */
  public function testInstalledConfiguration() {
    /** @var \Drupal\config_update\ConfigReverter $configUpdate */
    $configUpdate = \Drupal::service('config_update.config_update');

    $activeStorage = \Drupal::service('config.storage');
    $installStorage = \Drupal::service('config_update.extension_storage');
    $optionalStorage = \Drupal::service('config_update.extension_optional_storage');

    // Get list of configurations (active, install and optional).
    $activeList = $activeStorage->listAll();
    $installList = $installStorage->listAll();
    $optionalList = $optionalStorage->listAll();

    // Check that all required configurations are available.
    $installListDiff = array_diff($installList, $activeList);
    $this->assertEquals([], $installListDiff, "All required configurations should be installed.");

    // Filter active list.
    $activeList = array_diff($activeList, static::$ignoreCoreSettings);

    // Check that all active configuration are provided by Yaml files.
    $activeListDiff = array_diff($activeList, $installList, $optionalList);
    $this->assertEquals([], $activeListDiff, "All active configurations should be defined in Yaml files.");

    /** @var \Drupal\config_update\ConfigDiffer $configDiffer */
    $configDiffer = \Drupal::service('config_update.config_diff');

    $differentConfigs = [];
    $differentConfigNames = [];
    foreach ($activeList as $activeConfigName) {
      // Skip incorrect configuration from contribution modules.
      if (in_array($activeConfigName, static::$ignoreContribSettings)) {
        continue;
      }

      // Get configuration from file and active configuration.
      $activeConfig = $configUpdate->getFromActive('', $activeConfigName);
      $fileConfig = $configUpdate->getFromExtension('', $activeConfigName);

      // Apply ignore for defined configurations and custom properties.
      if (array_key_exists($activeConfigName, static::$ignoreConfigKeys)) {
        $activeConfig = NestedArray::mergeDeep(
          $activeConfig,
          static::$ignoreConfigKeys[$activeConfigName]
        );

        $fileConfig = NestedArray::mergeDeep(
          $fileConfig,
          static::$ignoreConfigKeys[$activeConfigName]
        );
      }

      // Check is configuration same as in Yaml file.
      if (!$configDiffer->same($fileConfig, $activeConfig)) {
        $differentConfigNames[] = $activeConfigName;

        $differentConfigs[$activeConfigName] = [
          'active' => $activeConfig,
          'file' => $fileConfig,
        ];
      }
    }

    // Output different configuration names.
    if (!empty($differentConfigNames)) {
      echo 'Different configurations: ' . PHP_EOL . print_r($differentConfigs, TRUE) . PHP_EOL;
    }
  }

}
