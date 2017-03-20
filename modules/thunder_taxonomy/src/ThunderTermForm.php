<?php

namespace Drupal\thunder_taxonomy;

use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\TermForm;

/**
 * Base for handler for taxonomy term edit forms.
 */
class ThunderTermForm extends TermForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    // Hide status checkbox. We have the button.
    $form['status']['#access'] = FALSE;

    // Create sidebar group.
    $form['advanced'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['entity-meta']],
      '#weight' => 99,
    ];

    // Use the same form like node edit.
    $form['#theme'] = ['node_edit_form'];
    $form['#attached']['library'][] = 'seven/node-form';

    // Move relations into sidebar.
    $form['relations']['#group'] = 'advanced';

    // Move pathauto into sidebar.
    $term = $form_state->getFormObject()->getEntity();
    $form['path_settings'] = [
      '#type' => 'details',
      '#title' => t('URL path settings'),
      '#open' => !empty($form['path']['widget'][0]['alias']['#value']),
      '#group' => 'advanced',
      '#access' => !empty($form['path']['#access']) && $term->hasField('path') && $term->get('path')->access('edit'),
      '#attributes' => [
        'class' => ['path-form'],
      ],
      '#attached' => [
        'library' => ['path/drupal.path'],
      ],
      '#weight' => 30,
    ];
    $form['path']['#group'] = 'path_settings';

    // Update callback for status change.
    $form['#entity_builders']['update_status'] = [$this, 'updateStatus'];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $element = parent::actions($form, $form_state);

    $term = $this->entity;

    // Add a "Publish" button.
    $element['publish'] = $element['submit'];
    // If the "Publish" button is clicked, we want to update the status to
    // "published".
    $element['publish']['#published_status'] = TRUE;
    $element['publish']['#dropbutton'] = 'save';
    if ($term->isNew()) {
      $element['publish']['#value'] = $this->t('Save and publish');
    }
    else {
      $element['publish']['#value'] = $term->status->value ? $this->t('Save and keep published') : $this->t('Save and publish');
    }
    $element['publish']['#weight'] = 0;

    // Add a "Unpublish" button.
    $element['unpublish'] = $element['submit'];
    // If the "Unpublish" button is clicked, we want to update the status to
    // "unpublished".
    $element['unpublish']['#published_status'] = FALSE;
    $element['unpublish']['#dropbutton'] = 'save';
    if ($term->isNew()) {
      $element['unpublish']['#value'] = $this->t('Save as unpublished');
    }
    else {
      $element['unpublish']['#value'] = !$term->status->value ? $this->t('Save and keep unpublished') : $this->t('Save and unpublish');
    }
    $element['unpublish']['#weight'] = 10;

    // If already published, the 'publish' button is primary.
    if ($term->status->value) {
      unset($element['unpublish']['#button_type']);
    }
    // Otherwise, the 'unpublish' button is primary and should come first.
    else {
      unset($element['publish']['#button_type']);
      $element['unpublish']['#weight'] = -10;
    }

    // Remove the "Save" button.
    $element['submit']['#access'] = FALSE;

    return $element;

  }

  /**
   * Entity builder updating the term status with the submitted value.
   *
   * @param string $entity_type_id
   *   The entity type identifier.
   * @param \Drupal\taxonomy\Entity\Term $term
   *   The term updated with the submitted values.
   * @param array $form
   *   The complete form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function updateStatus($entity_type_id, Term $term, array $form, FormStateInterface $form_state) {
    $element = $form_state->getTriggeringElement();
    if (isset($element['#published_status'])) {
      $term->status->setValue($element['#published_status']);
    }
  }

}
