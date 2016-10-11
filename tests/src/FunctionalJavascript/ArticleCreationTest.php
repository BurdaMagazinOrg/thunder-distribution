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

  /**
   * Test Creation of Article.
   */
  public function testCreateArticle() {
    $this->drupalGet('node/add/article');

    $page = $this->getSession()->getPage();

    $page->selectFieldOption('field_channel', 1);

    $page->fillField('title[0][value]', 'Test article');
    $page->fillField('field_seo_title[0][value]', 'Massive gaining seo traffic text');

    $this->selectMedia('field_teaser_media', 'image_browser', ['media:1']);

    // Paragraph 1.
    $this->addMediaParagraph('field_paragraphs', ['media:5']);

    // Paragraph 2.
    $this->addTextParagraph('field_paragraphs', 'Awesome text');

    // Paragraph 3.
    $this->addGalleryParagraph('field_paragraphs', 'Test gallery', [
      'media:1',
      'media:5',
    ]);

    // Paragraph 4.
    $this->addTextParagraph('field_paragraphs', 'Awesome quote', 'quote');

    $this->scrollElementInView('#edit-actions');

    $this->createScreenshot($this->getScreenshotFolder() . '/ArticleCreationTest_BeforeSave_' . date('Ymd_His') . '.png');

    $page->pressButton('Save as unpublished');

    $this->createScreenshot($this->getScreenshotFolder() . '/ArticleCreationTest_AfterSave_' . date('Ymd_His') . '.png');

    $this->assertPageTitle('Massive gaining seo traffic text');
    $this->assertSession()->pageTextContains('Test article');

    $this->assertSession()->pageTextContains('Awesome text');
    $this->assertSession()->pageTextContains('Awesome quote');

    $this->assertSession()
      ->elementExists('xpath', '//div[contains(@class, "field--name-field-paragraphs")]/div[contains(@class, "field__item")][1]//img');
    $this->assertSession()
      ->elementExists('xpath', '//div[contains(@class, "field--name-field-paragraphs")]/div[contains(@class, "field__item")][3]//img');
  }

}
