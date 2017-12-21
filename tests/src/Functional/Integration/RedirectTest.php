<?php

namespace Drupal\Tests\thunder\Functional\Integration;

use Drupal\thunder\ThunderBaseTest;

/**
 * Tests integration with the redirect.
 *
 * @group Thunder
 */
class RedirectTest extends ThunderBaseTest {

  /**
   * Tests empty menu groups are gone with admin_toolbar_links_access_filter.
   */
  public function testRedirectFromOldToNewUrl() {

    $this->logWithRole('editor');

    $this->drupalGet('burda-launches-worldwide-coalition-industry-partners-and-releases-open-source-online-cms-platform');
    $this->assertSession()->statusCodeEquals(200);

    $this->drupalGet('node/6/edit');
    $this->getSession()->getPage()->fillField('SEO Title', 'Burda Launches Worldwide Coalition');
    $this->getSession()->getPage()->pressButton('Save');

    $this->drupalGet('burda-launches-worldwide-coalition-industry-partners-and-releases-open-source-online-cms-platform');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->addressEquals('burda-launches-worldwide-coalition');
  }

}
