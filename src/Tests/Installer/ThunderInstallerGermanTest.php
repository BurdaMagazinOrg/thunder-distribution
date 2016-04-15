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

}
