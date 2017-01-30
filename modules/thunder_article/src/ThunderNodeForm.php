<?php

namespace Drupal\thunder_article;

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeForm;

/**
 * Base for handler for node edit forms.
 */
class ThunderNodeForm extends NodeForm {

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $element = parent::actions($form, $form_state);

    if (!empty($element['publish']['#access'])) {
      $element['save_continue'] = $element['publish'];
      $element['save_continue']['#value'] = t('Save and continue');
      $element['save_continue']['#weight'] = min($element['publish']['#weight'], $element['unpublish']['#weight']) - 1;

      $node = $this->entity;

      // If unpublish comes before publish, then we should also not publish.
      if ($node->isNew() || $element['unpublish']['#weight'] < $element['publish']['#weight']) {
        $element['save_continue']['#published_status'] = FALSE;
      }
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    parent::save($form, $form_state);

    if (in_array('save_continue', $form_state->getTriggeringElement()['#parents'])) {
      $form_state->setRedirect('entity.node.edit_form', ['node' => $this->entity->id()]);
    }
  }

}
