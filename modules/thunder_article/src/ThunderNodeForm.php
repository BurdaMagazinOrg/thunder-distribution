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

    if ($element['publish']['#access'] && \Drupal::currentUser()->hasPermission('administer nodes')) {
      $element['save_continue'] = $element['publish'];
      $element['save_continue']['#value'] = t('Save and continue');
      $element['save_continue']['#weight'] = $element['publish']['#weight'] - 1;
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
