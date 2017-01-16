<?php

namespace Drupal\thunder_updater;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\user\SharedTempStoreFactory;
use Drupal\Component\Utility\DiffArray;
use Drupal\checklistapi\ChecklistapiChecklist;

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

    // Check that configuration exists before executing update.
    if (empty($config)) {
      return FALSE;
    }

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

  /**
   * Checks one bulletpoint on a checklist.
   *
   * @param string $name
   *   Name of the bulletpoint.
   */
  public function checkListPoint($name) {

    /** @var Drupal\Core\Config\Config $thunderUpdaterConfig */
    $thunderUpdaterConfig = $this->configFactory
      ->getEditable('checklistapi.progress.thunder_updater');

    if ($thunderUpdaterConfig && !$thunderUpdaterConfig->get(ChecklistapiChecklist::PROGRESS_CONFIG_KEY . ".$name")) {

      $user = \Drupal::currentUser()->id();

      $thunderUpdaterConfig
        ->set(ChecklistapiChecklist::PROGRESS_CONFIG_KEY . ".$name", [
          '#completed' => time(),
          '#uid' => $user,
        ])
        ->set(ChecklistapiChecklist::PROGRESS_CONFIG_KEY . '.#completed_items', $thunderUpdaterConfig->get(ChecklistapiChecklist::PROGRESS_CONFIG_KEY . ".#completed_items") + 1)
        ->set(ChecklistapiChecklist::PROGRESS_CONFIG_KEY . '.#changed', time())
        ->set(ChecklistapiChecklist::PROGRESS_CONFIG_KEY . '.#changed_by', $user)
        ->save();
    }
  }

  /**
   * Checks all the bulletpoints on a checklist.
   */
  public function checkAllListPoint($status = TRUE) {

    /** @var Drupal\Core\Config\Config $thunderUpdaterConfig */
    $thunderUpdaterConfig = $this->configFactory
      ->getEditable('checklistapi.progress.thunder_updater');

    $thunderUpdaterConfig
      ->set(ChecklistapiChecklist::PROGRESS_CONFIG_KEY . '.#changed', time())
      ->set(ChecklistapiChecklist::PROGRESS_CONFIG_KEY . '.#changed_by', \Drupal::currentUser()
        ->id());

    $checklist = checklistapi_checklist_load('thunder_updater');

    $items = 0;

    foreach ($checklist->items as $versionItems) {
      foreach ($versionItems as $itemName => $item) {

        $exclude = [
          '#title',
          '#description',
          '#weight',
        ];

        if (!in_array($itemName, $exclude)) {

          if ($status) {
            $thunderUpdaterConfig
              ->set(ChecklistapiChecklist::PROGRESS_CONFIG_KEY . ".$itemName", [
                '#completed' => time(),
                '#uid' => \Drupal::currentUser()->id(),
              ]);

            $items++;
          }
          else {
            $thunderUpdaterConfig
              ->set(ChecklistapiChecklist::PROGRESS_CONFIG_KEY . ".$itemName", 0);
          }
        }
      };
    }

    $thunderUpdaterConfig
      ->set(ChecklistapiChecklist::PROGRESS_CONFIG_KEY . '.#completed_items', $items)
      ->save();
  }

}
