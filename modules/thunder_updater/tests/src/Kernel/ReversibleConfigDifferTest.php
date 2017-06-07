<?php

namespace Drupal\Tests\thunder_updater\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Automated tests for ReversibleConfigDiffer class.
 *
 * TODO: Add test for diff() method.
 *
 * @group thunder_updater
 *
 * @covers \Drupal\thunder_updater\ReversibleConfigDifferTest
 */
class ReversibleConfigDifferTest extends KernelTestBase {

  protected static $modules = ['config_update', 'thunder_updater', 'user'];

  /**
   * @covers \Drupal\thunder_updater\ReversibleConfigDiffer::same
   *
   * @param array $configOne
   *   First configuration.
   * @param array $configTwo
   *   Second configuration.
   * @param bool $expected
   *   Expected result of checking if configs are same.
   *
   * @dataProvider sameDataProvider
   */
  public function testSame(array $configOne, array $configTwo, $expected) {
    /** @var \Drupal\thunder_updater\ReversibleConfigDiffer $configDiffer */
    $configDiffer = \Drupal::service('thunder_updater.config_differ');

    $result = $configDiffer->same($configOne, $configTwo);

    $this->assertEquals($expected, $result);
  }

  /**
   * Data provider for testing of same() method.
   *
   * @return array
   *   Test data with full name + type and name of configuration.
   */
  public function sameDataProvider() {
    return [
      [
        [
          'uuid' => '1234-5678-90',
          '_core' => 'core_id_1',
          'id' => 'test.config.id',
          'short_text' => 'en',
          'long_text' => 'Automated tests for the ConfigDiffTransformer service.',
          'true_value' => TRUE,
          'false_value' => FALSE,
          'nested_array' => [
            'flat_array' => [
              'value2',
              'value1',
              'value3',
            ],
            'custom_key' => 'value',
          ],
          'empty_array' => [],
          'empty_string' => '',
          'null_value' => NULL,
        ],
        [
          'uuid' => '09-876504321',
          '_core' => 'core_id_2',
          'id' => 'test.config.id',
          'short_text' => 'en',
          'long_text' => 'Automated tests for the ConfigDiffTransformer service.',
          'true_value' => TRUE,
          'false_value' => FALSE,
          'nested_array' => [
            'flat_array' => [
              'value2',
              'value1',
              'value3',
            ],
            'custom_key' => 'value',
          ],
          'empty_array' => [],
          'empty_string' => '',
          'null_value' => NULL,
        ],
        TRUE,
      ],
      [
        [
          'uuid' => '1234-5678-90',
          '_core' => 'core_id_1',
          'id' => 'test.config.id',
          'short_text' => 'en',
          'long_text' => 'Automated tests for the ConfigDiffTransformer service.',
          'true_value' => TRUE,
          'false_value' => FALSE,
          'nested_array' => [
            'flat_array' => [
              'value2',
              'value1',
              'value3',
            ],
            'custom_key' => 'value1',
          ],
          'empty_array' => [],
          'empty_string' => '',
          'null_value' => NULL,
        ],
        [
          'uuid' => '09-876504321',
          '_core' => 'core_id_2',
          'id' => 'test.config.id',
          'short_text' => 'en',
          'long_text' => 'Automated tests for the ConfigDiffTransformer service.',
          'true_value' => TRUE,
          'false_value' => FALSE,
          'nested_array' => [
            'flat_array' => [
              'value2',
              'value1',
              'value3',
            ],
            'custom_key' => 'value2',
          ],
          'empty_array' => [],
          'empty_string' => '',
          'null_value' => NULL,
        ],
        FALSE,
      ],
    ];
  }

}
