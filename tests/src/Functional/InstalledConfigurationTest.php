<?php

namespace Drupal\Tests\thunder\Functional;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\Schema\SchemaCheckTrait;

/**
 * Test for checking of configuration after install of thunder profile.
 *
 * @package Drupal\Tests\thunder\Kernel
 *
 * @group ThunderConfig
 */
class InstalledConfigurationTest extends ThunderTestBase {

  use SchemaCheckTrait;

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
  protected static $modules = [
    'thunder_testing_demo',
    'google_analytics',
    'nexx_integration',
    'ivw_integration',
    'adsense',
    'thunder_riddle',
    'harbourmaster',
    'simple_gmap',

    // Additional modules.
    // 'thunder_fia',
    // We are messing around with configuration in
    // thunder_liveblog_module_preinstall, so it's not possible to check the
    // thunder_liveblog config in a proper way.
    // 'thunder_liveblog',
    // TODO: Uncomment this when https://www.drupal.org/node/2860803 is fixed.
    // 'amp'
    // end of list.
  ];

  /**
   * Theme name that will be used on installation of test.
   *
   * @var string
   */
  protected $defaultTheme = 'stable';

  /**
   * Ignore list of Core related configurations.
   *
   * @var array
   */
  protected static $ignoreCoreConfigs = [
    'checklistapi.progress.update_helper_checklist',
    'system.site',
    'core.extension',
    'system.performance',
    'system.theme',

    // Configs created by User module.
    'system.action.user_add_role_action.administrator',
    'system.action.user_add_role_action.editor',
    'system.action.user_add_role_action.restricted_editor',
    'system.action.user_add_role_action.seo',
    'system.action.user_remove_role_action.administrator',
    'system.action.user_remove_role_action.editor',
    'system.action.user_remove_role_action.restricted_editor',
    'system.action.user_remove_role_action.seo',
    'system.action.user_add_role_action.harbourmaster',
    'system.action.user_remove_role_action.harbourmaster',

    // Configs created by Token module.
    'core.entity_view_mode.access_token.token',
    'core.entity_view_mode.block.token',
    'core.entity_view_mode.content_moderation_state.token',
    'core.entity_view_mode.crop.token',
    'core.entity_view_mode.file.token',
    'core.entity_view_mode.menu_link_content.token',
    'core.entity_view_mode.node.token',
    'core.entity_view_mode.paragraph.token',
    'core.entity_view_mode.taxonomy_term.token',
    'core.entity_view_mode.user.token',
  ];

  /**
   * Ignore custom keys that are changed during installation process.
   *
   * @var array
   */
  protected static $ignoreConfigKeys = [
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

    // Changed on installation.
    'views.view.glossary' => [
      'dependencies' => [
        'config' => TRUE,
      ],
      'display' => [
        'page_1' => ['cache_metadata' => ['max-age' => TRUE]],
        'attachment_1' => ['cache_metadata' => ['max-age' => TRUE]],
        'default' => ['cache_metadata' => ['max-age' => TRUE]],
      ],
    ],
    'views.view.watchdog' => [
      'display' => [
        'page' => ['cache_metadata' => ['max-age' => TRUE]],
        'default' => ['cache_metadata' => ['max-age' => TRUE]],
      ],
    ],
    'views.view.files' => [
      'display' => [
        'page_1' => ['cache_metadata' => ['max-age' => TRUE]],
        'page_2' => ['cache_metadata' => ['max-age' => TRUE]],
        'default' => ['cache_metadata' => ['max-age' => TRUE]],
      ],
    ],
    'views.view.moderated_content' => [
      'display' => [
        'moderated_content' => ['cache_metadata' => ['max-age' => TRUE, 'tags' => TRUE]],
        'default' => ['cache_metadata' => ['max-age' => TRUE, 'tags' => TRUE]],
      ],
    ],
    // Diff Module: changed on installation of module when additional library
    // exists on system: mkalkbrenner/php-htmldiff-advanced.
    'diff.settings' => [
      'general_settings' => [
        'layout_plugins' => [
          'visual_inline' => [
            'enabled' => TRUE,
          ],
        ],
      ],
    ],

    // The thunder profile changes article and channel taxonomy when ivw module
    // is installed.
    'core.entity_form_display.node.article.default' => [
      'content' => [
        'field_ivw' => TRUE,
      ],
      'dependencies' => [
        'config' => TRUE,
        'module' => TRUE,
      ],
    ],
    'core.entity_form_display.taxonomy_term.channel.default' => [
      'content' => [
        'field_ivw' => TRUE,
      ],
      'dependencies' => [
        'config' => TRUE,
        'module' => TRUE,
      ],
    ],
    // Remove this when
    // https://github.com/BurdaMagazinOrg/module-nexx_integration/pull/37 lands.
    'core.entity_form_display.media.nexx_video.default' => [
      'content' => [
        'path' => TRUE,
        'moderation_state' => TRUE,
      ],
    ],
    'core.entity_form_display.paragraph.nexx_video.default' => [
      'content' => [
        'moderation_state' => TRUE,
      ],
    ],
    'core.entity_form_display.paragraph.nexx_video.default' => [
      'content' => [
        'moderation_state' => TRUE,
      ],
    ],
    'paragraphs.paragraphs_type.nexx_video' => [
      'icon_uuid' => TRUE,
      'description' => TRUE,
    ],
    // Riddle paragraph is added dynamically by thunder profile on
    // thunder_riddle installation.
    'field.field.node.article.field_paragraphs' => [
      'dependencies' => [
        'config' => TRUE,
      ],
      'settings' => [
        'handler_settings' => [
          'target_bundles' => [
            'riddle' => TRUE,
          ],
          'target_bundles_drag_drop' => [
            'riddle' => TRUE,
          ],
        ],
      ],
    ],
    // Drupal 8.6.x adds the anchor key to the crop schema.
    // As long as there is no release of Drupal 8.6.x we cannot provide a patch
    // To the slick module. As soon as 8.6.0 is released we should provide that
    // patch to get rid of this ignored key.
    'image.style.slick_media' => [
      'effects' => [
        '4b434ce0-90cc-44c3-9423-35d7cccc7d93' => [
          'data' => [
            'anchor' => TRUE,
          ],
        ],
      ],
    ],
  ];

