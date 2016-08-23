<?php

namespace Drupal\Tests\thunder\FunctionalJavascript;


trait ThunderParagraphsTestTrait {

  protected $paragraphCount;

  public function addParagraph($fieldName, $type) {

    $page = $this->getSession()->getPage();

    $toggleButton = $page->find('css', '#edit-' . str_replace('_', '-', $fieldName) . '-wrapper .dropbutton-toggle button');
    $toggleButton->click();
    $this->assertSession()->assertWaitOnAjaxRequest();

    $page->pressButton("${fieldName}_${type}_add_more");
    $this->assertSession()->assertWaitOnAjaxRequest();

    if (!isset($this->paragraphCount[$fieldName])) {
      $this->paragraphCount[$fieldName] = 0;
    } else {
      $this->paragraphCount[$fieldName]++;
    }

    $this->waitUntilVisible('div[data-drupal-selector="edit-' . str_replace('_', '-', $fieldName) . '-' . $this->paragraphCount[$fieldName] . '-subform"]');

  }

}
