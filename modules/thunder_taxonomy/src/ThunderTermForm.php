<?php

namespace Drupal\thunder_taxonomy;

use Drupal\Core\Form\FormStateInterface;
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
    $form['status']['#group'] = 'footer';

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
      '#title' => $this->t('URL path settings'),
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

    return $form;
  }

}