  /**
   * Configuration key path separator.
   *
   * @var string
   */
  protected static $configPathSeparator = '::';

  /**
   * Ignore configuration list values. Path to key is separated by '::'.
   *
   * Example:
   * 'field.field.node.article.field_example' => [
   *   'settings::settings_part1::list_part' => [
   *      'ignore_entry1',
   *      'ignore_entry5',
   *   ]
   * ]
   *
   * TODO: use this functionality for more strict "dependencies" checking.
   *
   * @var array
   */
  protected static $ignoreConfigListValues = [
    // Riddle permissions are added dynamically by thunder_riddle installation.
    'user.role.editor' => [
      'permissions' => [
        'access riddle_browser entity browser pages',
      ],
    ],
    'user.role.seo' => [
      'permissions' => [
        'access riddle_browser entity browser pages',
      ],
    ],
    // Google analytics adds one permission dynamically in the install hook.
    'user.role.authenticated' => [
      'permissions' => [
        'opt-in or out of google analytics tracking',
      ],
    ],
  ];

  /**
   * List of contribution settings that should be ignored.
   *
   * All these settings exists in module configuration Yaml files, but they are
   * not in sync with configuration that is set after installation.
   *
   * @var array
   */
  protected static $ignoreConfigs = [];

  /**
   * Set default theme for test.
   *
   * @param string $defaultTheme
   *   Default Theme.
   */
  protected function setDefaultTheme($defaultTheme) {
    \Drupal::service('theme_installer')->install([$defaultTheme]);

    $themeConfig = \Drupal::configFactory()->getEditable('system.theme');
    $themeConfig->set('default', $defaultTheme);
    $themeConfig->save();
  }

  /**
   * Return cleaned-up configurations provided as argument.
   *
   * @param array $configurations
   *   List of configurations that will be cleaned-up and returned.
   * @param string $configurationName
   *   Configuration name for provided configurations.
   *
   * @return array
   *   Returns cleaned-up configurations.
   */
  protected function cleanupConfigurations(array $configurations, $configurationName) {
    /** @var \Drupal\Core\Config\ExtensionInstallStorage $optionalStorage */
    $optionalStorage = \Drupal::service('config_update.extension_optional_storage');

    $configCleanup = [];
    $ignoreListRules = [];

    // Apply ignore for defined configurations and custom properties.
    if (array_key_exists($configurationName, static::$ignoreConfigKeys)) {
      $configCleanup = static::$ignoreConfigKeys[$configurationName];
    }

    if (array_key_exists($configurationName, static::$ignoreConfigListValues)) {
      foreach (static::$ignoreConfigListValues[$configurationName] as $keyPath => $ignoreValues) {
        $ignoreListRules[] = [
          'key_path' => explode(static::$configPathSeparator, $keyPath),
          'ignore_values' => $ignoreValues,
        ];
      }
    }

    // Ignore configuration dependencies in case of optional configuration.
    if ($optionalStorage->exists($configurationName)) {
      $configCleanup = NestedArray::mergeDeep(
        $configCleanup,
        ['dependencies' => TRUE]
      );
    }

    // If configuration doesn't require cleanup, just return configurations as
    // they are.
    if (empty($configCleanup) && empty($ignoreListRules)) {
      return $configurations;
    }

    // Apply cleanup for configurations.
    foreach ($configurations as $index => $arrayToOverwrite) {
      $configurations[$index] = NestedArray::mergeDeep(
        $arrayToOverwrite,
        $configCleanup
      );

      foreach ($ignoreListRules as $ignoreRule) {
        $list = $this->cleanupConfigList(
          NestedArray::getValue($configurations[$index], $ignoreRule['key_path']),
          $ignoreRule['ignore_values']
        );

        NestedArray::setValue($configurations[$index], $ignoreRule['key_path'], $list);
      }
    }

    return $configurations;
  }

