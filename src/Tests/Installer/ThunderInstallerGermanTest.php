<?php

/**
 * @file
 * Contains \Drupal\thunder\Tests\Installer\ThunderInstallerGermanTest.
 */

namespace Drupal\thunder\Tests\Installer;
use Drupal\Core\DrupalKernel;
use Drupal\Core\Language\Language;
use Drupal\Core\Session\UserSession;
use Drupal\Core\Site\Settings;
use Drupal\simpletest\InstallerTestBase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
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
