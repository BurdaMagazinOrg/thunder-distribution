<?php

namespace Drupal\thunder_paragraphs\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\inline_entity_form\Element\InlineEntityForm;
use Drupal\inline_entity_form\Plugin\Field\FieldWidget\InlineEntityFormBase;
use Drupal\inline_entity_form\TranslationHelper;

/**
 * Simple inline widget.
 *
 * @FieldWidget(
 *   id = "inline_entity_form_simple_plus",
 *   label = @Translation("Inline entity form - Simple plus"),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   multiple_values = false
 * )
 */
class InlineEntityFormSimplePlus extends InlineEntityFormBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $defaults = parent::defaultSettings();
    $defaults += [
      'add_existing' => TRUE,
      'match_operator' => 'CONTAINS',
    ];

    return $defaults;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $labels = $this->getEntityTypeLabels();
    $states_prefix = 'fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings]';
    $element['add_existing'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow users to add existing @label.', ['@label' => $labels['plural']]),
      '#default_value' => $this->getSetting('add_existing'),
    ];
    $element['match_operator'] = [
      '#type' => 'select',
      '#title' => $this->t('Autocomplete matching'),
      '#default_value' => $this->getSetting('match_operator'),
      '#options' => $this->getMatchOperatorOptions(),
      '#description' => $this->t('Select the method used to collect autocomplete suggestions. Note that <em>Contains</em> can cause performance issues on sites with thousands of nodes.'),
      '#states' => [
        'visible' => [
          ':input[name="' . $states_prefix . '[add_existing]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $labels = $this->getEntityTypeLabels();

    $match_operator_options = $this->getMatchOperatorOptions();
    if ($this->getSetting('add_existing')) {
      $summary[] = $this->t('Existing @label can be referenced and are matched with the %operator operator.', [
        '@label' => $labels['plural'],
        '%operator' => $match_operator_options[$this->getSetting('match_operator')],
      ]);
    }
    else {
      $summary[] = $this->t('Existing @label can not be referenced.', ['@label' => $labels['plural']]);
    }

    return $summary;
  }

  /**
   * Returns the options for the match operator.
   *
   * @return array
   *   List of options.
   */
  protected function getMatchOperatorOptions() {
    return [
      'STARTS_WITH' => $this->t('Starts with'),
      'CONTAINS' => $this->t('Contains'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $settings = $this->getSettings();
    $target_type = $this->getFieldSetting('target_type');
    // Get the entity type labels for the UI strings.
    $labels = $this->getEntityTypeLabels();

    // Trick inline_entity_form_form_alter() into attaching the handlers,
    // WidgetSubmit will be needed once extractFormValues fills the $form_state.
    $parents = array_merge($element['#field_parents'], [$items->getName()]);
    $this->setIefId(sha1(implode('-', $parents)));

    // Get state from storage.
    $widget_state = $form_state->get(['inline_entity_form', $this->getIefId()]);

    if (!$widget_state) {
      $widget_state = [
        'instance' => $this->fieldDefinition,
        'form' => NULL,
        'delete' => [],
        'entities' => [],
      ];
      $form_state->set(['inline_entity_form', $this->getIefId()], $widget_state);
    }

    // Determine the wrapper ID for the entire element.
    $wrapper = 'inline-entity-form-' . $this->getIefId();

    $element = [
      '#type' => $this->getSetting('collapsible') ? 'details' : 'fieldset',
      '#prefix' => '<div id="' . $wrapper . '">',
      '#suffix' => '</div>',
      '#ief_id' => $this->getIefId(),
      '#ief_root' => TRUE,
      '#field_title' => $this->fieldDefinition->getLabel(),
      '#after_build' => [
        [get_class($this), 'removeTranslatabilityClue'],
      ],
    ] + $element;

    if ($element['#type'] == 'details') {
      $element['#open'] = !$this->getSetting('collapsed');
    }

    $item = $items->get($delta);
    if ($item->target_id && !$item->entity) {
      $element['warning']['#markup'] = $this->t('Unable to load the referenced entity.');
      return $element;
    }

    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $item->entity;

    if (isset($widget_state['entities'][$delta]['entity'])) {
      $entity = $widget_state['entities'][$delta]['entity'];
    }

    $op = $entity ? 'edit' : 'add';
    $langcode = $items->getEntity()->language()->getId();
    $parents = array_merge($element['#field_parents'], [
      $items->getName(),
      $delta,
      'inline_entity_form',
    ]);
    $bundle = !empty($this->getFieldSetting('handler_settings')['target_bundles']) ? reset($this->getFieldSetting('handler_settings')['target_bundles']) : NULL;
    if (($op == 'add' && !$settings['add_existing']) || $op == 'edit') {
      $element['inline_entity_form'] = $this->getInlineEntityForm($op, $bundle, $langcode, $delta, $parents, $entity);
    }

    if ($op == 'add' && $settings['add_existing']) {

      $element['inline_entity_form'] = [
        '#type' => 'fieldset',
        '#attributes' => ['class' => ['ief-form', 'ief-form-bottom']],
        // Identifies the IEF widget to which the form belongs.
        '#ief_id' => $this->getIefId(),
        // Used by Field API and controller methods to find the relevant
        // values in $form_state.
        '#parents' => array_merge($parents),
        // Pass the current entity type.
        '#entity_type' => $target_type,
        // Pass the widget specific labels.
        '#ief_labels' => $labels,
      ];

      $element['inline_entity_form'] += inline_entity_form_reference_form($element['inline_entity_form'], $form_state);

      // Hide submit only visually, gets triggered by entityBrowserEntityForm
      // behaviour.
      if (isset($element['inline_entity_form']['entity_browser'])) {
        $element['inline_entity_form']['actions']['ief_reference_save']['#attributes']['class'][] = 'visually-hidden';
      }

      $element['inline_entity_form']['actions']['ief_reference_cancel']['#access'] = FALSE;
      unset($element['inline_entity_form']['#title']);
    }

    if ($op == 'edit') {
      /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
      if (!$entity->access('update')) {
        // The user isn't allowed to edit the entity, but still needs to see
        // it, to be able to reorder values.
        $element['entity_label'] = [
          '#type' => 'markup',
          '#markup' => $entity->label(),
        ];
        // Hide the inline form. getInlineEntityForm() still needed to be
        // called because otherwise the field re-ordering doesn't work.
        $element['inline_entity_form']['#access'] = FALSE;
      }
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $element = parent::formMultipleElements($items, $form, $form_state);

    // If we're using ulimited cardinality we don't display one empty item. Form
    // validation will kick in if left empty which esentially means people won't
    // be able to submit w/o creating another entity.
    if (!$form_state->isSubmitted() && $element['#cardinality'] == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED && $element['#max_delta'] > 0) {
      $max = $element['#max_delta'];
      unset($element[$max]);
      $element['#max_delta'] = $max - 1;
      $items->removeItem($max);
      // Decrement the items count.
      $field_name = $element['#field_name'];
      $parents = $element[0]['#field_parents'];
      $field_state = static::getWidgetState($parents, $field_name, $form_state);
      $field_state['items_count']--;
      static::setWidgetState($parents, $field_name, $form_state, $field_state);
    }

    // Remove add options if the user cannot add new entities.
    if (!$this->canAddNew()) {
      if (isset($element['add_more'])) {
        unset($element['add_more']);
      }
      foreach (Element::children($element) as $delta) {
        if (isset($element[$delta]['inline_entity_form'])) {
          if ($element[$delta]['inline_entity_form']['#op'] == 'add') {
            unset($element[$delta]);
          }
        }
      }
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function extractFormValues(FieldItemListInterface $items, array $form, FormStateInterface $form_state) {
    if ($this->isDefaultValueWidget($form_state)) {
      $items->filterEmptyItems();
      return;
    }

    $field_name = $this->fieldDefinition->getName();
    $parents = array_merge($form['#parents'], [$field_name]);
    $submitted_values = $form_state->getValue($parents);
    $values = [];
    foreach ($items as $delta => $value) {
      $element = NestedArray::getValue($form, [$field_name, 'widget', $delta]);
      if (!empty($element['inline_entity_form'])) {
        $inline_form_handler = InlineEntityForm::getInlineFormHandler($element['inline_entity_form']['#entity_type']);
        /** @var \Drupal\Core\Entity\EntityInterface $entity */
        if (!empty($element['inline_entity_form']['#entity'])) {

          $entity = $element['inline_entity_form']['#entity'];
          $inline_form_handler->buildEntity($element['inline_entity_form'], $entity, $form_state);

          $weight = isset($submitted_values[$delta]['_weight']) ? $submitted_values[$delta]['_weight'] : 0;
          $values[$weight] = ['entity' => $entity];
        }
      }
    }

    // Sort items base on weights.
    ksort($values);
    $values = array_values($values);

    // Let the widget massage the submitted values.
    $values = $this->massageFormValues($values, $form, $form_state);

    // Assign the values and remove the empty ones.
    $items->setValue($values);
    $items->filterEmptyItems();

    // Populate the IEF form state with $items so that WidgetSubmit can
    // perform the necessary saves.
    $ief_id = sha1(implode('-', $parents));
    $widget_state = [
      'instance' => $this->fieldDefinition,
      'delete' => [],
      'entities' => [],
    ];
    foreach ($items as $delta => $value) {
      TranslationHelper::updateEntityLangcode($value->entity, $form_state);
      $widget_state['entities'][$delta] = [
        'entity' => $value->entity,
        'needs_save' => TRUE,
      ];
    }
    $form_state->set(['inline_entity_form', $ief_id], $widget_state);

    // Put delta mapping in $form_state, so that flagErrors() can use it.
    $field_name = $this->fieldDefinition->getName();
    $field_state = WidgetBase::getWidgetState($form['#parents'], $field_name, $form_state);
    foreach ($items as $delta => $item) {
      $field_state['original_deltas'][$delta] = isset($item->_original_delta) ? $item->_original_delta : $delta;
      unset($item->_original_delta, $item->weight);
    }
    WidgetBase::setWidgetState($form['#parents'], $field_name, $form_state, $field_state);
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $handler_settings = $field_definition->getSettings()['handler_settings'];
    $target_entity_type_id = $field_definition->getFieldStorageDefinition()->getSetting('target_type');
    $target_entity_type = \Drupal::entityTypeManager()->getDefinition($target_entity_type_id);
    // The target entity type doesn't use bundles, no need to validate them.
    if (!$target_entity_type->getKey('bundle')) {
      return TRUE;
    }

    if (empty($handler_settings['target_bundles'])) {
      return FALSE;
    }

    if (count($handler_settings['target_bundles']) != 1) {
      return FALSE;
    }

    return TRUE;
  }

}
