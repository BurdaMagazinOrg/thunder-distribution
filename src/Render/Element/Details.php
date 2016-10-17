<?php

namespace Drupal\thunder\Render\Element;

use Drupal\Core\Render\Element\Details as CoreDetails;

/**
 * Class Details.
 */
class Details extends CoreDetails {

  /**
   * {@inheritdoc}
   */
  public static function preRenderDetails($element) {

    $element = parent::preRenderDetails($element);

    // Open the detail if specified or if a child has an error.
    if (!empty($element['#open']) || !empty($element['#children_errors'])) {
      $element['#attributes']['open'] = 'open';
    }

    return $element;
  }

}
