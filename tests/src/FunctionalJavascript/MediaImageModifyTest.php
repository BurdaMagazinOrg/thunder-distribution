<?php

namespace Drupal\Tests\thunder\FunctionalJavascript;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\media_entity\Entity\Media;

/**
 * Tests the media modification.
 *
 * @group Thunder
 */
class MediaImageModifyTest extends ThunderJavascriptTestBase {

  public function testFocalPointChange() {

    $page = $this->getSession()->getPage();

    $mediaId = 9;

    $this->drupalGet("media/$mediaId/edit");

    $this->getSession()->getDriver()->executeScript('var e = new jQuery.Event("click"); e.offsetX = 48; e.offsetY = 15; jQuery(".focal-point-wrapper img").trigger(e);');

    $page->pressButton('Save and keep publish');

    $media = Media::load($mediaId);
    $img = $media->get('field_image')->target_id;

    $file = File::load($img);
    $path = $file->getFileUri();

    $derivativeUri = ImageStyle::load('teaser')->buildUri($path);

    ImageStyle::load('teaser')->createDerivative($path,$derivativeUri);

    $image1 = new \Imagick($derivativeUri);
    $image2 = new \Imagick(dirname(__FILE__) . '/../../fixtures/reference.jpg');

    $result = $image1->compareImages($image2, \Imagick::METRIC_MEANSQUAREERROR);

    $this->assertTrue($result[1] < 0.01, 'Images are identical');

    $image1->clear();
    $image2->clear();
    $this->getSession()->stop();
  }

}
