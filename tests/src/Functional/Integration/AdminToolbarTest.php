<?php

namespace Drupal\Tests\thunder\Functional\Integration;

use Drupal\Tests\thunder\Functional\ThunderTestBase;

/**
 * Tests integration with the admin toolbar.
 *
 * @group Thunder
 */
class AdminToolbarTest extends ThunderTestBase {

  /**
   * Tests empty menu groups are gone with admin_toolbar_links_access_filter.
   */
  public function testEmptyMenuEntriesAreGone() {

    $this->logWithRole('seo');

    $this->assertSession()->elementNotExists('css', 'nav a[href="/admin/config/people"]');
    $this->assertSession()->elementNotExists('css', 'nav a[href="/admin/config/system"]');
    $this->assertSession()->elementNotExists('css', 'nav a[href="/admin/config/content"]');
    $this->assertSession()->elementNotExists('css', 'nav a[href="/admin/config/development"]');
    $this->assertSession()->elementNotExists('css', 'nav a[href="/admin/config/media"]');

    $this->assertSession()->elementExists('css', 'nav a[href="/admin/config/search"]');
  }

}
