<?php

namespace Drupal\thunder\Installer\Form\OptionalModules;

/**
 * Class FacebookInstantArticles.
 *
 * @package Drupal\thunder\Installer\Form\OptionalModules
 */
class FacebookInstantArticles extends AbstractOptionalModule {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {

    return 'fb_instant_articles';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormName() {
    return 'Facebook Instant Articles';
  }

}
