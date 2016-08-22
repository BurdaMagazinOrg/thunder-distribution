<?php

namespace Drupal\Tests\thunder\FunctionalJavascript;
use Drupal\FunctionalJavascriptTests\JavascriptTestBase;

/**
 * Base class for Thunder Javascript functional tests.
 *
 * @package Drupal\Tests\thunder\FunctionalJavascript
 */
abstract class ThunderJavascriptTestBase extends JavascriptTestBase {

  /**
   * The profile to install as a basis for testing.
   *
   * @var string
   */
  protected $profile = 'thunder';

}