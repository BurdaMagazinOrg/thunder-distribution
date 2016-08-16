<?php

namespace Drupal\thunder\Plugin\Thunder\OptionalModule;

use Drupal\Core\Form\FormStateInterface;

/**
 * Riddle integration.
 *
 * @ThunderOptionalModule(
 *   id = "nexx_integration",
 *   label = @Translation("Nexx video integration"),
 *   type = "module",
 * )
 */
class NexxIntegration extends AbstractOptionalModule {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['nexx_integration'] = array(
      '#type' => 'details',
      '#title' => $this->t('Nexx video'),
      '#open' => TRUE,
      '#states' => array(
        'visible' => array(
          ':input[name="install_modules[nexx_integration]"]' => array('checked' => TRUE),
        ),
      ),
    );

    $form['nexx_integration']['nexx_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Nexx URL'),
      '#description' => $this->t('Register a new account at <a href=":nexx_url" target="_blank">http://www.nexx.tv/thunder</a> and get a url and key.',
        [':nexx_url' => 'http://www.nexx.tv/thunder']),
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
