<?php

namespace Drupal\Tests\thunder\Functional;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\thunder\Traits\ThunderTestTrait;

/**
 * Class ThunderTestBase.
 */
abstract class ThunderTestBase extends BrowserTestBase {

  use ThunderTestTrait;
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected $profile = 'thunder';

}
