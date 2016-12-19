<?php

namespace Drupal\thunder_media;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\user\SharedTempStoreFactory;
use Drupal\Component\Utility\DiffArray;

/**
 * Helper class to update configuration.
 */
class Updater {

  /**
   * Site configFactory object.
   *
   * @var ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Temp store factory.
   *
   * @var SharedTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Constructs the PathBasedBreadcrumbBuilder.
   *
   * @param \Drupal\user\SharedTempStoreFactory $tempStoreFactory
   *   A temporary key-value store service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory service.
   */
  public function __construct(SharedTempStoreFactory $tempStoreFactory, ConfigFactoryInterface $configFactory) {
    $this->tempStoreFactory = $tempStoreFactory;
    $this->configFactory = $configFactory;
  }

  /**
   * Update entity browser configuration.
   *
   * @param string $browser
   *   Id of the entity browser.
   * @param array $configuration
   *   Configuration array to update.
   * @param array $oldConfiguration
   *   Only if current config is same like old config we are updating.
   *
   * @return bool
   *   Indicates if config was updated or not.
   */
  public function updateEntityBrowserConfig($browser, array $configuration, array $oldConfiguration = []) {

    $ebConfig = $this->configFactory
      ->getEditable('entity_browser.browser.' . $browser);

    $config = $ebConfig->get();

    if (!empty($oldConfiguration) && DiffArray::diffAssocRecursive($oldConfiguration, $config)) {
      return FALSE;
    }

    $ebConfig->setData(NestedArray::mergeDeep($config, $configuration));
    $ebConfig->save();

    // Update entity browser edit form.
    $entityBrowserConfig = $this->tempStoreFactory
      ->get('entity_browser.config');

    $storage = $entityBrowserConfig->get($browser);

    if (!empty($storage)) {

      foreach ($configuration as $key => $value) {

        $part = $storage['entity_browser']->getPluginCollections()[$key];

        $part->setConfiguration(NestedArray::mergeDeep($part->getConfiguration(), $value));

      }

      $entityBrowserConfig->set($browser, $storage);
    }
    return TRUE;
  }

}
