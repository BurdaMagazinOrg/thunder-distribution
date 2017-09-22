<?php

namespace Drupal\thunder_updater;

use Drupal\config_update\ConfigDiffer;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Overwrite of config updater differ.
 *
 * Normalization is changed so that it can be 2-way normalization, not 1-way.
 * Also format is adjusted to better supports converting from/to config array.
 *
 * TODO:
 * - (de)normalization should be solved properly. It does not support option
 *   with multiple assoc arrays in array. In Yaml empty line with '-' and then
 *   parameters after it.
 *
 * @package Drupal\thunder_updater
 */
class ReversibleConfigDiffer extends ConfigDiffer {

  /**
   * Config diff transformer service.
   *
   * @var \Drupal\thunder_updater\ConfigDiffTransformer
   */
  protected $configDiffTransformer;

  /**
   * ConfigDiffer constructor.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation
   *   String translation service.
   * @param \Drupal\thunder_updater\ConfigDiffTransformer $config_diff_transformer
   *   Configuration transformer for diffing.
   * @param string[] $ignore
   *   Config components to ignore.
   * @param string $hierarchy_prefix
   *   Prefix to use in diffs for array hierarchy.
   * @param string $value_prefix
   *   Prefix to use in diffs for array value.
   */
  public function __construct(TranslationInterface $translation, ConfigDiffTransformer $config_diff_transformer, array $ignore = ['uuid', '_core'], $hierarchy_prefix = '::', $value_prefix = ' : ') {
    parent::__construct($translation, $ignore, $hierarchy_prefix, $value_prefix);

    $this->configDiffTransformer = $config_diff_transformer;
  }

  /**
   * Strip some generic fields (uuid, _core).
   *
   * @param mixed $data
   *   Configuration array.
   *
   * @return mixed
   *   Returns stripped configuration.
   */
  protected function stripIgnore($data) {
    foreach ($this->ignore as $element) {
      unset($data[$element]);
    }

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  protected function normalize($config) {
    // Recursively normalize remaining elements, if they are arrays.
    foreach ($config as $key => $value) {
      if (is_array($value)) {
        $config[$key] = $this->normalize($value);
      }
    }

    // Sort and return.
    ksort($config);

    return $config;
  }

  /**
   * {@inheritdoc}
   */
  protected function format(array $config, $prefix = '') {
    return $this->configDiffTransformer->transform($config, $prefix);
  }

  /**
   * {@inheritdoc}
   */
  public function same($source, $target) {
    $source = $this->stripIgnore($source);
    $target = $this->stripIgnore($target);

    return parent::same($source, $target);
  }

  /**
   * {@inheritdoc}
   */
  public function diff($source, $target) {
    $source = $this->stripIgnore($source);
    $target = $this->stripIgnore($target);

    return parent::diff($source, $target);
  }

}
