<?php

namespace Drupal\thunder_updater\Diff3;

/**
 * Class Diff3BlockBuilder.
 *
 * @package Drupal\Component\Diff\Engine
 */
class Diff3BlockBuilder {

  public $orig;
  public $final1;
  public $final2;

  /**
   * Diff3BlockBuilder constructor.
   */
  public function __construct() {
    $this->init();
  }

  /**
   * Init method.
   */
  private function init() {
    $this->orig = array();
    $this->final1 = array();
    $this->final2 = array();
  }

  /**
   * Append lines to array.
   *
   * @param array $array
   *   List of lines.
   * @param array $lines
   *   New lines that will be appended.
   */
  private function append(&$array, $lines) {
    array_splice($array, count($array), 0, $lines);
  }

  /**
   * Add original lines to existing list.
   *
   * @param mixed $lines
   *   List of original lines.
   */
  public function input($lines) {
    if ($lines) {
      $this->append($this->orig, $lines);
    }
  }

  /**
   * Add first difference lines to existing list.
   *
   * @param mixed $lines
   *   List of first diff lines.
   */
  public function out1($lines) {
    if ($lines) {
      $this->append($this->final1, $lines);
    }
  }

  /**
   * Add second diff lines to existing list.
   *
   * @param mixed $lines
   *   List of second diff lines.
   */
  public function out2($lines) {
    if ($lines) {
      $this->append($this->final2, $lines);
    }
  }

  /**
   * Check is block empty.
   *
   * @return bool
   *   Empty block.
   */
  private function isEmpty() {
    return !$this->orig && !$this->final1 && !$this->final2;
  }

  /**
   * Finalize building of 3-way diff block.
   *
   * @return bool|\Drupal\thunder_updater\Diff3\Diff3Block
   *   Return new 3-way diff block or FALSE if it's empty block.
   */
  public function finish() {
    if ($this->isEmpty()) {
      return FALSE;
    }
    else {
      $block = new Diff3Block($this->orig, $this->final1, $this->final2);
      $this->init();
      return $block;
    }
  }

}
