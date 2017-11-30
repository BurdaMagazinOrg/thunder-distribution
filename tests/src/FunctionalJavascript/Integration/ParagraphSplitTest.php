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
   * Filed name for paragraphs in article content.
   *
   * @var string
   */
  protected static $paragraphsField = 'field_paragraphs';

  /**
   * Test split of single paragraph after a selection.
   */
  public function testSingleParagraphSplitAfter() {
    $firstParagraphContent = '<p>Content that will be in the first paragraph after the split.</p>';
    $secondParagraphContent = '<p>Content that will be in the second paragraph after the split.</p>';

    $selectorTemplate = "textarea[name='%s[%d][subform][field_text][0][value]']";

    $this->articleFillNew([]);

    // Add text paragraph with two elements.
    $this->addTextParagraph(static::$paragraphsField, $firstParagraphContent . $secondParagraphContent);

    // Textfield selector template.
    $this->selectCkEditorElement(sprintf($selectorTemplate, static::$paragraphsField, 0), 0);

    // Split text paragraph.
    $this->getSession()->executeScript("jQuery('.cke_button__splittextafter')[0].click();");
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Split after reverts the paragraph counting order.
    $this->assertCkEditorData(sprintf($selectorTemplate, static::$paragraphsField, 1), $firstParagraphContent . PHP_EOL);
    $this->assertCkEditorData(sprintf($selectorTemplate, static::$paragraphsField, 0), $secondParagraphContent . PHP_EOL);

  }

  /**
   * Test split of single paragraph before a selection.
   */
  public function testSingleParagraphSplitBefore() {
    $firstParagraphContent = '<p>Content that will be in the first paragraph after the split.</p>';
    $secondParagraphContent = '<p>Content that will be in the second paragraph after the split.</p>';

    $selectorTemplate = "textarea[name='%s[%d][subform][field_text][0][value]']";

    $this->articleFillNew([]);

    // Add text paragraph with two elements.
    $this->addTextParagraph(static::$paragraphsField, $firstParagraphContent . $secondParagraphContent);

    // Textfield selector template.
    $this->selectCkEditorElement(sprintf($selectorTemplate, static::$paragraphsField, 0), 1);

    // Split text paragraph.
    $this->getSession()->executeScript("jQuery('.cke_button__splittextbefore')[0].click();");
    $this->assertSession()->assertWaitOnAjaxRequest();

    $this->assertCkEditorData(sprintf($selectorTemplate, static::$paragraphsField, 0), $firstParagraphContent . PHP_EOL);
    $this->assertCkEditorData(sprintf($selectorTemplate, static::$paragraphsField, 1), $secondParagraphContent . PHP_EOL);

  }

}
