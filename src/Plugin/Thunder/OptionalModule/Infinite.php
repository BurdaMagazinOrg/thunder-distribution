<?php

namespace Drupal\thunder\Plugin\Thunder\OptionalModule;

use Drupal\Core\Action\ActionManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigInstallerInterface;

/**
 * Google Analytics.
 *
 * @ThunderOptionalModule(
 *   id = "infinite",
 *   label = @Translation("Infinite Theme"),
 *   type = "theme",
 * )
 */
class Infinite extends AbstractOptionalModule {


  public function submitForm(array $formValues) {

    \Drupal::configFactory()
      ->getEditable('system.theme')
      ->set('default', 'infinite')
      ->save(TRUE);
  }


}
