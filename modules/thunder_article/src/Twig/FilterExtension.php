<?php

namespace Drupal\thunder_article\Twig;

/**
 * Introduce some twig filters.
 */
class FilterExtension extends \Twig_Extension {

  /**
   * Returns introduced filters.
   *
   * @return array
   *   Declared Twig filters
   */
  public function getFilters() {
    return [
      new \Twig_SimpleFilter('plain_text', [$this, 'plainText']),
      new \Twig_SimpleFilter('basic_format', [$this, 'basicFormat'], ['is_safe' => ['html']]),
    ];
  }

  /**
   * Returns the name of the extension.
   *
   * @return string
   *   The extension name
   */
  public function getName() {
    return 'thunder_article_filter_extension';
  }

  /**
   * Plains a text. Strips everything evil out.
   *
   * @param string $value
   *   The content to be processed.
   *
   * @return string
   *   The processed content.
   */
  public static function plainText($value) {
    $element = render($value);
    $element = strip_tags($element);
    $element = html_entity_decode($element, ENT_QUOTES);
    return $element;
  }

  /**
   * Cleans a text and just allow a few tags.
   *
   * @param string $value
   *   The content to be processed.
   *
   * @return string
   *   The processed content.
   */
  public static function basicFormat($value) {
    $element = render($value);
    $element = strip_tags($element, '<a><em><strong><b><i>');
    return $element;
  }

}
