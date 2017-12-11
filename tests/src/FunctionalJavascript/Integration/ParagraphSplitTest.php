<?php

namespace Drupal\Tests\thunder\FunctionalJavascript\Integration;

use Drupal\Tests\thunder\FunctionalJavascript\ThunderArticleTestTrait;
use Drupal\Tests\thunder\FunctionalJavascript\ThunderJavascriptTestBase;
use Drupal\Tests\thunder\FunctionalJavascript\ThunderParagraphsTestTrait;

/**
 * Tests the paragraph split module integration.
 *
 * @group Thunder
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
   * To use it, you have to provide a string containing the paragraphs field
   * name and the delta of the paragraph.
   *
   * @var string
   */
  protected static $selectorTemplate = "textarea[name='%s[%d][subform][field_text][0][value]']";

  /**
   * Test split of paragraph after a selection.
   */
  public function testParagraphSplitAfter() {
    $firstParagraphContent = '<p>Content that will be in the first paragraph after the split.</p>';
    $secondParagraphContent = '<p>Content that will be in the second paragraph after the split.</p>';

    $this->articleFillNew([]);

    // Add text paragraph with two elements.
    $this->addTextParagraph(static::$paragraphsField, $firstParagraphContent . $secondParagraphContent);

    // Select first element in editor.
    $this->selectCkEditorElement($this->getCkEditorCssSelector(0), 0);

    // Split text paragraph after the current selection.
    $this->clickParagraphSplitButton('after');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Test if all texts are in the correct paragraph.
    // When splitting after the current position, the new paragraph will
    // be in front of the old, that is why the paragraph delta is reversed.
    $this->assertCkEditorContent($this->getCkEditorCssSelector(1), $firstParagraphContent . PHP_EOL);
    $this->assertCkEditorContent($this->getCkEditorCssSelector(0), $secondParagraphContent . PHP_EOL);
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

    // Select second element in editor.
    $this->selectCkEditorElement($this->getCkEditorCssSelector(0), 1);

    // Split text paragraph before the current selection.
    $this->clickParagraphSplitButton('before');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Test if all texts are in the correct paragraph.
    $this->assertCkEditorContent($this->getCkEditorCssSelector(0), $firstParagraphContent . PHP_EOL);
    $this->assertCkEditorContent($this->getCkEditorCssSelector(1), $secondParagraphContent . PHP_EOL);
  }

  /**
   * Test if a deleted paragraph leads to data loss.
   */
  public function testParagraphSplitDataLoss() {
    $firstParagraphContent = '<p>Content that will be in the first paragraph after the split.</p>';
    $secondParagraphContent = '<p>Content that will be in the second paragraph after the split.</p>';

    $this->articleFillNew([]);

    // Create first paragraph.
    $this->addTextParagraph(static::$paragraphsField, '');

    // Remove the paragraph.
    $page = $this->getSession()->getPage();
    $this->clickButtonCssSelector($page, '[name="field_paragraphs_0_remove"]');
    $this->clickButtonCssSelector($page, '[name="field_paragraphs_0_confirm_remove"]');

    // Create second paragraph.
    $this->addTextParagraph(static::$paragraphsField, $firstParagraphContent . $secondParagraphContent);

    // Select second element in editor.
    $this->selectCkEditorElement($this->getCkEditorCssSelector(1), 1);

    // Split text paragraph.
    $this->clickParagraphSplitButton('before');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Test if all texts are in the correct paragraph.
    $this->assertCkEditorContent($this->getCkEditorCssSelector(1), $firstParagraphContent . PHP_EOL);
    $this->assertCkEditorContent($this->getCkEditorCssSelector(2), $secondParagraphContent . PHP_EOL);
  }

  /**
   * Test if a adding paragraph after split leads to data loss.
   */
  public function testAddParagraphAfterSplitDataLoss() {
    $firstParagraphContent = '<p>Content that will be in the first paragraph after the split.</p>';
    $secondParagraphContent = '<p>Content that will be in the second paragraph after the split.</p>';
    $thirdParagraphContent = '<p>Content that will be placed into the first paragraph after split.</p>';

    $this->articleFillNew([]);

    // Create first paragraph.
    $this->addTextParagraph(static::$paragraphsField, $firstParagraphContent . $secondParagraphContent);

    // Select second element in editor.
    $this->selectCkEditorElement($this->getCkEditorCssSelector(0), 1);

    // Split text paragraph.
    $this->clickParagraphSplitButton('before');
    $this->assertSession()->assertWaitOnAjaxRequest();

    $paragraphDelta = $this->getParagraphDelta(static::$paragraphsField, 0);
    $ckEditorCssSelector = "textarea[name='" . static::$paragraphsField . "[{$paragraphDelta}][subform][field_text][0][value]']";

    $this->fillCkEditor(
      $ckEditorCssSelector,
      $thirdParagraphContent
    );

    $ckEditorId = $this->getCkEditorId($ckEditorCssSelector);
    $this->getSession()
      ->getDriver()
      ->executeScript("CKEDITOR.instances[\"$ckEditorId\"].setData(\"$thirdParagraphContent\");");

    $this->addTextParagraph(static::$paragraphsField, '', 'text', 1);

    // Test if all texts are in the correct paragraph.
    $this->assertCkEditorContent($this->getCkEditorCssSelector(0), $thirdParagraphContent . PHP_EOL);
    $this->assertCkEditorContent($this->getCkEditorCssSelector(1), '' . PHP_EOL);
    $this->assertCkEditorContent($this->getCkEditorCssSelector(2), $secondParagraphContent . PHP_EOL);
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

  /**
   * Create css selector for paragraph with the given delta.
   *
   * @param int $paragraphDelta
   *   The delta of the paragraph.
   *
   * @return string
   *   Css selector for the paragraph.
   */
  protected function getCkEditorCssSelector($paragraphDelta) {
    return sprintf(static::$selectorTemplate, static::$paragraphsField, $paragraphDelta);
  }

}
