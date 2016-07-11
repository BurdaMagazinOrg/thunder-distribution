<?php

namespace Drupal\thunder\Plugin\Thunder\OptionalModule;

use Drupal\Core\Form\FormStateInterface;

/**
 * Riddle integration.
 *
 * @ThunderOptionalModule(
 *   id = "paragraphs_riddle_marketplace",
 *   label = @Translation("Riddle integration"),
 * )
 */
class RiddleIntegration extends AbstractOptionalModule {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['paragraphs_riddle_marketplace'] = array(
      '#type' => 'details',
      '#title' => $this->t('Riddle'),
      '#open' => TRUE,
      '#states' => array(
        'visible' => array(
          ':input[name="install_modules[paragraphs_riddle_marketplace]"]' => array('checked' => TRUE),
        ),
      ),
    );

    $form['paragraphs_riddle_marketplace']['riddle_token'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Riddle token'),
      '#description' => $this->t('Register a new account at <a href=":riddle" target="_blank">riddle.com</a> and get a token from the Account->Plugins page (you may need to reset to get the first token)',
        [':riddle' => 'http://www.riddle.com']),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array $formValues) {

    $this->configFactory->getEditable('riddle_marketplace.settings')
      ->set('riddle_marketplace.token', (string) $formValues['riddle_token'])
      ->save(TRUE);

    /** @var \Drupal\field\Entity\FieldConfig $field */
    $field = entity_load('field_config', 'node.article.field_paragraphs');

    $settings = $field->getSetting('handler_settings');

    $settings['target_bundles']['paragraphs_riddle_marketplace'] = 'paragraphs_riddle_marketplace';
    $settings['target_bundles_drag_drop']['paragraphs_riddle_marketplace'] = ['enabled' => TRUE, 'weight' => 10];

    $field->setSetting('handler_settings', $settings);

    $field->save();

  }

}
