<?php

namespace Drupal\Tests\thunder\FunctionalJavascript;

use Drupal\Core\Url;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Tests the article creation.
 *
 * @group Thunder
 */
class ArticleCreationTest extends ThunderJavascriptTestBase {

  use ThunderParagraphsTestTrait;
  use ThunderArticleTestTrait;
  use NodeCreationTrait;

  /**
   * Field name for paragraphs in article content.
   *
   * @var string
   */
  protected static $paragraphsField = 'field_paragraphs';

  /**
   * Test Creation of Article.
   */
  public function testCreateArticle() {
    $this->articleFillNew([
      'field_channel' => 1,
      'title[0][value]' => 'Test article',
      'field_seo_title[0][value]' => 'Massive gaining seo traffic text',
    ]);

    $this->selectMedia('field_teaser_media', 'image_browser', ['media:1']);

    // Add Image Paragraph.
    $this->addImageParagraph(static::$paragraphsField, ['media:5']);

    // Add Text Paragraph.
    $this->addTextParagraph(static::$paragraphsField, '<p>Awesome text</p><p>With a new line</p>');

    // Add Gallery Paragraph between Image and Text.
    $this->addGalleryParagraph(static::$paragraphsField, 'Test gallery', [
      'media:1',
      'media:5',
    ], 1);

    // Add Quote Paragraph.
    $this->addTextParagraph(static::$paragraphsField, 'Awesome quote', 'quote');

    // Add Twitter Paragraph between Text and Quote.
    $this->addSocialParagraph(static::$paragraphsField, 'https://twitter.com/ThunderCoreTeam/status/776417570756976640', 'twitter', 3);

    // Add Instagram Paragraph.
    $this->addSocialParagraph(static::$paragraphsField, 'https://www.instagram.com/p/BbywAZBBqlI/', 'instagram');

    // Add Link Paragraph.
    $this->addLinkParagraph(static::$paragraphsField, 'Link to Thunder', 'http://www.thunder.org');

    // Add Video paragraph at the beginning.
    $this->addVideoParagraph(static::$paragraphsField, ['media:7'], 0);

    // Add Pinterest Paragraph.
    $this->addSocialParagraph(static::$paragraphsField, 'https://www.pinterest.de/pin/478085316687452268/', 'pinterest');

    $this->createScreenshot($this->getScreenshotFolder() . '/ArticleCreationTest_BeforeSave_' . date('Ymd_His') . '.png');

    $this->clickSave();

    $this->createScreenshot($this->getScreenshotFolder() . '/ArticleCreationTest_AfterSave_' . date('Ymd_His') . '.png');

    $this->assertPageTitle('Massive gaining seo traffic text');
    $this->assertSession()->pageTextContains('Test article');

    // Check Image paragraph.
    $this->assertSession()
      ->elementsCount('xpath', '//div[contains(@class, "field--name-field-paragraphs")]/div[contains(@class, "field__item")][2]//img', 1);

    // Check Text paragraph.
    $this->assertSession()->pageTextContains('Awesome text');

    // Check Gallery paragraph. Ensure that there are 2 images in gallery.
    $this->assertSession()
      ->elementsCount('xpath', '//div[contains(@class, "field--name-field-paragraphs")]/div[contains(@class, "field__item")][3]//div[contains(@class, "slick-track")]/div[not(contains(@class, "slick-cloned"))]//img', 2);

    // Check Quote paragraph.
    $this->assertSession()->pageTextContains('Awesome quote');

    // Check that one Instagram widget is on page.
    $this->getSession()
      ->wait(5000, "jQuery('iframe').filter(function(){return (this.src.indexOf('instagram.com/p/BbywAZBBqlI') !== -1);}).length === 1");
    $numOfElements = $this->getSession()->evaluateScript("jQuery('iframe').filter(function(){return (this.src.indexOf('instagram.com/p/BbywAZBBqlI') !== -1);}).length");
    $this->assertEquals(1, $numOfElements, "Number of instagrams on page should be one.");

    // Check that one Twitter widget is on page.
    $this->getSession()
      ->wait(5000, "jQuery('twitter-widget').filter(function(){return (this.id.indexOf('twitter-widget-0') !== -1);}).length === 1");
    $numOfElements = $this->getSession()->evaluateScript("jQuery('twitter-widget').filter(function(){return (this.id.indexOf('twitter-widget-0') !== -1);}).length");
    $this->assertEquals(1, $numOfElements, "Number of twitter on page should be one.");

    // Check Link Paragraph.
    $this->assertSession()->linkExists('Link to Thunder');
    $this->assertSession()->linkByHrefExists('http://www.thunder.org');

    // Check for sharing buttons.
    $this->assertSession()->elementExists('css', '.shariff-button.twitter');
    $this->assertSession()->elementExists('css', '.shariff-button.facebook');

    // Check Video paragraph.
    $this->getSession()
      ->wait(5000, "jQuery('iframe').filter(function(){return (this.src.indexOf('media/oembed?url=https%3A//www.youtube.com/watch%3Fv%3DKsp5JVFryEg') !== -1);}).length === 1");
    $numOfElements = $this->getSession()->evaluateScript("jQuery('iframe').filter(function(){return (this.src.indexOf('/media/oembed?url=https%3A//www.youtube.com/watch%3Fv%3DKsp5JVFryEg') !== -1);}).length");
    $this->assertEquals(1, $numOfElements, "Number of youtube on page should be one.");

    // Check that one Pinterest widget is on page.
    $this->assertSession()
      ->elementsCount('xpath', '//div[contains(@class, "field--name-field-paragraphs")]/div[contains(@class, "field__item")][9]//span[contains(@data-pin-id, "478085316687452268")]', 2);
  }

