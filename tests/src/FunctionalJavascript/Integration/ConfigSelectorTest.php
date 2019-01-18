<?php

namespace Drupal\Tests\thunder\FunctionalJavascript\Integration;

use Drupal\Tests\thunder\FunctionalJavascript\ThunderJavascriptTestBase;

/**
 * Tests integration with the config_selector.
 *
 * @group Thunder
 */
class ConfigSelectorTest extends ThunderJavascriptTestBase {

  protected static $modules = ['thunder_demo'];

  protected static $defaultUserRole = 'administrator';

  /**
   * Tests content view with and without search_api.
   */
  public function testContentViewSearchApi() {

    $assert_session = $this->assertSession();

    // Content lock fields are there by default.
    $this->drupalGet('admin/content');
    $assert_session->elementExists('xpath', '//*[@id="view-title-table-column"]/a');
    $assert_session->elementExists('css', '#block-thunder-admin-content > div > div.view-content');

    // Install search_api.
    $module_installer = \Drupal::service('module_installer');
    $module_installer->install(['search_api']);

    // Now we have a search_api based view.
    $this->drupalGet('admin/config/search/search-api/index/content');
    $this->getSession()->getPage()->pressButton('Index now');
    $assert_session->waitForId('edit-index-now');

    $this->drupalGet('admin/content');
    $assert_session->elementExists('xpath', '//*[@id="view-title-table-column"]/a');
    $assert_session->elementExists('css', '#block-thunder-admin-content > div > div.view-content-search-api');

    // Uninstall search_api.
    $module_installer->uninstall(['search_api']);
    drupal_flush_all_caches();

    // The normal view is back.
    $this->drupalGet('admin/content');
    $assert_session->elementExists('xpath', '//*[@id="view-title-table-column"]/a');
    $assert_session->elementExists('css', '#block-thunder-admin-content > div > div.view-content');
  }

}
