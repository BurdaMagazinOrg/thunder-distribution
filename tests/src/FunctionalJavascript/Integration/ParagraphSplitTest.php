<?php

namespace Drupal\Tests\thunder\FunctionalJavascript\Integration;

use Drupal\Tests\thunder\FunctionalJavascript\ThunderArticleTestTrait;
use Drupal\Tests\thunder\FunctionalJavascript\ThunderJavascriptTestBase;
use Drupal\Tests\thunder\FunctionalJavascript\ThunderParagraphsTestTrait;

/**
 * Tests the paragraph split module integration.
 *
 * @group _Thunder
 */
class ParagraphSplitTest extends ThunderJavascriptTestBase {

  use ThunderParagraphsTestTrait;
  use ThunderArticleTestTrait;

  /**
   * Field name for paragraphs in article content.
   *
   * @var string
   */
  protected static $paragraphsField = 'field_paragraphs';

  /**
   * Selector template for CKEditor instances.
   *
   * To use it, you have to provide a string containing the paragraps field
   * name and the number of the paragraph.
   *
   * @var string
   */
  protected static $selectorTemplate = "textarea[name='%s[%d][subform][field_text][0][value]']";

  /**
   * Test if a deleted paragraph leads to data loss.
   */
  public function testParagraphSplitDataLoss() {
    $firstParagraphContent = '<p>Content that will be in the first paragraph after the split.</p>';
    $secondParagraphContent = '<p>Content that will be in the second paragraph after the split.</p>';

    $this->articleFillNew([]);
    $this->addTextParagraph(static::$paragraphsField, '');

    $page = $this->getSession()->getPage();
    $this->clickButtonCssSelector($page, '[name="field_paragraphs_0_remove"]');
    $this->clickButtonCssSelector($page, '[name="field_paragraphs_0_confirm_remove"]');

    $this->addTextParagraph(static::$paragraphsField, $firstParagraphContent . $secondParagraphContent);

    // Textfield selector template.
    $this->selectCkEditorElement(sprintf(static::$selectorTemplate, static::$paragraphsField, 0), 0);

    // Split text paragraph.
    $this->clickParagraphSplitButton('after');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Split after reverts the paragraph counting order.
    #$this->assertCkEditorContent(sprintf(static::$selectorTemplate, static::$paragraphsField, 1), $firstParagraphContent . PHP_EOL);
    #$this->assertCkEditorContent(sprintf(static::$selectorTemplate, static::$paragraphsField, 0), $secondParagraphContent . PHP_EOL);
  }

  /**
   * Test split of paragraph after a selection.
   */
  public function testParagraphSplitAfter() {
    $firstParagraphContent = '<p>Content that will be in the first paragraph after the split.</p>';
    $secondParagraphContent = '<p>Content that will be in the second paragraph after the split.</p>';

    $this->articleFillNew([]);

    // Add text paragraph with two elements.
    $this->addTextParagraph(static::$paragraphsField, $firstParagraphContent . $secondParagraphContent);

    // Textfield selector template.
    $this->selectCkEditorElement(sprintf(static::$selectorTemplate, static::$paragraphsField, 0), 0);

    // Split text paragraph.
    $this->clickParagraphSplitButton('after');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Split after reverts the paragraph counting order.
    $this->assertCkEditorContent(sprintf(static::$selectorTemplate, static::$paragraphsField, 1), $firstParagraphContent . PHP_EOL);
    $this->assertCkEditorContent(sprintf(static::$selectorTemplate, static::$paragraphsField, 0), $secondParagraphContent . PHP_EOL);

  }

  /**
   * Test split of paragraph before a selection.
   */
  public function testParagraphSplitBefore() {
    $firstParagraphContent = '<p>Content that will be in the first paragraph after the split.</p>';
    $secondParagraphContent = '<p>Content that will be in the second paragraph after the split.</p>';

    $this->articleFillNew([]);

    // Add text paragraph with two elements.
    $this->addTextParagraph(static::$paragraphsField, $firstParagraphContent . $secondParagraphContent);

    // Textfield selector template.
    $this->selectCkEditorElement(sprintf(static::$selectorTemplate, static::$paragraphsField, 0), 1);

    // Split text paragraph.
    $this->clickParagraphSplitButton('before');
    $this->getSession()->executeScript("jQuery('.cke_button__splittextbefore')[0].click();");
    $this->assertSession()->assertWaitOnAjaxRequest();

    $this->assertCkEditorContent(sprintf(static::$selectorTemplate, static::$paragraphsField, 0), $firstParagraphContent . PHP_EOL);
    $this->assertCkEditorContent(sprintf(static::$selectorTemplate, static::$paragraphsField, 1), $secondParagraphContent . PHP_EOL);

  }

  /**
   * Click on split button.
   *
   * @param string $type
   *   The button type to click. Can be 'before' or 'after'.
   */
  protected function clickParagraphSplitButton($type) {
    $this->getSession()->executeScript("jQuery('.cke_button__splittext{$type}')[0].click();");
  }
}
