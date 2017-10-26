<?php

namespace Drupal\thunder_media\Tests;

use Drupal\thunder\ThunderBaseTest;

/**
 * Tests for transliteration of file names.
 *
 * @group Thunder
 */
class FileRemoveButtonTest extends ThunderBaseTest {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['thunder_demo'];

  /**
   * Test for transliteration of file name.
   */
  public function testRemoveButtonGone() {

    $this->logWithRole('editor');

    $this->drupalGet('media/19/edit');
    $this->assertSession()->elementNotExists('css', '#edit-field-image-0-remove-button');

    $this->config('thunder_media.settings')
      ->set('enable_filefield_remove_button', TRUE)
      ->save();

    $this->drupalGet('media/19/edit');
    $this->assertSession()->elementExists('css', '#edit-field-image-0-remove-button');

  }

}
