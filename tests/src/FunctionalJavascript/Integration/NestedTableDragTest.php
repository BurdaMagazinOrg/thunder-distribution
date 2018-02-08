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
   * Field name for link field in paragraph content.
   *
   * @var string
   */
  protected static $linkField = 'field_link';

  /**
   * Test tabledrag sorting in nested table.
   */
  public function testNestedTableSorting() {
    $this->articleFillNew([]);

    // Add text paragraph with two elements.
    $this->addTextParagraph(static::$paragraphsField, '<p>Some random text paragraph.</p>');
    $this->addLinkParagraph(static::$paragraphsField, 'Example 11', 'https://example.com/11');
    // Add two link paragraphs with two link fields each.
    $this->addLinkField(static::$paragraphsField, 1, static::$linkField, 1, 'Example 12', 'https://example.com/12');
    $this->addLinkParagraph(static::$paragraphsField, 'Example 21', 'https://example.com/21');
    $this->addLinkField(static::$paragraphsField, 2, static::$linkField, 1, 'Example 22', 'https://example.com/22');

    /* @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    // Enable sorting on second link paragraph.
    $page->find('xpath', '//*[@data-drupal-selector="edit-field-paragraphs-2-subform-field-link-wrapper"]/div/div/table/thead/tr[2]/th/button')->click();

    // Check that related sort buttons are disabled, but not this one.
    $this->assertTrue(
      $page->find('xpath', '//*[@data-drupal-selector="edit-field-paragraphs-wrapper"]/div/div/table/thead/tr[2]/th/button')->hasAttribute('disabled'),
      'Related sort buttons should be disabled.'
    );
    $this->assertTrue(
      $page->find('xpath', '//*[@data-drupal-selector="edit-field-paragraphs-1-subform-field-link-wrapper"]/div/div/table/thead/tr[2]/th/button')->hasAttribute('disabled'),
      'Related sort buttons should be disabled.'
    );
    $this->assertFalse(
      $page->find('xpath', '//*[@data-drupal-selector="edit-field-paragraphs-2-subform-field-link-wrapper"]/div/div/table/thead/tr[2]/th/button')->hasAttribute('disabled'),
      'Active sort button should be enabled.'
    );

    // Check for sort targets outside active table.
    $this->assertFalse(
      $page->find('xpath', '//*[@class="tabledrag-sort-target" and not(ancestor::*[@data-drupal-selector="edit-field-paragraphs-2-subform-field-link-wrapper"])]'),
      'Found tabledrag sort targets outside of active table.'
    );

    // Select and move link field.
    $page->find('xpath', '//*[@data-drupal-selector="edit-field-paragraphs-2-subform-field-link-wrapper"]/div/div/table/tbody/tr[4]/td[1]/input')->click();
    $page->find('xpath', '//*[@data-drupal-selector="edit-field-paragraphs-2-subform-field-link-wrapper"]/div/div/table/tbody/tr[1]/td/a')->click();

    // Check content of field url on certain position.
    $this->assertTrue(
      $page->find('xpath', '//*[@data-drupal-selector="edit-field-paragraphs-2-subform-field-link-wrapper"]/div/div/table/tbody/tr[2]/td[2]/div/div[1]/div[1]/input')->getValue() === 'https://example.com/22',
      'Content of field url on certain position is incorrect.'
    );

    // Disable sorting on second link paragraph.
    $page->find('xpath', '//*[@data-drupal-selector="edit-field-paragraphs-2-subform-field-link-wrapper"]/div/div/table/thead/tr[2]/th/button')->click();

    // Check that all sort buttons are enabled again.
    $message = 'All sort buttons should be enabled again.';
    $this->assertFalse(
      $page->find('xpath', '//*[@data-drupal-selector="edit-field-paragraphs-wrapper"]/div/div/table/thead/tr[2]/th/button')->hasAttribute('disabled'),
      $message
    );
    $this->assertFalse(
      $page->find('xpath', '//*[@data-drupal-selector="edit-field-paragraphs-1-subform-field-link-wrapper"]/div/div/table/thead/tr[2]/th/button')->hasAttribute('disabled'),
      $message
    );
    $this->assertFalse(
      $page->find('xpath', '//*[@data-drupal-selector="edit-field-paragraphs-2-subform-field-link-wrapper"]/div/div/table/thead/tr[2]/th/button')->hasAttribute('disabled'),
      $message
    );

    // Enable sorting on second link paragraph.
    $page->find('xpath', '//*[@data-drupal-selector="edit-field-paragraphs-2-subform-field-link-wrapper"]/div/div/table/thead/tr[2]/th/button')->click();
    $this->assertFalse(
      $page->find('xpath', '//*[@data-drupal-selector="edit-field-paragraphs-2-subform-field-link-wrapper"]/div/div/table/tbody/tr[2]/td[1]/input')->isChecked(),
      'Checkbox is still checked after sort completed.'
    );
  }

  /**
   * Adding link field instance.
   *
   * @param string $paragraphName
   *   Paragraph field name.
   * @param int $paragraphIndex
   *   Paragraph field index.
   * @param string $fieldName
   *   Field name.
   * @param int $fieldIndex
   *   Field index.
   * @param string $urlText
   *   Url text.
   * @param string $url
   *   Url.
   * @param int $position
   *   Position.
   */
  protected function addLinkField($paragraphName, $paragraphIndex, $fieldName, $fieldIndex, $urlText, $url, $position = NULL) {
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    $addButtonName = $paragraphName . "_" . $paragraphIndex . "_subform_" . $fieldName . "_add_more";
    $this->scrollElementInView("[name=\"{$addButtonName}\"]");
    $page->pressButton($addButtonName);
    $this->assertSession()->assertWaitOnAjaxRequest();

    $page->fillField("{$paragraphName}[{$paragraphIndex}][subform][{$fieldName}][{$fieldIndex}][title]", $urlText);
    $page->fillField("{$paragraphName}[{$paragraphIndex}][subform][{$fieldName}][{$fieldIndex}][uri]", $url);
  }

}
