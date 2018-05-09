<?php

namespace Drupal\Tests\thunder\FunctionalJavascript;

use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Exception\ElementNotFoundException;

/**
 * Trait for manipulation of form fields.
 *
 * @package Drupal\Tests\thunder\FunctionalJavascript
 */
trait ThunderFormFieldTestTrait {

  /**
   * Set value for group of checkboxes.
   *
   * Existing selection will be cleared before new values are applied.
   *
   * @param \Behat\Mink\Element\DocumentElement $page
   *   Current active page.
   * @param string $fieldName
   *   Field name.
   * @param string $value
   *   Comma separated values for checkboxes.
   */
  protected function setCheckbox(DocumentElement $page, $fieldName, $value) {
    // UnCheck all checkboxes and check defined.
    $this->getSession()
      ->executeScript("jQuery('input[name*=\"{$fieldName}\"]').prop('checked', false);");

    $checkNames = explode(',', $value);
    foreach ($checkNames as $checkName) {
      $checkBoxName = $fieldName . '[' . trim($checkName) . ']';

      $this->scrollElementInView('[name="' . $checkBoxName . '"]');
      $page->checkField($checkBoxName);
    }
  }

  /**
   * Set value for defined field name on current page.
   *
   * @param \Behat\Mink\Element\DocumentElement $page
   *   Current active page.
   * @param string $fieldName
   *   Field name.
   * @param string $value
   *   Value for field.
   */
  public function setFieldValue(DocumentElement $page, $fieldName, $value) {
    // If field is checkbox list, then use custom functionality to set values.
    // TODO needs documentation.
    $checkboxes = $page->findAll('xpath', "//input[@type=\"checkbox\" and starts-with(@name, \"{$fieldName}[\")]");
    if (!empty($checkboxes)) {
      $this->setCheckbox($page, $fieldName, $value);

      return;
    }

    // If field is date/time field, then set value directly to field.
    $dateTimeFields = $page->findAll('xpath', "//input[(@type=\"date\" or @type=\"time\") and @name=\"{$fieldName}\"]");
    if (!empty($dateTimeFields)) {
      $this->setRawFieldValue($fieldName, $value);

      return;
    }

    // Handle specific types of form fields.
    $field = $page->findField($fieldName);
    $fieldTag = $field->getTagName();
    if ($fieldTag === 'textarea') {
      // Clear text first, before setting value for "textarea" field.
      $this->getSession()
        ->evaluateScript("jQuery('[name=\"{$fieldName}\"]').val('');");
    }
    elseif ($fieldTag === 'select') {
      // Handling of dropdown list.
      if (is_array($value)) {
        foreach ($value as $item) {

          try {
            $page->selectFieldOption($fieldName, $item, TRUE);
          }
          catch (ElementNotFoundException $e) {
            $this->getSession()->evaluateScript("jQuery('[name=\"{$fieldName}\"]').append(new Option(\"{$item}\", \"{$item}\", false, false)).trigger('change')");
          }
        }
        $this->getSession()->evaluateScript("jQuery('[name=\"{$fieldName}\"]').val([" . trim(json_encode(array_values($value)), '[]') . "]).trigger('change')");
      }
      else {
        try {
          $page->selectFieldOption($fieldName, $value, TRUE);
        }
        catch (ElementNotFoundException $e) {
          $this->getSession()->evaluateScript("jQuery('[name=\"{$fieldName}\"]').append(new Option(\"{$value}\", \"{$value}\", false, false)).trigger('change')");
        }

        $this->getSession()->evaluateScript("jQuery('[name=\"{$fieldName}\"]').val([" . trim(json_encode([$value]), '[]') . "]).trigger('change')");
      }

      return;
    }

    $this->scrollElementInView('[name="' . $fieldName . '"]');
    $page->fillField($fieldName, $value);

    $this->assertSession()->assertWaitOnAjaxRequest();
  }

  /**
   * Set fields on current page.
   *
   * @param \Behat\Mink\Element\DocumentElement $page
   *   Current active page.
   * @param array $fieldValues
   *   Field values as associative array with field names as keys.
   */
  public function  setFieldValues(DocumentElement $page, array $fieldValues) {
    foreach ($fieldValues as $fieldName => $value) {
      $this->setFieldValue($page, $fieldName, $value);
    }
  }

}
