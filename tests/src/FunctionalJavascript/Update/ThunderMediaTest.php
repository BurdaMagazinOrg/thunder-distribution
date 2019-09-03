<?php

namespace Drupal\Tests\thunder\FunctionalJavascript\Update;

use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\file\Entity\File;
use Drupal\media_entity\Entity\Media;
use Drupal\Tests\thunder\FunctionalJavascript\ThunderArticleTestTrait;
use Drupal\Tests\thunder\FunctionalJavascript\ThunderJavascriptTestBase;
use Drupal\Tests\thunder\FunctionalJavascript\ThunderParagraphsTestTrait;

/**
 * Test for update hook changes.
 *
 * @group Thunder
 *
 * @package Drupal\Tests\thunder\FunctionalJavascript\Update
 */
class ThunderMediaTest extends ThunderJavascriptTestBase {

  use ThunderArticleTestTrait;
  use ThunderParagraphsTestTrait;

  /**
   * Test that entity browsers does not have language filters anymore.
   */
  public function test8101() {
    // Open article creation page but without setting any element in form.
    $this->articleFillNew([]);

    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    // Open teaser entity browser.
    $buttonName = 'field_teaser_media_entity_browser_entity_browser';
    $this->scrollElementInView("[name=\"{$buttonName}\"]");
    $page->pressButton($buttonName);
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()->switchToIFrame('entity_browser_iframe_image_browser');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Check that status and name filtering fields exist, but not langcode.
    $this->assertSession()
      ->elementNotExists('xpath', '//*[@data-drupal-selector="edit-langcode"]');
    $this->assertSession()
      ->elementExists('xpath', '//*[@data-drupal-selector="edit-status"]');
    $this->assertSession()
      ->elementExists('xpath', '//*[@data-drupal-selector="edit-name"]');

    // Close entity browser.
    $this->getSession()->switchToIFrame();
    $page->find('xpath', '//*[contains(@class, "ui-dialog-titlebar-close")]')
      ->click();
    $this->assertSession()->assertWaitOnAjaxRequest();
  }

  /**
   * Test that Video and Image entity browser uses 24 images per page.
   */
  public function test8104() {
    \Drupal::service('file_system')->copy(realpath(dirname(__FILE__) . '/../../../fixtures/thunder.jpg'), PublicStream::basePath() . '/testing_thunder.jpg');
    $file = File::create([
      'uri' => 'public://testing_thunder.jpg',
      'status' => FILE_STATUS_PERMANENT,
    ]);
    $file->save();

    $videoUrl = 'https://www.youtube.com/watch?v=cnT8dycXnMU';

    for ($i = 0; $i < 50; $i++) {
      $mediaImage = Media::create([
        'name' => 'Test Image Id ' . $i,
        'field_image' => $file,
        'bundle' => 'image',
        'status' => Media::PUBLISHED,
      ]);
      $mediaImage->save();

      $mediaVideo = Media::create([
        'name' => 'Test Video Id ' . $i,
        'field_media_video_embed_field' => $videoUrl,
        'bundle' => 'video',
        'status' => Media::PUBLISHED,
      ]);
      $mediaVideo->save();
    }

    // Open article creation page but without setting any element in form.
    $this->articleFillNew([]);

    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    // Open teaser entity browser.
    $buttonName = 'field_teaser_media_entity_browser_entity_browser';
    $this->scrollElementInView("[name=\"{$buttonName}\"]");
    $page->pressButton($buttonName);
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()
      ->switchToIFrame('entity_browser_iframe_image_browser');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Ensure there are 24 elements and 3 pages.
    /** @var \Behat\Mink\Element\NodeElement[] $imageElements */
    $imageElements = $page->findAll('xpath', '//*[@id="entity-browser-image-browser-form"]//span/img[contains(@class, "image-style-entity-browser-thumbnail")]');
    $this->assertEquals(24, count($imageElements));

    $pagerPageElements = $page->findAll('xpath', '//*[@id="entity-browser-image-browser-form"]//nav/ul/li[@class="pager__item" or @class="pager__item is-active"]');
    $this->assertEquals(3, count($pagerPageElements));

    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test8104_1')));
    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test8104_1_1350x768'), [], ['width' => 1350, 'height' => 768]));
    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test8104_1_768x768'), [], ['width' => 768, 'height' => 768]));
    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test8104_1_440x768'), [], ['width' => 440, 'height' => 768]));

    // Close entity browser.
    $this->getSession()->switchToIFrame();
    $page->find('xpath', '//*[contains(@class, "ui-dialog-titlebar-close")]')
      ->click();
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Create Video paragraph.
    $paragraphIndex = $this->addParagraph('field_paragraphs', 'video');

    $buttonName = "field_paragraphs_{$paragraphIndex}_subform_field_video_entity_browser_entity_browser";
    $this->scrollElementInView("[name=\"{$buttonName}\"]");
    $page->pressButton($buttonName);
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()
      ->switchToIFrame('entity_browser_iframe_video_browser');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Ensure there are 24 elements and 3 pages.
    /** @var \Behat\Mink\Element\NodeElement[] $imageElements */
    $imageElements = $page->findAll('xpath', '//*[@id="entity-browser-video-browser-form"]//span/img[contains(@class, "image-style-entity-browser-thumbnail")]');
    $this->assertEquals(24, count($imageElements));

    $pagerPageElements = $page->findAll('xpath', '//*[@id="entity-browser-video-browser-form"]//nav/ul/li[@class="pager__item" or @class="pager__item is-active"]');
    $this->assertEquals(3, count($pagerPageElements));

    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test8104_2')));
    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test8104_2_1350x768'), [], ['width' => 1350, 'height' => 768]));
    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test8104_2_768x768'), [], ['width' => 768, 'height' => 768]));
    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test8104_2_440x768'), [], ['width' => 440, 'height' => 768]));

    // Close entity browser.
    $this->getSession()->switchToIFrame();
    $page->find('xpath', '//*[contains(@class, "ui-dialog-titlebar-close")]')
      ->click();
    $this->assertSession()->assertWaitOnAjaxRequest();
  }

  /**
   * Test autoplay of videos.
   */
  public function test8111() {

    $this->drupalGet('node/6');
    $this->assertSession()->elementAttributeContains('css', '.video-embed-field-responsive-video iframe', 'src', 'autoplay=0');
    $this->drupalLogout();

    $this->drupalGet('node/6');
    $this->assertSession()->elementAttributeContains('css', '.video-embed-field-responsive-video iframe', 'src', 'autoplay=1');

    $this->logWithRole('seo');
    $this->drupalGet('node/6');
    $this->assertSession()->elementAttributeContains('css', '.video-embed-field-responsive-video iframe', 'src', 'autoplay=0');
  }

}
