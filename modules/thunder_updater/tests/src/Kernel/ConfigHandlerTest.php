<?php

namespace Drupal\Tests\thunder_updater\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Automated tests for ConfigName class.
 *
 * @group thunder_updater
 *
 * @covers \Drupal\thunder_updater\ConfigHandler
 */
class ConfigHandlerTest extends KernelTestBase {

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
   * Returns update defintion data.
   *
   * @return string
   *   Update definition Yaml string.
   */
  protected function getUpdateDefinition() {
    return 'field.storage.node.body:' . PHP_EOL .
      '  expected_config:' . PHP_EOL .
      '    lost_config: text' . PHP_EOL .
      '    settings:' . PHP_EOL .
      '      max_length: 123' . PHP_EOL .
      '    status: false' . PHP_EOL .
      '    type: text' . PHP_EOL .
      '  update_actions:' . PHP_EOL .
      '    add:' . PHP_EOL .
      '      cardinality: 1' . PHP_EOL .
      '    change:' . PHP_EOL .
      '      settings: {  }' . PHP_EOL .
      '      status: true' . PHP_EOL .
      '      type: text_with_summary' . PHP_EOL .
      '    delete:' . PHP_EOL .
      '      lost_config: text' . PHP_EOL .
      '      settings:' . PHP_EOL .
      '        max_length: 123' . PHP_EOL;
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown() {
    $moduleHandler = \Drupal::service('module_handler');
    $dirName = $moduleHandler->getModule('node')->getPath() . '/config/update';
    $fileName = 'thunder_updater__node_test.yml';

    if (is_file($dirName . '/' . $fileName)) {
      unlink($dirName . '/' . $fileName);
    }

    if (is_dir($dirName)) {
      rmdir($dirName);
    }

    parent::tearDown();
  }

  /**
   * @covers \Drupal\thunder_updater\ConfigHandler::generatePatchFile
   */
  public function testGeneratePatchFile() {
    /** @var \Drupal\thunder_updater\ConfigHandler $configHandler */
    $configHandler = \Drupal::service('thunder_updater.config_handler');

    /** @var \Drupal\config_update\ConfigRevertInterface $configReverter */
    $configReverter = \Drupal::service('config_update.config_update');
    $configReverter->import('field_storage_config', 'node.body');

    /** @var \Drupal\Core\Config\ConfigFactory $configFactory */
    $configFactory = \Drupal::service('config.factory');
    $config = $configFactory->getEditable('field.storage.node.body');
    $configData = $config->get();
    $configData['status'] = FALSE;
    $configData['type'] = 'text';
    unset($configData['cardinality']);
    $configData['settings'] = ['max_length' => 123];
    $configData['lost_config'] = 'text';

    $config->setData($configData)->save(TRUE);

    // Generate patch after configuration change.
    $data = $configHandler->generatePatchFile(['node']);

    $this->assertEquals($this->getUpdateDefinition(), $data);
  }

}
