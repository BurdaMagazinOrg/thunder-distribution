<?php

namespace Drupal\thunder_updater;

use Drupal\config_update\ConfigDiffer;

/**
 * Overwrite of config updater differ.
 *
 * Normalization is changed so that it can be 2-way normalization, not 1-way.
 * Also format is adjusted to better supports converting from/to config array.
 *
 * @package Drupal\thunder_updater
 */
class UpdaterConfigDiffer extends ConfigDiffer {

  /**
   * {@inheritdoc}
   */
  protected function normalize($config) {
    // Recursively normalize remaining elements, if they are arrays.
    foreach ($config as $key => $value) {
      if (is_array($value)) {
        $new = $this->normalize($value);

        $config[$key] = $new;
      }
    }

    // Sort and return.
    ksort($config);
    return $config;
  }

  /**
   * {@inheritdoc}
   */
  protected function format($config, $prefix = '') {
    $lines = [];

    $associativeConfig = array_keys($config) !== range(0, count($config) - 1);

    foreach ($config as $key => $value) {
      if (!$associativeConfig) {
        $key = '-';
      }

      $section_prefix = ($prefix) ? $prefix . $this->hierarchyPrefix . $key : $key;
      if (is_array($value)) {
        $lines[] = $section_prefix;
        $newlines = $this->format($value, $section_prefix);
        foreach ($newlines as $line) {
          $lines[] = $line;
        }
      }
      elseif (is_null($value)) {
        $lines[] = $section_prefix . $this->valuePrefix . $this->t('(NULL)');
      }
      else {
        $lines[] = $section_prefix . $this->valuePrefix . $value;
      }
    }

    return $lines;
  }

  /**
   * Denormalize flat array and generate associative array for Yaml export.
   *
   * @param array $mergedData
   *   Merged flat array.
   *
   * @return array
   *   Normalized array for Yaml generation.
   */
  public function formatToConfig(array $mergedData) {
    $result = [];

    foreach ($mergedData as $ymlRow) {
      $keyValue = explode(' : ', $ymlRow);

      $keyPath = explode('::', $keyValue[0]);

      $lastKey = array_pop($keyPath);
      $currentElement = &$result;
      foreach ($keyPath as $key) {
        if (!isset($currentElement[$key])) {
          $currentElement[$key] = [];
        }

        $currentElement = &$currentElement[$key];
      }

      if (count($keyValue) === 2) {
        $value = ($keyValue[1] == $this->t('(NULL)')) ? NULL : $keyValue[1];
        if ($lastKey === '-') {
          $currentElement[] = $value;
        }
        else {
          $currentElement[$lastKey] = $value;
        }
      }
      else {
        $currentElement[$lastKey] = [];
      }
    }

    return $result;
  }

}
