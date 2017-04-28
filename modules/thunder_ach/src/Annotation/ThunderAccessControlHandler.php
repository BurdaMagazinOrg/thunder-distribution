<?php

namespace Drupal\thunder_ach\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an featured content processor annotation object.
 *
 * Plugin Namespace: Plugin\thunder_ach.
 *
 * @see \Drupal\thunder_ach\AccessControlHandlerManager
 * @see \Drupal\thunder_ach\Plugin\AccessControlHandlerInterface
 * @see \Drupal\thunder_ach\Plugin\AccessControlHandlerBase
 * @see plugin_api
 *
 * @Annotation
 */
class ThunderAccessControlHandler extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The plugin weight.
   *
   * @var int
   */
  public $weight;

  /**
   * Name of the entity type the handler controls access for.
   *
   * @var string
   */
  public $type;

}
