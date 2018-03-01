<?php

namespace Drupal\thunder\Plugin\Thunder\OptionalModule;

use Drupal\Core\Form\FormStateInterface;

/**
 * AMP.
 *
 * @ThunderOptionalModule(
 *   id = "thunder_amp",
 *   label = @Translation("AMP"),
 *   description = @Translation("The Google AMP project strives for better performance, especially on mobile devices."),
 *   type = "theme",
 * )
 */
class AMP extends AbstractOptionalModule {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildForm($form, $form_state);

    if (!class_exists('\Lullabot\AMP\AMP')) {
      $form['thunder_amp']['library_info'] = [
        '#type' => 'item',
        '#description' => $this->t("ATTENTION: Due to licensing issues,
        you have to download the needed library manually, before activating this module.
        With composer installed execute the command `composer require pc-magas/amp`
        in the docroot of your installation."),
      ];
    }
    else {
      unset($form['thunder_amp']);
    }

    return $form;

  }

}
