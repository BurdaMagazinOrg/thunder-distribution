<?php

namespace Drupal\paragraph_split_text\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "SCAYT" plugin.
 *
 * @CKEditorPlugin(
 *   id = "splittext",
 *   label = @Translation("Split Text")
 * )
 */
class SplitText extends CKEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return $this->getLibraryPath() . '/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return [
      'SplitTextBefore' => [
        'label' => t('Split Text Before'),
        'image' => $this->getLibraryPath() . '/icons/splittext-before.png',
      ],
      'SplitTextAfter' => [
        'label' => t('Split Text After'),
        'image' => $this->getLibraryPath() . '/icons/splittext-after.png',
      ],
    ];
  }

  /**
   * Returns the path of the javascript files.
   *
   * @return string
   *   Path to javascript files.
   */
  protected function getLibraryPath() {
    $path = drupal_get_path('module', 'paragraph_split_text') . '/js/plugins/splittext';
    return $path;
  }

}
