<?php
/**
 * @file
 * Contains
 */

namespace Drupal\thunder\Tests;


use Drupal\simpletest\WebTestBase;

/**
 * @group Thunder
 */
class ThunderBaseTest extends WebTestBase {

  protected $profile = 'thunder';

  protected $strictConfigSchema = FALSE;

}
