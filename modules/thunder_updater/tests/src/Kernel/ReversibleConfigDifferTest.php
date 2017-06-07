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
   * Important part is that 'uuid' and '_core' are stripped only for base
   * configuration array, not in nested configuration parts.
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
          '1234-5678-90' => [
            'uuid' => '1234-5678-90',
          ],
          'empty_array' => [],
          'empty_string' => '',
          'null_value' => NULL,
        ],
        [
          'uuid' => '09-8765-4321',
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
          '1234-5678-90' => [
            'uuid' => '1234-5678-90',
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
            'custom_key' => 'value',
          ],
          '1234-5678-90' => [
            'uuid' => '1234-5678-90',
          ],
          'empty_array' => [],
          'empty_string' => '',
          'null_value' => NULL,
        ],
        [
          'uuid' => '09-8765-4321',
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
          '1234-5678-90' => [
            'uuid' => '09-8765-4321',
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
