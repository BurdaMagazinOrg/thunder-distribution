<?php

namespace Drupal\thunder_updater\Diff3;

/**
 * Class with 3-way diff implementation.
 *
 * @package Drupal\thunder_updater\Diff3
 */
class Diff3 {

  protected $conflictingBlocks;

  /**
   * Diff3 constructor.
   */
  public function __construct() {
    $this->conflictingBlocks = 0;
  }

  /**
   * Make 3-way diff.
   *
   * @param array $edits1
   *   First diff edits.
   * @param array $edits2
   *   Second diff edits.
   *
   * @return array
   *   Merged 3-way diff edits.
   */
  public function doDiff3($edits1, $edits2) {
    $blocks = array();
    $bb = new Diff3BlockBuilder();

    /** @var \Drupal\Component\Diff\Engine\DiffOp $e1 */
    $e1 = current($edits1);

    /** @var \Drupal\Component\Diff\Engine\DiffOp $e2 */
    $e2 = current($edits2);
    while ($e1 || $e2) {
      if ($e1 && $e2 && $e1->type == 'copy' && $e2->type == 'copy') {
        // We have copy blocks from both diffs.  This is the (only)
        // time we want to emit a diff3 copy block.
        // Flush current diff3 diff block, if any.
        if ($block = $bb->finish()) {
          $blocks[] = $block;
        }

        $ncopy = min($e1->norig(), $e2->norig());
        assert($ncopy > 0);
        $blocks[] = new Diff3CopyBlock(array_slice($e1->orig, 0, $ncopy));

        if ($e1->norig() > $ncopy) {
          array_splice($e1->orig, 0, $ncopy);
          array_splice($e1->closing, 0, $ncopy);
        }
        else {
          $e1 = next($edits1);
        }

        if ($e2->norig() > $ncopy) {
          array_splice($e2->orig, 0, $ncopy);
          array_splice($e2->closing, 0, $ncopy);
        }
        else {
          $e2 = next($edits2);
        }
      }
      else {
        if ($e1 && $e2) {
          $norig = 0;
          if ($e1->orig && $e2->orig) {
            $norig = min($e1->norig(), $e2->norig());
            $orig = array_splice($e1->orig, 0, $norig);
            array_splice($e2->orig, 0, $norig);
            $bb->input($orig);
          }

          if ($e1->type == 'copy') {
            $bb->out1(array_splice($e1->closing, 0, $norig));
          }

          if ($e2->type == 'copy') {
            $bb->out2(array_splice($e2->closing, 0, $norig));
          }
        }
        if ($e1 && !$e1->orig) {
          $bb->out1($e1->closing);
          $e1 = next($edits1);
        }
        if ($e2 && !$e2->orig) {
          $bb->out2($e2->closing);
          $e2 = next($edits2);
        }
      }
    }

    if ($block = $bb->finish()) {
      $blocks[] = $block;
    }

    return $blocks;
  }

  /**
   * Merge created 3-way diff edits.
   *
   * @param array $blocks
   *   List of 3-way diff edit blocks that should be merged.
   * @param string $label1
   *   Name of first diff used to generated 3-way diff edits.
   * @param string $label2
   *   Name of second diff used to generated 3-way diff edits.
   *
   * @return array
   *   Merged array.
   */
  public function mergedOutput($blocks, $label1 = '', $label2 = '') {
    $lines = array();
    foreach ($blocks as $block) {
      if ($block->isConflict()) {
        // FIXME: this should probably be moved somewhere else...
        $lines = array_merge($lines,
          array("<<<<<<<" . ($label1 ? " $label1" : '')),
          $block->final1,
          array("======="),
          $block->final2,
          array(">>>>>>>" . ($label2 ? " $label2" : '')));

        $this->conflictingBlocks++;
      }
      else {
        $lines = array_merge($lines, $block->merged());
      }
    }
    return $lines;
  }

  /**
   * Check are there any conflicts generated during execution of merge_output.
   *
   * @return bool
   *   Merge status.
   */
  public function isCleanlyMerged() {
    return $this->conflictingBlocks === 0;
  }

}
