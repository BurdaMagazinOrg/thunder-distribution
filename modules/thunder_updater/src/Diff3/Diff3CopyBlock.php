<?php

namespace Drupal\thunder_updater\Diff3;

/**
 * Class Diff3CopyBlock.
 *
 * @package Drupal\Component\Diff\Engine
 */
class Diff3CopyBlock extends Diff3Block {

  public $type = 'copy';

  /**
   * Diff3CopyBlock constructor.
   *
   * @param array $lines
   *   Lines that will be copied.
   */
  public function __construct($lines = array()) {
    parent::__construct([], [], []);

    $this->orig = $lines ? $lines : array();
    $this->final1 = &$this->orig;
    $this->final2 = &$this->orig;
  }

  /**
   * {@inheritdoc}
   */
  public function merged() {
    return $this->orig;
  }

  /**
   * {@inheritdoc}
   */
  public function isConflict() {
    return FALSE;
  }

}
