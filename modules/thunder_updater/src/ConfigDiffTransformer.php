<?php

namespace Drupal\thunder_updater;

/**
 * Config transformer for configuration diffing.
 *
 * @package Drupal\thunder_updater
 */
class ConfigDiffTransformer {

  /**
   * Prefix to use to indicate config hierarchy.
   *
   * @var string
   *
   * @see ReversibleConfigDiffer::format().
   */
  protected $hierarchyPrefix = '::';

  /**
   * Prefix to use to indicate config values.
   *
   * @var string
   *
   * @see ReversibleConfigDiffer::format().
   */
  protected $valuePrefix = ' : ';

  /**
   * {@inheritdoc}
   */
  public function transform($config, $prefix = '') {
    $lines = [];

    $associativeConfig = array_keys($config) !== range(0, count($config) - 1);

    foreach ($config as $key => $value) {
      if (!$associativeConfig) {
        $key = '-';
      }

      $sectionPrefix = ($prefix) ? $prefix . $this->hierarchyPrefix . $key : $key;
      if (is_array($value) && !empty($value)) {
        $lines[] = $sectionPrefix;
        $newlines = $this->transform($value, $sectionPrefix);
        foreach ($newlines as $line) {
          $lines[] = $line;
        }
      }
      else {
        $lines[] = $sectionPrefix . $this->valuePrefix . $this->stringifyValue($value);
      }
    }

    return $lines;
  }

  /**
   * {@inheritdoc}
   */
  public function reverseTransform(array $configStringLines) {
    $result = [];

    foreach ($configStringLines as $ymlRow) {
      $keyValue = explode(' : ', $ymlRow);

      $keyPath = explode('::', $keyValue[0]);

      $lastKey = array_pop($keyPath);
      $currentElement = &$result;
      foreach ($keyPath as $key) {
        if ($key === '-') {
          $key = count($currentElement) - 1;
        }
        elseif (!isset($currentElement[$key])) {
          $currentElement[$key] = [];
        }

        $currentElement = &$currentElement[$key];
      }

      $value = [];
      if (count($keyValue) === 2) {
        $value = $this->unstringifyValue($keyValue[1]);
      }

      if ($lastKey === '-') {
        $currentElement[] = $value;
      }
      else {
        $currentElement[$lastKey] = $value;
      }

    }

    return $result;
  }

  /**
   * Get string representation of value in format that it can be un-serialized.
   *
   * @param mixed $value
   *   Value that should be serialized.
   *
   * @return string
   *   Return string representation of value.
   */
  protected function stringifyValue($value) {
    return serialize($value);
  }

  /**
   * Get correct value from string representation of it.
   *
   * @param string $value
   *   String value.
   *
   * @return mixed
   *   Returns value.
   */
  protected function unstringifyValue($value) {
    return unserialize($value);
  }

}
