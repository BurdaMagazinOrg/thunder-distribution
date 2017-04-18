<?php

namespace Drupal\thunder_riddle\Plugin\EntityBrowser\Widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\entity_browser\Plugin\EntityBrowser\Widget\View;

/**
 * Displays current selection in a View.
 *
 * @EntityBrowserWidget(
 *   id = "riddle_view",
 *   label = @Translation("Riddle View"),
 *   provider = "views",
 *   description = @Translation("Extended view to import riddles out of the EB."),
 *   auto_select = TRUE
 * )
 */
class RiddleView extends View {

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$original_form, FormStateInterface $form_state, array $additional_widget_parameters) {
    $form = parent::getForm($original_form, $form_state, $additional_widget_parameters);

    $form['actions']['import_riddle'] = [
      '#title' => $this->t('Import my riddles'),
      '#type' => 'link',
      '#attributes' => [
        'class' => ['button'],
      ],
      '#url' => Url::fromRoute('riddle_marketplace.import'),
    ];

    return $form;
  }

}
