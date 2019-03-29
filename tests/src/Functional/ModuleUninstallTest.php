<?php

namespace Drupal\Tests\thunder\Functional;

/**
 * Test for checking of module uninstall functionality.
 *
 * @package Drupal\Tests\thunder\Kernel
 *
 * @group Thunder
 */
class ModuleUninstallTest extends ThunderTestBase {

  /**
   * Modules to test uninstall/install capability.
   *
   * @var string[]
   */
  protected static $moduleLists = [
    ['thunder_riddle'],
    ['media_riddle_marketplace'],
    ['riddle_marketplace'],
    ['thunder_riddle', 'media_riddle_marketplace', 'riddle_marketplace'],
    ['thunder_liveblog'],
    ['liveblog_pusher', 'liveblog', 'simple_gmap'],
    ['thunder_liveblog', 'liveblog_pusher', 'liveblog', 'simple_gmap'],
    ['diff'],
    ['content_lock'],
    ['adsense'],
    ['google_analytics'],
    ['access_unpublished'],
    ['responsive_preview'],
    ['shariff'],
    ['length_indicator'],
    ['redirect'],
    ['simple_sitemap'],
    [
      'search_api_db',
      'search_api',
      'facets',
      'views_bulk_operations',
      'select2_facets',
    ],

    // ['amp'],
    // Patch provided: https://www.drupal.org/files/issues/2901581_3.patch.
    // ['harbourmaster'],
    // Requires update of project on drupal.org.
    // ['thunder_fia'],
    // ['fb_instant_articles'],
    // ['thunder_fia', 'fb_instant_articles'],
    // Patch provided: https://www.drupal.org/files/issues/2901583_2.patch.
  ];

  /**
   * Install modules.
   *
   * @param array $modules
   *   Modules that should be installed.
   */
  protected function installModules(array $modules = []) {
    if ($modules) {
      $success = $this->container->get('module_installer')
        ->install($modules, TRUE);
      $this->assertTrue($success);

      $this->rebuildContainer();
    }
  }

  /**
   * Uninstall modules.
   *
   * @param array $modules
   *   Modules that should be uninstalled.
   */
  protected function uninstallModules(array $modules = []) {
    if ($modules) {
      $success = $this->container->get('module_installer')
        ->uninstall($modules, TRUE);
      $this->assertTrue($success);

      $this->rebuildContainer();
    }
  }

  /**
   * Compare active configuration with configuration Yaml files.
   */
  public function testModules() {
    $uninstallFailures = [];

    foreach (static::$moduleLists as $modules) {
      try {
        $this->installModules($modules);
        $this->uninstallModules($modules);
        $this->installModules($modules);
      }
      catch (\Exception $e) {
        // Store errors, so that all modules can be tested.
        $uninstallFailures[] = [
          'modules' => $modules,
          'error' => $e->getMessage(),
        ];
      }
    }

    if ($uninstallFailures) {
      // Output all errors for modules tested.
      throw new \Exception(print_r($uninstallFailures, TRUE));
    }
  }

}
