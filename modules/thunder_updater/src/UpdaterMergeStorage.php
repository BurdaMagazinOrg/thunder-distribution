<?php

namespace Drupal\thunder_updater;

use Drupal\Core\Config\FileStorage;

/**
 * Class UpdaterMergeStorage.
 *
 * TODO:
 *   - when service is terminated, temp folder should be removed too.
 *
 * @package Drupal\thunder_updater
 */
class UpdaterMergeStorage extends FileStorage {

  /**
   * Temporally sub-directory where exports will be saved before applying them.
   *
   * It will be used to generate temporal folder with that prefix.
   *
   * @var string
   */
  protected $folderNamePrefix = 'thunder_updater_export';

  /**
   * UpdaterMergeStorage constructor provides merge file storage.
   *
   * @throws \Exception
   *   Throws exception when it's not possible to crate temporally folder.
   */
  public function __construct() {
    $tmpDir = tempnam(sys_get_temp_dir(), $this->folderNamePrefix);
    if (is_file($tmpDir)) {
      unlink($tmpDir);
    }

    mkdir($tmpDir);
    if (!is_dir($tmpDir)) {
      throw new \Exception('Unable to create temporally folder: ' . $tmpDir . ' - to export merged files.');
    }

    parent::__construct($tmpDir);
  }

}
