<?php

namespace Drupal\Tests\thunder\FunctionalJavascript;

trait ThunderParagraphsTestTrait {

  protected $paragraphCount;

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

  public function addMediaParagraph($fieldName, $media) {

    $this->addParagraph($fieldName, 'media');

    $count = $this->paragraphCount[$fieldName];
    $this->selectMedia("${fieldName}_${count}_subform_field_media", 'media_browser', $media);

  }


  public function addGalleryParagraph($fieldName, $name, $media) {

    $this->addParagraph($fieldName, 'gallery');
    $count = $this->paragraphCount[$fieldName];

    $this->openIefComplex("${fieldName}_${count}_subform_field_media");

    $this->createGallery($name, "${fieldName}_${count}_subform_field_media", $media);

  }

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
