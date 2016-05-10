<?php
/**
 * @file
 * Contains
 */

namespace Drupal\thunder;


use Drupal\simpletest\WebTestBase;

class ThunderBaseTest extends WebTestBase {

  protected $profile = 'thunder';

  protected $strictConfigSchema = FALSE;

  /**
   * Gets IEF button name.
   *
   * @param array $xpath
   *   Xpath of the button.
   *
   * @return string
   *   The name of the button.
   */
  protected function getButtonName($xpath) {
    $retval = '';
    /** @var \SimpleXMLElement[] $elements */
    if ($elements = $this->xpath($xpath)) {
      foreach ($elements[0]->attributes() as $name => $value) {
        if ($name == 'name') {
          $retval = $value;
          break;
        }
      }
    }
    return $retval;
  }
}
