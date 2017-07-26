<?php

namespace Drupal\Tests\thunder_updater\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * @covers \Drupal\thunder_updater\Updater
 *
 * @group thunder_updater
 *
 * @package Drupal\Tests\thunder_updater\Kernel
 */
class UpdaterTest extends KernelTestBase {

  protected static $configSchemaCheckerExclusions = [
    'field.storage.node.body',
  ];

  protected static $modules = [
    'config_update',
    'thunder_updater',
    'user',
    'text',
    'field',
    'node',
  ];

  /**
   * Get update definition that should be executed.
   *
   * @return array
   *   Update definition array.
   */
  protected function getUpdateDefinition() {
    return [
      'field.storage.node.body' => [
        'expected_config' => [
          'lost_config' => 'text',
          'settings' => [
            'max_length' => 123,
          ],
          'status' => FALSE,
          'type' => 'text',
        ],
        'update_actions' => [
          'add' => [
            'cardinality' => 1,
          ],
          'change' => [
            'settings' => [],
            'status' => TRUE,
            'type' => 'text_with_summary',
          ],
          'delete' => [
            'lost_config' => 'text',
            'settings' => [
              'max_length' => '123',
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * @covers \Drupal\thunder_updater\Updater::executeUpdate
   */
  public function testExecuteUpdate() {
    /** @var \Drupal\config_update\ConfigRevertInterface $configReverter */
    $configReverter = \Drupal::service('config_update.config_update');
    $configReverter->import('field_storage_config', 'node.body');

    /** @var \Drupal\Core\Config\ConfigFactory $configFactory */
    $configFactory = \Drupal::service('config.factory');
    $config = $configFactory->getEditable('field.storage.node.body');

    $expectedConfigData = $config->get();

    $configData = $config->get();
    $configData['status'] = FALSE;
    $configData['type'] = 'text';
    unset($configData['cardinality']);
    $configData['settings'] = ['max_length' => 123];
    $configData['lost_config'] = 'text';

    $config->setData($configData)->save(TRUE);

    /** @var \Drupal\thunder_updater\Updater $thunderUpdater */
    $thunderUpdater = \Drupal::service('thunder_updater');

    $thunderUpdater->executeUpdate($this->getUpdateDefinition());

    $this->assertEquals($expectedConfigData, $configFactory->get('field.storage.node.body')->get());
  }

}
