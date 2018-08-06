<?php

namespace Drupal\Tests\thunder\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\thunder\Traits\ThunderTestTrait;

/**
 * Class ThunderBaseTest.
 *
 * @package Drupal\thunder
 */
class ThunderBaseTest extends BrowserTestBase {

  use ThunderTestTrait;

  protected $profile = 'thunder';

}
