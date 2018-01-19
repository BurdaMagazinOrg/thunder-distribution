<?php

namespace Drupal\Tests\thunder\FunctionalJavascript;

use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\media_entity\Entity\Media;
use Imagick;

/**
 * Tests the Image media modification.
 *
 * @group Thunder
 */
class MediaImageModifyTest extends ThunderJavascriptTestBase {

  /**
   * Test Focal Point change.
   */
  public function testFocalPointChange() {

    // Media ID used for testing.
    $mediaId = 9;

    $this->drupalGet("media/$mediaId/edit");

    $this->createScreenshot($this->getScreenshotFolder() . '/MediaImageModifyTest_BeforeFocalPointChange_' . date('Ymd_His') . '.png');

    $this->getSession()
      ->getDriver()
      ->executeScript('var e = new jQuery.Event("click"); e.offsetX = 48; e.offsetY = 15; jQuery(".focal-point-wrapper img").trigger(e);');

    $this->createScreenshot($this->getScreenshotFolder() . '/MediaImageModifyTest_AfterFocalPointChange_' . date('Ymd_His') . '.png');

    $this->clickSave();

    $media = Media::load($mediaId);
    $img = $media->get('field_image')->target_id;

    $file = File::load($img);
    $path = $file->getFileUri();

    $derivativeUri = ImageStyle::load('teaser')->buildUri($path);

    ImageStyle::load('teaser')->createDerivative($path, $derivativeUri);

    $image1 = new Imagick($derivativeUri);
    $image2 = new Imagick(realpath(dirname(__FILE__) . '/../../fixtures/reference.jpg'));

    $result = $image1->compareImages($image2, \Imagick::METRIC_MEANSQUAREERROR);

    $this->assertTrue($result[1] < 0.01, 'Images are identical');

    $image1->clear();
    $image2->clear();
  }

  /**
   * Test Image modifications (edit fields).
   */
  public function testImageEdit() {
    // Media ID used for testing.
    $mediaId = 9;

    $page = $this->getSession()->getPage();

    $this->drupalGet("media/$mediaId/edit");

    $this->assertSession()->assertWaitOnAjaxRequest();

    $page->fillField('name[0][value]', "Media {$mediaId}");
    $page->fillField('field_image[0][alt]', "Media {$mediaId} Alt Text");
    $page->fillField('field_image[0][title]', "Media {$mediaId} Title");
    $this->setRawFieldValue('field_expires[0][value][date]', '2022-12-18');
    $this->setRawFieldValue('field_expires[0][value][time]', '01:02:03');
    $page->fillField('field_copyright[0][value]', "Media {$mediaId} Copyright");
    $page->fillField('field_source[0][value]', "Media {$mediaId} Source");

    $this->fillCkEditor('#edit-field-description-0-value', "Media {$mediaId} Description");

    $this->createScreenshot($this->getScreenshotFolder() . '/MediaImageModifyTest_BeforeImageEditSave_' . date('Ymd_His') . '.png');

    $this->clickSave();

    // Edit media and check are fields correct.
    $this->drupalGet("media/$mediaId/edit");

    $this->createScreenshot($this->getScreenshotFolder() . '/MediaImageModifyTest_AfterImageEdit_' . date('Ymd_His') . '.png');

    $this->assertSession()
      ->fieldValueEquals('name[0][value]', "Media {$mediaId}");
    $this->assertSession()
      ->fieldValueEquals('field_image[0][alt]', "Media {$mediaId} Alt Text");
    $this->assertSession()
      ->fieldValueEquals('field_image[0][title]', "Media {$mediaId} Title");
    $this->assertSession()
      ->fieldValueEquals('field_expires[0][value][date]', '2022-12-18');
    $this->assertSession()
      ->fieldValueEquals('field_expires[0][value][time]', '01:02:03');
    $this->assertSession()
      ->fieldValueEquals('field_copyright[0][value]', "Media {$mediaId} Copyright");
    $this->assertSession()
      ->fieldValueEquals('field_source[0][value]', "Media {$mediaId} Source");
    $this->assertSession()
      ->fieldValueEquals('field_description[0][value]', "<p>Media {$mediaId} Description</p>");
  }

  /**
   * Click article save option based on index of action.
   *
   * 1 - Save as unpublished (default).
   * 2 - Save and publish.
   *
   * Media entity still using the old collased "Save and publish" button.
   *
   * @param int $actionIndex
   *   Index for option that should be clicked. (by default 1)
   *
   * @TODO: Remove this when switching to core media.
   */
  protected function clickSave($actionIndex = 1) {
    $page = $this->getSession()->getPage();
    if ($actionIndex !== 1) {
      $page->find('xpath', '//ul[@data-drupal-selector="edit-save"]/li[2]/button')
        ->click();
    }
    $page->find('xpath', '(//ul[@data-drupal-selector="edit-save"]/li/input)[' . $actionIndex . ']')
      ->click();
  }

}
