<?php
/**
 * Created by PhpStorm.
 * User: timowelde
 * Date: 04.08.16
 * Time: 15:39
 */

namespace Drupal\thunder_article\Twig;


class FilterExtension extends \Twig_Extension{

  /**
   * @return array Declared Twig filters
   */
  public function getFilters() {
    return array(
      new \Twig_SimpleFilter('plain_text', array($this, 'plainText')),
    );
  }

  /**
   * Returns the name of the extension.
   *
   * @return string The extension name
   */
  public function getName() {
    return 'filter_extension';
  }

  /**
   * @param string $value The content to be processed
   * @return string The processed content
   */
  public static function plainText($value) {
    $element = render($value);
    $element = strip_tags($element);
    $element = html_entity_decode($element, ENT_QUOTES);
    return $element;
  }
}