<?php

namespace Drupal\Tests\thunder\Functional\Integration;

use Drupal\Tests\thunder\Functional\ThunderTestBase;

/**
 * Tests integration with the redirect.
 *
 * @group Thunder
 */
class RedirectTest extends ThunderTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['thunder_testing_demo', 'content_moderation'];

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
    $page->find('xpath', '//*[@id="edit-moderation-state-0"]')
      ->selectOption('published');
    $page->pressButton('Save');

    $this->drupalGet('burda-launches-open-source-cms-thunder');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->addressEquals('burda-launches-worldwide-coalition');
  }

}
