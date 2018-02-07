<?php

namespace Drupal\Tests\thunder\FunctionalJavascript\Integration;

use Drupal\Tests\thunder\FunctionalJavascript\ThunderArticleTestTrait;
use Drupal\Tests\thunder\FunctionalJavascript\ThunderJavascriptTestBase;
use Drupal\Tests\thunder\FunctionalJavascript\ThunderParagraphsTestTrait;

/**
 * Tests the tabledrag sorting mechanism in nested tables.
 *
 * @group Thunder
 */
class NestedTableDragTest extends ThunderJavascriptTestBase {

  use ThunderParagraphsTestTrait;
  use ThunderArticleTestTrait;

  /**
   * Field name for paragraphs in article content.
   *
   * @var string
   */
  protected static $paragraphsField = 'field_paragraphs';

  /**
   * Test split of paragraph before a selection.
   */
  public function testNestedTableSorting() {
    $this->articleFillNew([]);

    // Add text paragraph with two elements.
    $this->addTextParagraph(static::$paragraphsField, '<p>Some random text paragraph.</p>');
    $this->addLinkParagraph(static::$paragraphsField, 'Example 11', 'https://example.com/11');
    // Add two link paragraphs with two link fields each
    $this->addLinkField(static::$paragraphsField, "1", 'Example 12', 'https://example.com/12');
    $this->addLinkParagraph(static::$paragraphsField, 'Example 21', 'https://example.com/21');
    $this->addLinkField(static::$paragraphsField, "2", 'Example 22', 'https://example.com/22');

    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    // Enable sorting on second link paragraph.
    $page->find('xpath', '//*[@data-drupal-selector="edit-field-paragraphs-2-subform-field-link-wrapper"]/div/div/table/thead/tr[2]/th/button')->click();
    // Check that related sort buttons are disabled, but not this one.
    $this->assertTrue($page->find('xpath', '//*[@data-drupal-selector="edit-field-paragraphs-wrapper"]/div/div/table/thead/tr[2]/th/button')->hasAttribute('disabled'));
    $this->assertTrue($page->find('xpath', '//*[@data-drupal-selector="edit-field-paragraphs-1-subform-field-link-wrapper"]/div/div/table/thead/tr[2]/th/button')->hasAttribute('disabled'));
    $this->assertFalse($page->find('xpath', '//*[@data-drupal-selector="edit-field-paragraphs-2-subform-field-link-wrapper"]/div/div/table/thead/tr[2]/th/button')->hasAttribute('disabled'));

    // Select and move link field.
    $page->find('xpath', '//*[@data-drupal-selector="edit-field-paragraphs-2-subform-field-link-wrapper"]/div/div/table/tbody/tr[4]/td[1]/input')->click();
    $page->find('xpath', '//*[@data-drupal-selector="edit-field-paragraphs-2-subform-field-link-wrapper"]/div/div/table/tbody/tr[1]/td/a')->click();
    // Check content of field url on certain position.
    $this->assertTrue($page->find('xpath', '//*[@data-drupal-selector="edit-field-paragraphs-2-subform-field-link-wrapper"]/div/div/table/tbody/tr[2]/td[2]/div/div[1]/div[1]/input')->getValue() === 'https://example.com/22');

    $page->find
    // Disable sorting on second link paragraph.
    $page->find('xpath', '//*[@data-drupal-selector="edit-field-paragraphs-2-subform-field-link-wrapper"]/div/div/table/thead/tr[2]/th/button')->click();
    // Check that all sort buttons are enabled again.
    $this->assertFalse($page->find('xpath', '//*[@data-drupal-selector="edit-field-paragraphs-wrapper"]/div/div/table/thead/tr[2]/th/button')->hasAttribute('disabled'));
    $this->assertFalse($page->find('xpath', '//*[@data-drupal-selector="edit-field-paragraphs-1-subform-field-link-wrapper"]/div/div/table/thead/tr[2]/th/button')->hasAttribute('disabled'));
    $this->assertFalse($page->find('xpath', '//*[@data-drupal-selector="edit-field-paragraphs-2-subform-field-link-wrapper"]/div/div/table/thead/tr[2]/th/button')->hasAttribute('disabled'));
  }


  /**
   * Adding link field instance.
   *
   * @param string $fieldName
   * @param int $fieldIndex
   * @param string $urlText
   * @param string $url
   * @param int $position
   */
  function addLinkField($fieldName, $fieldIndex,  $urlText, $url, $position = NULL) {

    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    $addButtonName =  $fieldName ."_". $fieldIndex ."_subform_field_link_add_more";
    $this->scrollElementInView("[name=\"{$addButtonName}\"]");
    $page->pressButton($addButtonName);
    $this->assertSession()->assertWaitOnAjaxRequest();

    $page->fillField("{$fieldName}[{$fieldIndex}][subform][field_link][1][title]", $urlText);
    $page->fillField("{$fieldName}[{$fieldIndex}][subform][field_link][1][uri]", $url);
  }
}
