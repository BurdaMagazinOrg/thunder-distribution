<?php

namespace Drupal\thunder\Plugin\Field\FieldWidget;

use Drupal\content_moderation\Plugin\Field\FieldWidget\ModerationStateWidget as CoreModerationStateWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'thunder_moderation_state_default' widget.
 *
 * Thunder provides it's own moderation_state widget that only shows a list of
 * possible states to switch in. To display the current state is not part of
 * this widget. Thunder shows that in ThunderNodeForm.
 *
 * @FieldWidget(
 *   id = "thunder_moderation_state_default",
 *   label = @Translation("Moderation state (Thunder)"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class ModerationStateWidget extends CoreModerationStateWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $items->getEntity();

    $workflow = $this->moderationInformation->getWorkflowForEntity($entity);
    $default = $items->get($delta)->value ? $workflow->getTypePlugin()->getState($items->get($delta)->value) : $workflow->getTypePlugin()->getInitialState($entity);

    /** @var \Drupal\workflows\Transition[] $transitions */
    $transitions = $this->validator->getValidTransitions($entity, $this->currentUser);

    $transition_labels = [];
    $default_value = NULL;
    foreach ($transitions as $transition) {
      $transition_to_state = $transition->to();
      $transition_labels[$transition_to_state->id()] = $transition_to_state->label();
      if ($default->id() === $transition_to_state->id()) {
        $default_value = $default->id();
      }
    }

    $element = [
      '#type' => 'select',
      '#key_column' => $this->column,
      '#options' => $transition_labels,
      '#default_value' => $default_value,
      '#access' => !empty($transition_labels),
      '#wrapper_attributes' => [
        'class' => ['container-inline'],
      ],
    ];
    $element['#element_validate'][] = [get_class($this), 'validateElement'];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function validateElement(array $element, FormStateInterface $form_state) {
    $form_state->setValueForElement($element, [$element['#key_column'] => $element['#value']]);
  }

}
