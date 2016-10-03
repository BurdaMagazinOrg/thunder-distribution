<?php

namespace Drupal\Tests\thunder\FunctionalJavascript;

/**
 * Trait for handling of Paragraph related test actions.
 *
 * @package Drupal\Tests\thunder\FunctionalJavascript
 */
trait ThunderParagraphsTestTrait {

  /**
   * Counter used to count number of added Paragraphs.
   *
   * @var int $paragraphCount
   */
  protected $paragraphCount;

  /**
   * Add paragraph for field with defined paragraph type.
   *
   * @param string $fieldName
   *   Field name.
   * @param string $type
   *   Type of the paragraph.
   */
  public function addParagraph($fieldName, $type) {

    $page = $this->getSession()->getPage();

    $toggleButtonSelector = '#edit-' . str_replace('_', '-', $fieldName) . '-wrapper .dropbutton-toggle button';
    $toggleButton = $page->find('css', $toggleButtonSelector);
    $this->scrollElementInView($toggleButtonSelector);

    $toggleButton->click();
    $this->assertSession()->assertWaitOnAjaxRequest();

    $addMoreButtonName = "${fieldName}_${type}_add_more";
    $this->scrollElementInView("[name=\"$addMoreButtonName\"]");
    $page->pressButton($addMoreButtonName);
    $this->assertSession()->assertWaitOnAjaxRequest();

    if (!isset($this->paragraphCount[$fieldName])) {
      $this->paragraphCount[$fieldName] = 0;
    }
    else {
      $this->paragraphCount[$fieldName]++;
    }

    $this->waitUntilVisible('div[data-drupal-selector="edit-' . str_replace('_', '-', $fieldName) . '-' . $this->paragraphCount[$fieldName] . '-subform"]');
  }

  /**
   * Add Media paragraph.
   *
   * @param string $fieldName
   *   Field name.
   * @param array $media
   *   List of media identifiers.
   */
  public function addMediaParagraph($fieldName, $media) {

    $this->addParagraph($fieldName, 'media');

    $count = $this->paragraphCount[$fieldName];
    $this->selectMedia("${fieldName}_${count}_subform_field_media", 'media_browser', $media);

  }

  /**
   * Add Gallery paragraph.
   *
   * @param string $fieldName
   *   Field name.
   * @param string $name
   *   Name of the gallery.
   * @param array $media
   *   List of media identifiers.
   */
  public function addGalleryParagraph($fieldName, $name, $media) {

    $this->addParagraph($fieldName, 'gallery');
    $count = $this->paragraphCount[$fieldName];

    $this->openIefComplex("${fieldName}_${count}_subform_field_media");

    $this->createGallery($name, "${fieldName}_${count}_subform_field_media", $media);

  }

  /**
   * Adding text type paragraphs.
   *
   * @param string $fieldName
   *   Field name.
   * @param string $text
   *   Text for paragraph.
   * @param string $type
   *   Type of text paragraph.
   */
  public function addTextParagraph($fieldName, $text, $type = 'text') {

    $this->addParagraph($fieldName, $type);
    $count = $this->paragraphCount[$fieldName];

    $page = $this->getSession()->getPage();

    $ckEditor = $page->find('css', "textarea[name='${fieldName}[${count}][subform][field_text][0][value]']");

    $ckEditorId = $ckEditor->getAttribute('id');

    $this->getSession()
      ->getDriver()
      ->executeScript("CKEDITOR.instances[\"$ckEditorId\"].setData(\"$text\");");
  }

}
