<?php

namespace Drupal\Tests\thunder\FunctionalJavascript;

/**
 * Tests the article creation.
 *
 * @group thunder
 */
class ArticleCreationTest extends ThunderJavascriptTestBase {

  use ThunderParagraphsTestTrait;
  use ThunderMediaTestTrait;

  public function testCreateArticle() {

    $this->drupalGet('node/add/article');

    $page = $this->getSession()->getPage();

    $page->selectFieldOption('field_channel', 1);

    $page->fillField('title[0][value]', 'Test article');
    $page->fillField('field_seo_title[0][value]', 'Massive gaining seo traffic text');

    $this->selectMedia('field_teaser_media', 'image_browser', 'media:1');

    $this->addParagraph('field_paragraphs', 'media');
    $this->selectMedia('field_paragraphs_0_subform_field_media', 'media_browser', 'media:5');

    $this->addParagraph('field_paragraphs', 'media');
    $this->selectMedia('field_paragraphs_1_subform_field_media', 'media_browser', 'media:1');

    $page->pressButton('Save and publish');

    $this->assertSession()->titleEquals('Massive gaining seo traffic text');

    $this->assertSession()->pageTextContains('Test article');

    $this->createScreenshot('foo.jpg');

  }
}
