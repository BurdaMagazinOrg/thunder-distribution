<?php

namespace Drupal\Tests\thunder_updater\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Automated tests for ReversibleConfigDiffer class.
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

  /**
   * @covers \Drupal\thunder_updater\ReversibleConfigDiffer::diff
   */
  public function testDiff() {
    /** @var \Drupal\thunder_updater\ReversibleConfigDiffer $configDiffer */
    $configDiffer = \Drupal::service('thunder_updater.config_differ');

    $configOne = [
      'uuid' => '1234-5678-90',
      '_core' => 'core_id_1',
      'id' => 'test.config.id',
      'short_text' => 'en',
      'true_value' => TRUE,
      'nested_array' => [
        'flat_array' => [
          'value2',
          'value1',
          'value3',
        ],
        'custom_key' => 'value',
      ],
      'main_data' => [
        'uuid' => '1234-5678-90',
      ],
    ];

    $configTwo = [
      'uuid' => '09-8765-4321',
      '_core' => 'core_id_2',
      'id' => 'test.config.id',
      'short_text' => 'en',
      'true_value' => TRUE,
      'nested_array' => [
        'flat_array' => [
          'value2',
          'value3',
        ],
        'custom_key' => 'value',
        'new_custom_key' => 'value',
      ],
      'main_data' => [
        'uuid' => '09-8765-4321',
      ],
    ];

    $edits = $configDiffer->diff($configOne, $configTwo)->getEdits();

    $expectedEdits = [
      [
        'copy' => [
          'orig' => ['id : s:14:"test.config.id";', 'main_data'],
          'closing' => ['id : s:14:"test.config.id";', 'main_data'],
        ],
      ],
      [
        'change' => [
          'orig' => ['main_data::uuid : s:12:"1234-5678-90";'],
          'closing' => ['main_data::uuid : s:12:"09-8765-4321";'],
        ],
      ],
      [
        'copy' => [
          'orig' => [
            'nested_array',
            'nested_array::custom_key : s:5:"value";',
            'nested_array::flat_array',
            'nested_array::flat_array::- : s:6:"value2";',
          ],
          'closing' => [
            'nested_array',
            'nested_array::custom_key : s:5:"value";',
            'nested_array::flat_array',
            'nested_array::flat_array::- : s:6:"value2";',
          ],
        ],
      ],
      [
        'delete' => [
          'orig' => ['nested_array::flat_array::- : s:6:"value1";'],
          'closing' => FALSE,
        ],
      ],
      [
        'copy' => [
          'orig' => ['nested_array::flat_array::- : s:6:"value3";'],
          'closing' => ['nested_array::flat_array::- : s:6:"value3";'],
        ],
      ],
      [
        'add' => [
          'orig' => FALSE,
          'closing' => ['nested_array::new_custom_key : s:5:"value";'],
        ],
      ],
      [
        'copy' => [
          'orig' => ['short_text : s:2:"en";', 'true_value : b:1;'],
          'closing' => ['short_text : s:2:"en";', 'true_value : b:1;'],
        ],
      ],
    ];

    $this->assertEquals(count($expectedEdits), count($edits));

    foreach ($edits as $index => $diffOp) {
      /** @var \Drupal\Component\Diff\Engine\DiffOp $diffOp */

      $this->assertEquals($expectedEdits[$index][$diffOp->type]['orig'], $diffOp->orig);
      $this->assertEquals($expectedEdits[$index][$diffOp->type]['closing'], $diffOp->closing);
    }
  }

}
