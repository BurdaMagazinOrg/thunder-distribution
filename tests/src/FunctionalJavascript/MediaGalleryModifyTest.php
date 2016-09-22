<?php

namespace Drupal\Tests\thunder\FunctionalJavascript;

/**
 * Tests the Gallery media modification.
 *
 * @group Thunder
 */
class MediaGalleryModifyTest extends ThunderJavascriptTestBase {


  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'thunder_test',
  ];

  /**
   * Test order change for Gallery.
   *
   * @throws \Exception
   */
  public function testOrderChange() {

    $this->drupalGet("node/7/edit");

    $page = $this->getSession()->getPage();

    $this->createScreenshot($this->getScreenshotFolder() . '/MediaGalleryModifyTest_BeforeOrderChange_' . date('Ymd_His') . '.png');

    $this->scrollElementInView('[name="field_paragraphs_0_edit"]');
    $page->pressButton('field_paragraphs_0_edit');

    $this->assertSession()->assertWaitOnAjaxRequest();

    $editButton = $page->find('css', 'input[data-drupal-selector="edit-field-paragraphs-0-subform-field-media-entities-0-actions-ief-entity-edit"]');
    $editButton->click();
    $this->assertSession()->assertWaitOnAjaxRequest();

    $cssSelector = 'div[data-drupal-selector="edit-field-paragraphs-0-subform-field-media-form-inline-entity-form-entities-0-form-field-media-images-current"]';

    $this->scrollElementInView($cssSelector . ' > *:nth-child(2)');
    $this->getSession()
      ->getDriver()
      ->executeScript('jQuery(\'' . $cssSelector . ' div[data-entity-id="media:8"]\').simulate( "drag", { moves: 1, dx: 0, dy: 300 });');

    $this->createScreenshot($this->getScreenshotFolder() . '/MediaGalleryModifyTest_AfterOrderChange_' . date('Ymd_His') . '.png');

    $secondElement = $page->find('xpath', '//div[@data-drupal-selector="edit-field-paragraphs-0-subform-field-media-form-inline-entity-form-entities-0-form-field-media-images-current"]/div[2]');
    if (empty($secondElement)) {
      throw new \Exception('Second element in Gallery is not found');
    }

    $this->assertSame('media:8', $secondElement->getAttribute('data-entity-id'));

    $this->getSession()->stop();
  }

}
