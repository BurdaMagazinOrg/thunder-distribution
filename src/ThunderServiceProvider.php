<?php

namespace Drupal\thunder;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies services.
 */
class ThunderServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Overrides form_error_handler class to use thunder ones.
    $definition = $container->getDefinition('form_error_handler');
    $definition->setClass('Drupal\thunder\Form\FormErrorHandler');
  }

}
