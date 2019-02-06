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
   * List of used languages.
   *
   * @var \Drupal\Core\Language\LanguageInterface[]
   */
  protected $languages = [];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->languages['en'] = ConfigurableLanguage::createFromLangcode('en');
    $this->languages['de'] = ConfigurableLanguage::createFromLangcode('de');
    $this->languages['de']->save();
  }

  /**
   * Test that basic translation creation works.
   */
  public function testEmptyMenuEntriesAreGone() {

    $this->logWithRole('editor');

    $page = $this->getSession()->getPage();

    $this->drupalGet('node/add/article');
    $page->selectFieldOption('Channel', 'News');
    $page->fillField('Title', 'English draft');
    $page->fillField('SEO Title', 'English draft');

    $page->pressButton('Save');

    $node = $this->getNodeByTitle('English draft');

    $url = $node->toUrl('drupal:content-translation-add');
    $url->setRouteParameter('source', 'en');
    $url->setRouteParameter('target', 'de');

    $this->drupalGet($url);
    $page->fillField('Title', 'German draft');
    $page->pressButton('Save');
  }

}
