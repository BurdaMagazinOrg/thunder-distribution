<?php

namespace Drupal\Tests\thunder\FunctionalJavascript;


trait ThunderMediaTestTrait {

  public function selectMedia($fieldName, $entityBrowser, $media) {

    $page = $this->getSession()->getPage();

    $selectButton = $page->find('css', 'input[data-drupal-selector="edit-' . str_replace('_', '-', $fieldName) . '-entity-browser-entity-browser-open-modal"]');
    $selectButton->click();

    $this->getSession()
      ->switchToIFrame('entity_browser_iframe_' . $entityBrowser);
    $this->assertSession()->assertWaitOnAjaxRequest();

    $page->checkField("entity_browser_select[$media]");

    $page->pressButton('Select entities');

    $this->getSession()->switchToIFrame();
    $this->assertSession()->assertWaitOnAjaxRequest();


    $this->waitUntilVisible('div[data-drupal-selector="edit-' . str_replace('_', '-', $fieldName) . '-wrapper"] img');
  }
}
