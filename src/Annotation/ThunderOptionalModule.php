<?php

namespace Drupal\thunder\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an entity browser widget annotation object.
 *
 * @see hook_entity_browser_widget_info_alter()
 *
 * @Annotation
 */
class ThunderOptionalModule extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the widget.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * The human-readable name of the widget.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $type;

}
