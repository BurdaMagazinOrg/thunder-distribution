<?php

namespace Drupal\Tests\thunder\Functional\Integration;

use Drupal\thunder\ThunderBaseTest;

/**
 * Tests integration with the redirect.
 *
 * @group Thunder
 */
class RedirectTest extends ThunderBaseTest {

  protected static $modules = ['thunder_demo'];

  /**
   * Tests redirect from old URL to new one.
   */
  public function testRedirectFromOldToNewUrl() {

    $this->logWithRole('editor');

    $this->drupalGet('burda-launches-open-source-cms-thunder');
    $this->assertSession()->statusCodeEquals(200);

    $this->drupalGet('node/6/edit');
    $this->getSession()->getPage()->fillField('SEO Title', 'Burda Launches Worldwide Coalition');
    $this->getSession()->getPage()->pressButton('Save');

    $this->drupalGet('burda-launches-open-source-cms-thunder');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->addressEquals('burda-launches-worldwide-coalition');
  }

}
