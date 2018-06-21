<?php

namespace Drupal\Tests\thunder\Functional\Integration;

use Drupal\thunder\ThunderBaseTest;

/**
 * Tests integration with the redirect.
 *
 * @group Thunder
 */
class RedirectTest extends ThunderBaseTest {

  protected static $modules = ['thunder_demo', 'content_moderation'];

  /**
   * Tests redirect from old URL to new one.
   */
  public function testRedirectFromOldToNewUrl() {

    $this->logWithRole('editor');

    $this->drupalGet('burda-launches-open-source-cms-thunder');
    $this->assertSession()->statusCodeEquals(200);

    $page = $this->getSession()->getPage();

    $this->drupalGet('node/6/edit');
    $page->fillField('SEO Title', 'Burda Launches Worldwide Coalition');
    $page->find('xpath', '//*[@id="edit-moderation-state-0-state"]')
      ->selectOption('published');
    $page->pressButton('Save');

    $this->drupalGet('burda-launches-open-source-cms-thunder');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->addressEquals('burda-launches-worldwide-coalition');
  }

}
