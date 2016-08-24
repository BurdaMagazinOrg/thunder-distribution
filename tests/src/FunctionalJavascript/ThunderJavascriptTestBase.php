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


  protected function setUp() {

    parent::setUp();

    $editor = $this->drupalCreateUser();
    $editor->addRole('editor');
    $editor->save();
    $this->drupalLogin($editor);
  }

  /**
   * Waits and asserts that a given element is visible.
   *
   * @param string $selector
   *   The CSS selector.
   * @param int $timeout
   *   (Optional) Timeout in milliseconds, defaults to 1000.
   * @param string $message
   *   (Optional) Message to pass to assertJsCondition().
   */
  protected function waitUntilVisible($selector, $timeout = 1000, $message = '') {
    $condition = "jQuery('" . $selector . ":visible').length > 0";
    $this->assertJsCondition($condition, $timeout, $message);
  }
}