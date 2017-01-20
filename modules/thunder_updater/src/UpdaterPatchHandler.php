<?php

namespace Drupal\thunder_updater;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystem;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Patch handler class uses to read and store patch files.
 *
 * @package Drupal\thunder_updater
 */
class UpdaterPatchHandler {

  use StringTranslationTrait;

  /**
   * Configuration folder in module, used to store patch files.
   *
   * @var string
   */
  protected $configUpdateFolder = 'config/update';

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * File system service.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

  /**
   * Constructor for patch handler.
   *
   * @param \Drupal\Core\File\FileSystem $fileSystem
   *   File system service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module handler service.
   */
  public function __construct(FileSystem $fileSystem, ModuleHandlerInterface $moduleHandler) {
    $this->fileSystem = $fileSystem;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * Create patch file name based on version and module name.
   *
   * @param string $moduleName
   *   Module name that will be used to generate patch for it.
   * @param string $versionName
   *   Suffix name for patch. Usually version number.
   *
   * @return string
   *   Returns patch file path with file name.
   */
  protected function getFileName($moduleName, $versionName) {
    $modulePath = $this->moduleHandler->getModule($moduleName)->getPath();

    return $modulePath . '/' . $this->configUpdateFolder . '/' . urlencode($moduleName . '-' . $versionName) . '.gz';
  }

  /**
   * Generate patch file.
   *
   * It also creates directory if it does not exist.
   *
   * @param string $moduleName
   *   Module name that will be used to generate patch for it.
   * @param string $versionName
   *   Suffix name for patch. Usually version number.
   * @param string $data
   *   Data that will be saved in file.
   *
   * @return string
   *   Returns full path to saved patch file.
   *
   * @throws \Exception
   */
  public function savePatch($moduleName, $versionName, $data) {
    $patchFilePath = $this->getFileName($moduleName, $versionName);
    $directory = dirname($patchFilePath);

    if (!is_dir($directory)) {
      if ($this->fileSystem->mkdir($directory, NULL, TRUE) === FALSE) {
        throw new \Exception($this->t('Failed to create directory @directory.', ['@directory' => $directory]));
      }
    }

    $updatePatch = gzencode(serialize($data));
    if (file_put_contents($patchFilePath, $updatePatch) === FALSE) {
      throw new \Exception($this->t('Failed to write file @filename.', ['@filename' => $patchFilePath]));
    }

    return $patchFilePath;
  }

  /**
   * Get edits provided by patch for module.
   *
   * @param string $moduleName
   *   Module name that will be used to generate patch for it.
   * @param string $versionName
   *   Suffix name for patch. Usually version number.
   *
   * @return mixed
   *   Returns list of edits per configuration from patch file or empty list.
   */
  public function getPatch($moduleName, $versionName) {
    $patchFilename = $this->getFileName($moduleName, $versionName);

    if (!is_file($patchFilename)) {
      return [];
    }

    $gzipContent = file_get_contents($patchFilename);

    return unserialize(gzdecode($gzipContent));
  }

  /**
   * Get list of patch edits for accross multiple versions.
   *
   * @param string $moduleName
   *   Module name that will be used to generate patch for it.
   * @param string $versions
   *   Suffix names for patch. Usually version number. Comma separated.
   *
   * @return array
   *   Returns list of configuration names and their base configs.
   */
  public function getPatches($moduleName, $versions) {
    $reverseEdits = [];

    $versionNames = explode(',', $versions);
    foreach (array_reverse($versionNames) as $versionName) {
      $reverseEdits = array_merge($reverseEdits, $this->getPatch($moduleName, $versionName));
    }

    return $reverseEdits;
  }

}
