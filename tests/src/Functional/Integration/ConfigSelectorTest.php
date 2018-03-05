<?php

namespace Drupal\Tests\thunder\Functional\Integration;

use Drupal\thunder\ThunderBaseTest;

/**
 * Tests integration with the config_selector.
 *
 * @group Thunder
 */
class ConfigSelectorTest extends ThunderBaseTest {

  /**
   * Tests content view with and without content_lock.
   */
  public function testContentViewContentLock() {

    $this->logWithRole('administrator');

    $assert_session = $this->assertSession();

    // Content lock fields are there by default.
    $this->drupalGet('admin/content');
    $assert_session->elementExists('xpath', '//*[@id="view-title-table-column"]/a');
    $assert_session->elementExists('xpath', '//*[@id="view-is-locked-table-column"]/a');

    // Uninstall content_lock.
    $this->drupalPostForm('admin/modules/uninstall', ['uninstall[content_lock]' => 1], 'Uninstall');
    $this->click('#edit-submit');

    // Now we have a view without content_lock fields.
    $this->drupalGet('admin/content');
    $assert_session->elementExists('xpath', '//*[@id="view-title-table-column"]/a');
    $assert_session->elementNotExists('xpath', '//*[@id="view-is-locked-table-column"]/a');

    // Install content_lock again.
    $this->drupalPostForm('admin/modules', ['modules[content_lock][enable]' => 1], 'Install');

    // Content lock fields are there again.
    $this->drupalGet('admin/content');
    $assert_session->elementExists('xpath', '//*[@id="view-title-table-column"]/a');
    $assert_session->elementExists('xpath', '//*[@id="view-is-locked-table-column"]/a');

  }

}
