<?php

namespace Drupal\thunder\Plugin\Thunder\OptionalModule;

use Drupal\Core\Form\FormStateInterface;

/**
 * IVW Integration.
 *
 * @ThunderOptionalModule(
 *   id = "ivw_integration",
 *   label = @Translation("IVW Integration"),
 *   type = "module",
 * )
 */
class IvwIntegration extends AbstractOptionalModule {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['ivw_integration'] = array(
      '#type' => 'details',
      '#title' => $this->t('IVW'),
      '#open' => TRUE,
      '#states' => array(
        'visible' => array(
          ':input[name="install_modules[ivw_integration]"]' => array('checked' => TRUE),
        ),
      ),
    );
    $form['ivw_integration']['ivw_site'] = array(
      '#type' => 'textfield',
      '#title' => t('IVW Site name'),
      '#description' => t('Site name as given by IVW, this is used as default for the "st" parameter in the iam_data object'),
    );

    $form['ivw_integration']['mobile_site'] = array(
      '#type' => 'textfield',
      '#title' => t('IVW Mobile Site name'),
      '#description' => t('Mobile site name as given by IVW, this is used as default for the "st" parameter in the iam_data object'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array $formValues) {

    $this->configFactory->getEditable('ivw_integration.settings')
      ->set('site', (string) $formValues['ivw_site'])
      ->set('mobile_site', (string) $formValues['mobile_site'])
      ->save(TRUE);

    $this->addField('node', 'article', 'field_ivw', 'IVW settings', 'ivw_integration_settings', 'ivw_integration_widget');
    $this->addField('taxonomy_term', 'channel', 'field_ivw', 'IVW settings', 'ivw_integration_settings', 'ivw_integration_widget');

  }

}
