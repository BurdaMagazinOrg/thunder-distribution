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
   * Filed name for paragraphs in article content.
   *
   * @var string
   */
  protected static $paragraphsField = 'field_paragraphs';

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

    // Add Media Image Paragraph.
    $this->addMediaParagraph(static::$paragraphsField, ['media:5']);

    // Add Text Paragraph.
    $this->addTextParagraph(static::$paragraphsField, 'Awesome text');

    // Add Gallery Paragraph.
    $this->addGalleryParagraph(static::$paragraphsField, 'Test gallery', [
      'media:1',
      'media:5',
    ]);

    // Add Quote Paragraph.
    $this->addTextParagraph(static::$paragraphsField, 'Awesome quote', 'quote');

    // Add Twitter Paragraph.
    $this->addTwitterParagraph(static::$paragraphsField, 'Thunder Tweet', 'https://twitter.com/ThunderCoreTeam/status/776417570756976640', 'twitter');

    // Add Instagram Paragraph.
    $this->addTwitterParagraph(static::$paragraphsField, 'DrupalCon2016 Instagram', 'https://www.instagram.com/p/BK3VVUtAuJ3/', 'instagram');

    // Add Link Paragraph.
    $this->addLinkParagraph(static::$paragraphsField, 'Link to Thunder', 'http://www.thunder.org');

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

    // Check that one Instagram widget is on page.
    $this->getSession()
      ->wait(5000, "jQuery('iframe').filter(function(){return (this.src.indexOf('instagram.com/p/BK3VVUtAuJ3') !== -1);}).length === 1");

    // Check that one Twitter widget is on page.
    $this->getSession()
      ->wait(5000, "jQuery('iframe').filter(function(){return (this.id.indexOf('twitter-widget-0') !== -1);}).length === 1");

    // Check Link Paragraph.
    $this->assertSession()->linkExists('Link to Thunder');
    $this->assertSession()->linkByHrefExists('http://www.thunder.org');
  }

}
