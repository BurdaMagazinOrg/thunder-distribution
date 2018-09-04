<?php

namespace Drupal\Tests\thunder\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\thunder\Traits\ThunderTestTrait;

/**
 * Class ThunderTestBase.
 */
abstract class ThunderTestBase extends BrowserTestBase {

  use ThunderTestTrait;

  protected $profile = 'thunder';

}