  /**
   * Clean up configuration list values.
   *
   * @param array $list
   *   List of values in configuration object.
   * @param array $ignoreValues
   *   Array with list of values that should be ignored.
   *
   * @return array
   *   Return cleaned-up list.
   */
  protected function cleanupConfigList(array $list, array $ignoreValues) {
    $cleanList = $list;

    if (!empty($cleanList)) {
      foreach ($ignoreValues as $ignoreValue) {
        if (!in_array($ignoreValue, $cleanList)) {
          $cleanList[] = $ignoreValue;
        }
      }
    }
    else {
      $cleanList = $ignoreValues;
    }

    // Sorting is required to get same order for configuration compare.
    sort($cleanList);

    return $cleanList;
  }

  /**
   * Compare active configuration with configuration Yaml files.
   */
  public function testInstalledConfiguration() {
    $this->setDefaultTheme($this->defaultTheme);

    /** @var \Drupal\config_update\ConfigReverter $configUpdate */
    $configUpdate = \Drupal::service('config_update.config_update');

    /** @var \Drupal\Core\Config\TypedConfigManager $typedConfigManager */
    $typedConfigManager = \Drupal::service('config.typed');

    $activeStorage = \Drupal::service('config.storage');
    $installStorage = \Drupal::service('config_update.extension_storage');

    /** @var \Drupal\Core\Config\ExtensionInstallStorage $optionalStorage */
    $optionalStorage = \Drupal::service('config_update.extension_optional_storage');

    // Get list of configurations (active, install and optional).
    $activeList = $activeStorage->listAll();
    $installList = $installStorage->listAll();
    $optionalList = $optionalStorage->listAll();

    // Check that all required configurations are available.
    $installListDiff = array_diff($installList, $activeList);
    $this->assertEquals([], $installListDiff, "All required configurations should be installed.");

    // Filter active list.
    $activeList = array_diff($activeList, static::$ignoreCoreConfigs);

    // Check that all active configuration are provided by Yaml files.
    $activeListDiff = array_diff($activeList, $installList, $optionalList);
    $this->assertEquals([], $activeListDiff, "All active configurations should be defined in Yaml files.");

    /** @var \Drupal\config_update\ConfigDiffer $configDiffer */
    $configDiffer = \Drupal::service('config_update.config_diff');

    $differentConfigNames = [];
    $schemaCheckFail = [];
    foreach ($activeList as $activeConfigName) {
      // Skip incorrect configuration from contribution modules.
      if (in_array($activeConfigName, static::$ignoreConfigs)) {
        continue;
      }

      // Get configuration from file and active configuration.
      $activeConfig = $configUpdate->getFromActive('', $activeConfigName);
      $fileConfig = $configUpdate->getFromExtension('', $activeConfigName);

      // Validate fetched configuration against corresponding schema.
      if ($typedConfigManager->hasConfigSchema($activeConfigName)) {
        // Validate active configuration.
        if ($this->checkConfigSchema($typedConfigManager, $activeConfigName, $activeConfig) !== TRUE) {
          $schemaCheckFail['active'][] = $activeConfigName;
        }

        // Validate configuration from file.
        if ($this->checkConfigSchema($typedConfigManager, $activeConfigName, $fileConfig) !== TRUE) {
          $schemaCheckFail['file'][] = $activeConfigName;
        }
      }
      else {
        $schemaCheckFail['no-schema'][] = $activeConfigName;
      }

      // Clean up configuration if it's required.
      list($activeConfig, $fileConfig) = $this->cleanupConfigurations(
        [
          $activeConfig,
          $fileConfig,
        ],
        $activeConfigName
      );

      // Check is active configuration same as in Yaml file.
      if (!$configDiffer->same($fileConfig, $activeConfig)) {
        $differentConfigNames[] = $activeConfigName;
      }
    }

    // Output different configuration names and failed schema checks.
    if (!empty($differentConfigNames) || !empty($schemaCheckFail)) {
      $errorOutput = [
        'configuration-diff' => $differentConfigNames,
        'schema-check' => $schemaCheckFail,
      ];

      throw new \Exception('Configuration difference is found: ' . print_r($errorOutput, TRUE));
    }
  }

}
