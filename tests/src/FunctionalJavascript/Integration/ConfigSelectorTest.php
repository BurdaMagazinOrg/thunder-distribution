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

    // Install thunder_search.
    \Drupal::service('module_installer')->install(['thunder_search']);

    // Now we have a search_api based view.
    $this->drupalGet('admin/config/search/search-api/index/content');
    $this->getSession()->getPage()->pressButton('Index now');
    $assert_session->waitForId('edit-index-now');

    $this->drupalGet('admin/content');
    $assert_session->elementExists('xpath', '//*[@id="view-label-table-column"]/a');
    $assert_session->elementExists('css', '#block-thunder-admin-content > div > div.view-content-search-api');

    // Uninstall search_api.
    \Drupal::service('module_installer')->uninstall(['thunder_search', 'search_api']);
    drupal_flush_all_caches();

    // The normal view is back.
    $this->drupalGet('admin/content');
    $assert_session->elementExists('xpath', '//*[@id="view-title-table-column"]/a');
    $assert_session->elementExists('css', '#block-thunder-admin-content > div > div.view-content');
  }

}
