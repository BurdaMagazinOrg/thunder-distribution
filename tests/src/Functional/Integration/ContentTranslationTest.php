<?php

namespace Drupal\Tests\thunder\Functional\Integration;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\thunder\Functional\ThunderTestBase;

/**
 * Tests integration with the content_translation.
 *
 * @group Thunder
 */
class ContentTranslationTest extends ThunderTestBase {

  protected static $modules = [
    'content_translation',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    ConfigurableLanguage::createFromLangcode('de')->save();
  }

  /**
   * Tests empty menu groups are gone with admin_toolbar_links_access_filter.
   */
  public function testEmptyMenuEntriesAreGone() {

    $this->logWithRole('editor');

    $page = $this->getSession()->getPage();

    $this->drupalGet('node/add/article');
    $page->fillField('Title', 'English draft');
    $page->fillField('SEO Title', 'English draft');

    $page->pressButton('Save');



    file_put_contents('foo.html', $page->getHtml());


  }

}
