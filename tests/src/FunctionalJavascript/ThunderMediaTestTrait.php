<?php

namespace Drupal\Tests\thunder\FunctionalJavascript;

/**
 * Trait for handling of Media related test actions.
 *
 * @package Drupal\Tests\thunder\FunctionalJavascript
 */
trait ThunderMediaTestTrait {

  /**
   * Select Medias for field.
   *
   * @param string $fieldName
   *   Field name.
   * @param string $entityBrowser
   *   Entity browser identifier.
   * @param array $medias
   *   List of media identifiers.
   */
  public function selectMedia($fieldName, $entityBrowser, $medias) {

    $classNameParts = explode('\\', __CLASS__);
    $className = array_pop($classNameParts);

    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->createScreenshot($this->getScreenshotFolder() . '/' . $className . '_TM1_' . date('Ymd_His') . '.png');

    $buttonName = $fieldName . '_entity_browser_entity_browser';
    $this->scrollElementInView("[name=\"{$buttonName}\"]");
    $page->pressButton($buttonName);

    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->createScreenshot($this->getScreenshotFolder() . '/' . $className . '_TM2_' . date('Ymd_His') . '.png');

    $this->getSession()
      ->switchToIFrame('entity_browser_iframe_' . $entityBrowser);
    $this->assertSession()->assertWaitOnAjaxRequest();

    foreach ($medias as $media) {
      $this->getSession()
        ->executeScript("jQuery('[name=\"entity_browser_select[$media]\"]').prop('checked', true);");
    }

    $page->pressButton('Select entities');

    if ($entityBrowser == 'multiple_image_browser') {
      $page->pressButton('Use selected');
    }

    $this->getSession()->switchToIFrame();
    $this->assertSession()->assertWaitOnAjaxRequest();

    $this->waitUntilVisible('div[data-drupal-selector="edit-' . str_replace('_', '-', $fieldName) . '-wrapper"] img');
  }

  /**
   * Create gallery with selected medias.
   *
   * @param string $name
   *   Name of gallery.
   * @param string $fieldName
   *   Field name.
   * @param string $medias
   *   List of media identifiers.
   */
  public function createGallery($name, $fieldName, $medias) {

    $page = $this->getSession()->getPage();

    $selector = "input[data-drupal-selector='edit-" . str_replace('_', '-', $fieldName) . "-form-inline-entity-form-name-0-value']";
    $this->assertSession()->elementExists('css', $selector);

    $nameField = $page->find('css', $selector);
    $nameField->setValue($name);

    $this->selectMedia("${fieldName}_form_inline_entity_form_field_media_images", 'multiple_image_browser', $medias);
  }

}
