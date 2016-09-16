<?php

namespace Drupal\Tests\thunder\FunctionalJavascript;

use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\media_entity\Entity\Media;

/**
 * Tests the media modification.
 *
 * @group ThunderOff
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


  public function testOrderChange() {

    $this->drupalGet("node/7/edit");

    $page = $this->getSession()->getPage();

    $page->pressButton('field_paragraphs_0_edit');
    $this->assertSession()->assertWaitOnAjaxRequest();

    $editButton = $page->find('css', 'input[data-drupal-selector="edit-field-paragraphs-0-subform-field-media-entities-0-actions-ief-entity-edit"]');
    $editButton->click();
    $this->assertSession()->assertWaitOnAjaxRequest();

    $selector = "div[data-drupal-selector='edit-field-paragraphs-0-subform-field-media-form-inline-entity-form-entities-0-form-field-media-images-current']";
    $this->getSession()->getDriver()->executeScript('jQuery("' . $selector . ' div[data-entity-id=\'media:8\']").simulateDragSortable({ move: 1 });');

    $secondElement = $page->find('css', $selector . ' > div:nth-child(2)');

    // Not sure why, but without this call, test fails
    $this->getSession()->getScreenshot();

    $this->assertSame('media:8', $secondElement->getAttribute('data-entity-id'));
    $this->getSession()->stop();
  }

}
