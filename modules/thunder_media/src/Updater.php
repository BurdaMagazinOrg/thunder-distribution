<?php

namespace Drupal\thunder_media;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\user\SharedTempStoreFactory;

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
   */
  public function updateEntityBrowserConfig($browser, $configuration) {

    $ebConfig = $this->configFactory
      ->getEditable('entity_browser.browser.' . $browser);

    $config = $ebConfig->get();

    $ebConfig->setData(NestedArray::mergeDeep($config, $configuration));
    $ebConfig->save();

    // Update entity browser edit form.
    $entityBrowserConfig = $this->tempStoreFactory
      ->get('entity_browser.config');

    $storage = $entityBrowserConfig->get($browser);

    if ($storage) {

      foreach ($configuration as $key => $value) {

        $reflection = new \ReflectionClass($storage['entity_browser']);
        $property = $reflection->getProperty($key);

        if (!$property->isProtected()) {

          $storage['entity_browser']->$key = (NestedArray::mergeDeep($storage['entity_browser']->$key, $value));
        }
        else {

          $part = call_user_func([
            $storage['entity_browser'],
            'get' . lcfirst($key),
          ]);

          $part->setConfiguration(NestedArray::mergeDeep($part->getConfiguration(), $value));
        }

        $entityBrowserConfig->set($browser, $storage);

      }
    }
  }

}
