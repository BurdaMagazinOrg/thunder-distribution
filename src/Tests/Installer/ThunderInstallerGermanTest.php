<?php

/**
 * @file
 * Contains \Drupal\thunder\Tests\Installer\ThunderInstallerGermanTest.
 */

namespace Drupal\thunder\Tests\Installer;

/**
 * Tests the interactive installer installing the standard profile.
 *
 * @group Thunder
 */
class ThunderInstallerGermanTest extends ThunderInstallerTest {

  protected $langcode = 'de';

  protected $translations = [
    'Save and continue' => 'Speichern und fortfahren'
  ];

  /**
   * Installer step: Select language.
   */
  protected function setUpLanguage() {
    $edit = array(
      'langcode' => $this->langcode,
    );
    $this->drupalPostForm(NULL, $edit, 'Save and continue');
  }
}
