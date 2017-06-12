<?php

namespace Drupal\thunder_updater\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Interface for the Update entity.
 */
interface UpdateInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * Returns if an update was successful during update hook.
   *
   * @return bool
   *   Update was successful or not.
   */
  public function wasSuccessfulByHook();

  /**
   * Set successful_by_hook field value.
   *
   * @param bool $success
   *   Update was successful or not.
   *
   * @return mixed
   *   This object.
   */
  public function setSuccessfulByHook($success);

}
