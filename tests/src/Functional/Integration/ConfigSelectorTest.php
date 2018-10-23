<?php

namespace Drupal\Tests\thunder\Functional\Integration;

use Drupal\Tests\thunder\Functional\ThunderTestBase;
use Drupal\Tests\thunder\FunctionalJavascript\ThunderJavascriptTestBase;

/**
 * Tests integration with the config_selector.
 *
 * @group Thunder
 */
class ConfigSelectorTest extends ThunderJavascriptTestBase {

  protected static $modules = ['thunder_demo'];

  /**
   * Tests content view with and without search_api.
   */
  public function testContentViewSearchApi() {

    $this->logWithRole('administrator');

    $assert_session = $this->assertSession();

    // Content lock fields are there by default.
    $this->drupalGet('admin/content');
    $assert_session->elementExists('xpath', '//*[@id="view-title-table-column"]/a');
    $assert_session->elementExists('css', '#block-thunder-admin-content > div > div.view-content');

    // Install thunder_search.
    $this->drupalPostForm('admin/modules', ['modules[thunder_search][enable]' => 1], 'Install');
    $this->drupalGet('admin/config/search/search-api/index/content');
    $this->click('#edit-submit');

    // Now we have a search_api based view.
    $this->drupalGet('admin/config/search/search-api/index/content');
    $this->getSession()->getPage()->pressButton('Track now');


    $this->drupalGet('admin/config/search/search-api/index/content');
    #$this->drupalGet('admin/content');
    $assert_session->elementExists('xpath', '//*[@id="view-label-table-column"]/a');
    $assert_session->elementExists('css', '#block-thunder-admin-content > div > div.view-content-search-api');

    // Uninstall search_api.
    $this->drupalPostForm('admin/modules/uninstall', ['uninstall[thunder_search]' => 1, 'uninstall[search_api]' => 1], 'Uninstall');
    $this->click('#edit-submit');

    // The normal view is back.
    $this->drupalGet('admin/content');
    $assert_session->elementExists('xpath', '//*[@id="view-title-table-column"]/a');
    $assert_session->elementExists('css', '#block-thunder-admin-content > div > div.view-content');
  }

}