  /**
   * Test Creation of Article without content moderation.
   */
  public function testCreateArticleWithNoModeration() {
    // Delete all the articles so we can disable content moderation.
    foreach (\Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'article']) as $node) {
      $node->delete();
    }
    \Drupal::service('module_installer')->uninstall(['content_moderation']);

    // Try to create an article.
    $this->articleFillNew([
      'field_channel' => 1,
      'title[0][value]' => 'Test article',
      'field_seo_title[0][value]' => 'Massive gaining seo traffic text',
    ]);
    $this->clickSave();
    $this->assertPageTitle('Massive gaining seo traffic text');
    $this->assertSession()->pageTextContains('Test article');
  }

  /**
   * Tests draft creation and that reverting to the default revision works.
   */
  public function testModerationWorkflow() {
    $this->articleFillNew([
      'field_channel' => 1,
      'title[0][value]' => 'Test workflow article',
      'field_seo_title[0][value]' => 'Massive gaining seo traffic text',
    ]);
    $this->setModerationState('published');
    $this->clickSave();
    $this->assertPageTitle('Massive gaining seo traffic text');

    $node = $this->getNodeByTitle('Test workflow article');

    $this->drupalGet($node->toUrl('edit-form'));
    $this->setFieldValues($this->getSession()->getPage(), [
      'title[0][value]' => 'Test workflow article in draft',
      'field_seo_title[0][value]' => 'Massive gaining even more seo traffic text',
    ]);
    $this->setModerationState('draft');
    $this->clickSave();

    $this->drupalGet($node->toUrl('edit-form'));

    $this->setFieldValues($this->getSession()->getPage(), [
      'title[0][value]' => 'Test workflow article in draft 2',
      'field_seo_title[0][value]' => 'Massive gaining even more and more seo traffic text',
    ]);
    $this->setModerationState('draft');
    $this->clickSave();

    $this->assertPageTitle('Massive gaining even more and more seo traffic text');

    /** @var \Drupal\content_moderation\ModerationInformationInterface $moderation_info */
    $moderation_info = \Drupal::service('content_moderation.moderation_information');

    $revert_url = Url::fromRoute('node.revision_revert_default_confirm', [
      'node' => $node->id(),
      'node_revision' => $moderation_info->getLatestRevisionId('node', $node->id()),
    ]);
    $this->drupalPostForm($revert_url, [], $this->t('Revert'));

    $this->drupalGet($node->toUrl());
    $this->assertPageTitle('Massive gaining seo traffic text');

    $this->drupalGet($node->toUrl('edit-form'));
    $this->assertSession()->fieldValueEquals('field_seo_title[0][value]', 'Massive gaining seo traffic text');
  }

}
