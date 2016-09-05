<?php

namespace Drupal\Tests\thunder\FunctionalJavascript;

/**
 * Tests the article creation.
 *
 * @group Thunder
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

    $this->selectMedia('field_teaser_media', 'image_browser', ['media:1']);

    // Paragraph 1
    #$this->addMediaParagraph('field_paragraphs', ['media:5']);

    // Paragraph 2
    #$this->addTextParagraph('field_paragraphs', 'Awesome text');

    // Paragraph 3
    #$this->addGalleryParagraph('field_paragraphs', 'Test gallery', ['media:1', 'media:5']);

    // Paragraph 4
    #$this->addTextParagraph('field_paragraphs', 'Awesome quote', 'quote');

    //$this->createScreenshot('before.jpg');

    $page->pressButton('Save and publish');

    $this->assertSession()->titleEquals('Massive gaining seo traffic text');
    $this->assertSession()->pageTextContains('Test article');
    #$this->assertSession()->pageTextContains('Awesome text');
    #$this->assertSession()->pageTextContains('Awesome quote');

    #$this->assertSession()->elementExists('css', '.field--name-field-paragraphs > div.field__item:nth-child(1) img');
    #$this->assertSession()->elementExists('css', '.field--name-field-paragraphs > div.field__item:nth-child(3) img');

    //$this->createScreenshot('after.jpg');
  }
}
