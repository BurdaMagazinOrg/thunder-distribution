<?php

namespace Drupal\Tests\thunder\FunctionalJavascript\Integration;

use Drupal\Tests\thunder\FunctionalJavascript\ThunderArticleTestTrait;
use Drupal\Tests\thunder\FunctionalJavascript\ThunderJavascriptTestBase;
use Drupal\Tests\thunder\FunctionalJavascript\ThunderParagraphsTestTrait;

/**
 * Tests the riddle integration.
 *
 * @group Thunder
 */
class RiddleTest extends ThunderJavascriptTestBase {

  use ThunderArticleTestTrait;
  use ThunderParagraphsTestTrait;

  /**
   * Testing integration of "thunder_riddle" module.
   */
  public function testRiddle() {
    $riddleToken = getenv('RIDDLE_TOKEN');

    if (empty($riddleToken)) {
      if ($this->isForkPullRequest()) {
        $this->markTestSkipped("Skip Riddle test (missing secure environment variables)");

        return;
      }

      $this->fail("Riddle token is not available.");

      return;
    }

    if (!\Drupal::service('module_installer')->install(['thunder_riddle'])) {
      $this->fail("Unable to install Thunder Riddle integration module.");

      return;
    }

    $this->logWithRole('administrator');

    // Adjust settings for Riddle.
    $this->drupalGet('admin/config/content/riddle_marketplace');
    $page = $this->getSession()->getPage();
    $this->setFieldValues($page, [
      'token' => $riddleToken,
    ]);
    $this->clickButtonDrupalSelector($page, 'edit-submit');

    // Log as editor user.
    $this->logWithRole(static::$defaultUserRole);

    // Fill article form with base fields.
    $this->articleFillNew([
      'field_channel' => 1,
      'title[0][value]' => 'Article 1',
      'field_seo_title[0][value]' => 'Article 1',
    ]);

    // Check loading of Riddles from riddle.com and creation of Riddle media.
    $paragraphIndex = $this->addParagraph('field_paragraphs', 'riddle');

    $buttonName = "field_paragraphs_{$paragraphIndex}_subform_field_riddle_entity_browser_entity_browser";
    $this->scrollElementInView("[name=\"{$buttonName}\"]");
    $page->pressButton($buttonName);
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()->switchToIFrame('entity_browser_iframe_riddle_browser');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Click button to load Riddles and compare thumbnails.
    $this->clickButtonDrupalSelector($page, 'edit-import-riddle');
    $this->assertNotEmpty($this->assertSession()->waitForElementVisible('css', '.view-media-entity-browser .views-field-thumbnail__target-id img'));
    $this->assertTrue(
      $this->compareScreenToImage(
        $this->getScreenshotFile('test_riddle_eb_list'),
        ['width' => 600, 'height' => 380, 'x' => 60, 'y' => 115]
      )
    );

    // Close entity browser.
    $this->getSession()->switchToIFrame();
    $page->find('xpath', '//*[contains(@class, "ui-dialog-titlebar-close")]')->click();
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Select first riddle.
    $this->selectMedia("field_paragraphs_{$paragraphIndex}_subform_field_riddle", 'riddle_browser', ['media:24']);

    // Select second riddle.
    $paragraphIndex = $this->addParagraph('field_paragraphs', 'riddle');
    $this->selectMedia("field_paragraphs_{$paragraphIndex}_subform_field_riddle", 'riddle_browser', ['media:25']);

    // Save article as unpublished.
    $this->clickSave();

    // Assert that riddle iframes are correctly generated.
    $this->drupalGet('node/10');

    $this->assertSession()
      ->elementExists('xpath', '//div[contains(@class, "field--name-field-paragraphs")]/div[contains(@class, "field__item")][1]//iframe[contains(@src, "https://www.riddle.com/a/114979")]');
    $this->assertSession()
      ->elementExists('xpath', '//div[contains(@class, "field--name-field-paragraphs")]/div[contains(@class, "field__item")][2]//iframe[contains(@src, "https://www.riddle.com/a/114982")]');
  }

}
