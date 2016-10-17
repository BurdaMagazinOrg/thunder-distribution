<?php

namespace Drupal\thunder\Form;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormErrorHandler as CoreFormErrorHandler;
use Drupal\Core\Render\Element;

/**
 * Handles form errors.
 */
class FormErrorHandler extends CoreFormErrorHandler {

  /**
   * Stores errors and children errors of each element directly on the element.
   *
   * We must provide a way for non-form functions to check the errors for a
   * specific element. The most common usage of this is a #pre_render callback.
   *
   * Grouping elements like containers, details, fieldgroups and fieldsets may
   * need error info of their children to be able to accessibly show form
   * problems to a user. A good example is a details element which should be
   * opened when children have errors.
   *
   * @param array $form
   *   An associative array containing the structure of a form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $elements
   *   An associative array containing the structure of a form element. This
   *   should be left empty in the case this method isn't called by itself.
   */
  protected function setElementErrorsFromFormState(array &$form, FormStateInterface $form_state, array &$elements = array()) {
    if (empty($elements)) {
      $elements =& $form;
    }

    // Recurse through all children.
    foreach (Element::children($elements) as $key) {
      if (isset($elements[$key]) && $elements[$key]) {
        $child =& $elements[$key];

        // Call self to traverse up the form tree.
        $this->setElementErrorsFromFormState($form, $form_state, $child);

        $elements['#children_errors'] = array();

        // Inherit all children errors of the direct child.
        if (!empty($child['#children_errors'])) {
          $elements['#children_errors'] = $child['#children_errors'];
        }

        // Additionally store the error of the direct child itself, keyed by
        // it's parent structure.
        if (!empty($child['#errors'])) {
          $parents = implode('][', $child['#parents']);
          $elements['#children_errors'][$parents] = $child['#errors'];
        }

        // If this direct child belongs to a group populate the grouping element
        // with the children errors.
        if (!empty($child['#group'])) {
          $parents = explode('][', $child['#group']);
          $group_element = NestedArray::getValue($form, $parents);
          if (isset($group_element['#children_errors'])) {
            $group_element['#children_errors'] = array_merge($group_element['#children_errors'], $elements['#children_errors']);
          }
          else {
            $group_element['#children_errors'] = $elements['#children_errors'];
          }
          NestedArray::setValue($form, $parents, $group_element);
        }
      }
    }

    // Store the errors for this element on the element directly.
    $elements['#errors'] = $form_state->getError($elements);
  }

}
