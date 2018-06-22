<?php

namespace Drupal\Tests\thunder\FunctionalJavascript\Integration;

use Drupal\Tests\thunder\FunctionalJavascript\ThunderArticleTestTrait;
use Drupal\Tests\thunder\FunctionalJavascript\ThunderJavascriptTestBase;
use Drupal\Tests\thunder\FunctionalJavascript\ThunderParagraphsTestTrait;

/**
 * Tests the paragraph feature for delete confirmation dialog.
 *
 * @group Thunder
 */
class ParagraphsFeaturesTest extends ThunderJavascriptTestBase {

  use ThunderParagraphsTestTrait;
  use ThunderArticleTestTrait;

  /**
   * Field name for paragraphs in article content.
   *
   * @var string
   */
  protected static $paragraphsField = 'field_paragraphs';

  /**
   * Testing of delete confirmation for paragraphs.
   */
  public function testDeleteConfirmation() {
    $page = $this->getSession()->getPage();

    $this->articleFillNew([
      'field_channel' => 1,
      'title[0][value]' => 'Article 1',
      'field_seo_title[0][value]' => 'Article 1',
    ]);

    // Add text paragraph with two elements.
    $this->addTextParagraph(static::$paragraphsField, 'Test Delete Confirmation to delete text');
    $this->addTextParagraph(static::$paragraphsField, 'Test Delete Confirmation to stay text');
    $this->clickSave();

    $this->drupalGet('node/10/edit');

    $customDeleteButton = $page->find('xpath', '//div[contains(@id, "field-paragraphs-0-item-wrapper")]//button[contains(@class, "paragraph-form-item__action--remove")]');
    $hiddenDeleteButton = $page->find('xpath', '//div[contains(@id, "field-paragraphs-0-item-wrapper")]//button[contains(@class, "paragraph-form-item__action--remove")]');
    $this->assertTrue($customDeleteButton->isVisible(), 'Delete button should be visible and not hidden behind drop-down');
    $this->assertTrue($hiddenDeleteButton->isVisible(), 'Default button from paragraphs should not be visible');
    $this->scrollElementInView('div[id^="field-paragraphs-0-item-wrapper"] button.paragraph-form-item__action--remove');
    $customDeleteButton->click();

    $confirmButton = $page->find('xpath', '//*[contains(@class, "paragraphs-features__delete-confirmation")]//button[contains(@class, "paragraphs-features__delete-confirmation__remove-button")]');
    $confirmButton->click();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertEquals(1, $this->getNumberOfParagraphs(static::$paragraphsField));

    $this->editParagraph($page, 'field_paragraphs', 1);
    $customFormDeleteButton = $page->find('xpath', '//div[contains(@id, "field-paragraphs-1-item-wrapper")]//button[contains(@class, "paragraph-form-item__action--remove")]');
    $hiddenFormDeleteButton = $page->find('xpath', '//div[contains(@id, "field-paragraphs-1-item-wrapper")]//button[contains(@class, "paragraph-form-item__action--remove")]');
    $this->assertTrue($customFormDeleteButton->isVisible(), 'Delete button should be visible');
    $this->assertTrue($hiddenFormDeleteButton->isVisible(), 'Default button from paragraphs should not be visible');
    $this->scrollElementInView('div[id^="field-paragraphs-1-item-wrapper"] button.paragraph-form-item__action--remove');
    $customFormDeleteButton->click();

    $this->assertFalse($customFormDeleteButton->isVisible(), 'Delete button should hidden behind confirmation dialog');
    $cancelButton = $page->find('xpath', '//*[contains(@class, "paragraphs-features__delete-confirmation")]//button[contains(@class, "paragraphs-features__delete-confirmation__cancel-button")]');
    $cancelButton->click();
    $this->assertEquals(1, $this->getNumberOfParagraphs(static::$paragraphsField));
    $this->assertTrue($customFormDeleteButton->isVisible(), 'Delete button should be visible again');
  }

}
