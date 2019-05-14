<?php

namespace Drupal\Tests\thunder\FunctionalJavascript;

use Drupal\Component\FileSystem\FileSystem;
use Imagick;

/**
 * Trait for creating and comparing of screenshots.
 *
 * @package Drupal\Tests\thunder\FunctionalJavascript
 */
trait ThunderImageCompareTestTrait {

  /**
   * Flag to switch between creation of screenshots and comparing of them.
   *
   * @var bool
   */
  protected $generateMode = FALSE;

  /**
   * File system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Prefix for temporally created screenshots.
   *
   * @var string
   */
  protected $screenShotPrefix = 'test_ss_';

  /**
   * Set generate mode.
   *
   * @param bool $generateMode
   *   Generate mode.
   */
  public function setGenerateMode($generateMode) {
    $this->generateMode = $generateMode;
  }

  /**
   * Get file system service.
   *
   * @return \Drupal\Core\File\FileSystemInterface
   *   Returns file system service.
   */
  protected function getFileSystem() {
    if (!isset($this->fileSystem)) {
      $this->fileSystem = \Drupal::service('file_system');
    }

    return $this->fileSystem;
  }

  /**
   * Set size of browser window.
   *
   * @param array $windowSize
   *   New size for window. Associative array with width and height keys.
   */
  protected function setWindowSize(array $windowSize) {
    $this->getSession()
      ->getDriver()
      ->resizeWindow($windowSize['width'], $windowSize['height']);
    $this->assertSession()->assertWaitOnAjaxRequest();
  }

  /**
   * Compare screen part to image of previous screenshot.
   *
   * @param string $expectedImageFile
   *   Expected image.
   * @param array $imageSize
   *   Crop information for comparible part of screenshot.
   * @param array $windowSize
   *   Window size, to adjust for browser before screenshot.
   * @param float $threshold
   *   Threshold of allowed error.
   *
   * @return bool
   *   Returns TRUE if difference between images are below allowed threshold.
   *
   * @throws \Exception
   */
  public function compareScreenToImage($expectedImageFile, array $imageSize = [], array $windowSize = [], $threshold = 0.01) {
    $tempScreenShotFile = $this->getFileSystem()
      ->tempnam(FileSystem::getOsTemporaryDirectory(), $this->screenShotPrefix);

    if (!$tempScreenShotFile) {
      throw new \Exception('Unable to get temporally file name.');
    }

    // Resizing of window before making screenshot.
    $adjustWindowForScreenshot = !empty($windowSize);
    if ($adjustWindowForScreenshot) {
      $this->setWindowSize($windowSize);
    }

    // Create screenshot and fetch full path to it.
    $this->createScreenshot($tempScreenShotFile);
    $tempScreenShotFile = realpath($tempScreenShotFile);

    // Set windows size back to previous size, before continuing.
    if ($adjustWindowForScreenshot) {
      $this->setWindowSize($this->getWindowSize());
    }

    // Crop screenshot.
    if (!empty($imageSize)) {
      $image = new Imagick($tempScreenShotFile);
      $image->cropImage($imageSize['width'], $imageSize['height'], $imageSize['x'], $imageSize['y']);
      file_put_contents($tempScreenShotFile, $image);
    }

    return $this->compareImages($expectedImageFile, $tempScreenShotFile, $threshold);
  }

  /**
   * Compare two images with allowed difference threshold.
   *
   * @param string $expectedImageFile
   *   Expected image.
   * @param string $actualImageFile
   *   Actual image.
   * @param float $threshold
   *   Threshold of allowed error.
   *
   * @return bool
   *   Returns TRUE if difference between images are below allowed threshold.
   *
   * @throws \Exception
   */
  public function compareImages($expectedImageFile, $actualImageFile, $threshold = 0.01) {
    // Store created screenshot file for next test execution.
    if ($this->generateMode) {
      $newFileName = \Drupal::service('file_system')->move($actualImageFile, $expectedImageFile, FILE_EXISTS_REPLACE);

      if (!$newFileName) {
        throw new \Exception(sprintf('Unable to create file in %s.', $expectedImageFile));
      }

      return TRUE;
    }

    $expectedImage = new Imagick(realpath($expectedImageFile));
    $actualImage = new Imagick(realpath($actualImageFile));

    $result = $actualImage->compareImages($expectedImage, Imagick::METRIC_MEANSQUAREERROR);
    $differentImages = $result[1] < $threshold;

    if (!$differentImages) {
      $this->storeDiffImage($expectedImageFile, $result);
    }

    return $differentImages;
  }

  /**
   * Store diff image to screenshot folder for possible debugging of test.
   *
   * @param string $expectedImageFile
   *   Expected image.
   * @param array $compareResult
   *   Result of Imagick::compareImages() execution.
   */
  protected function storeDiffImage($expectedImageFile, array $compareResult) {
    $fileName = $this->getScreenshotFolder() . '/' . basename($expectedImageFile) . '_' . date('Ymd_His') . '.png';

    file_put_contents($fileName, $compareResult[0]);
  }

  /**
   * Get screenshot file path.
   *
   * @param string $screenshotName
   *   Screenshot name.
   *
   * @return string
   *   Return file path to screenshot image file.
   */
  public function getScreenshotFile($screenshotName) {
    return dirname(__FILE__) . sprintf('/../../fixtures/screenshots/%s.png', $screenshotName);
  }

}
