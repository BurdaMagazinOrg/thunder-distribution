<?php

namespace Drupal\Tests\thunder\Functional;

use Drupal\thunder\ThunderBaseTest;

/**
 * Test for checking of module uninstall functionality.
 *
 * @package Drupal\Tests\thunder\Kernel
 *
 * @group ThunderConfig
 */
class ModuleUninstallTest extends ThunderBaseTest {

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

    ['thunder_fia'],
    ['fb_instant_articles'],
    ['thunder_fia', 'fb_instant_articles'],

    ['thunder_liveblog'],
    ['liveblog_pusher', 'liveblog', 'simple_gmap'],
    ['thunder_liveblog', 'liveblog_pusher', 'liveblog', 'simple_gmap'],

    ['diff'],
    ['content_lock'],
    ['checklistapi'],
    // ['nexx_integration'], // fields issue.
    // ['ivw_integration'], // fields issue.
    ['adsense'],
    ['google_analytics'],
    ['amp'],
    ['harbourmaster'],
  ];

  /**
   * Ignore checking of configuration schemas until it's solved.
   *
   * @var array
   */
  protected static $configSchemaCheckerExclusions = [
    'views.view.fb_instant_articles',
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
    foreach (static::$moduleLists as $modules) {
      $this->installModules($modules);
      $this->uninstallModules($modules);
      $this->installModules($modules);
    }
  }

}
