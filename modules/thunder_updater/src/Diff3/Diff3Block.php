<?php

namespace Drupal\thunder_updater\Diff3;

/**
 * Class Diff3Block.
 *
 * @package Drupal\Component\Diff\Engine
 */
class Diff3Block {

  public $type = 'diff3';

  /**
   * Diff3Block constructor.
   *
   * @param mixed $orig
   *   Original value.
   * @param mixed $final1
   *   Value from first diff.
   * @param mixed $final2
   *   Value from second diff.
   */
  public function __construct($orig = FALSE, $final1 = FALSE, $final2 = FALSE) {
    $this->orig = $orig ? $orig : array();
    $this->final1 = $final1 ? $final1 : array();
    $this->final2 = $final2 ? $final2 : array();
  }

  /**
   * Execute merging of block.
   *
   * @return array|bool|mixed
   *   Returns merged value for block.
   */
  public function merged() {
    if (!isset($this->_merged)) {
      if ($this->final1 === $this->final2) {
        $this->_merged = &$this->final1;
      }
      elseif ($this->final1 === $this->orig) {
        $this->_merged = &$this->final2;
      }
      elseif ($this->final2 === $this->orig) {
        $this->_merged = &$this->final1;
      }
      else {
        $this->_merged = FALSE;
      }
    }
    return $this->_merged;
  }

  /**
   * Check is merge conflicted.
   *
   * @return bool
   *   Is block with conflicted merge.
   */
  public function isConflict() {
    return $this->merged() === FALSE;
  }

}
