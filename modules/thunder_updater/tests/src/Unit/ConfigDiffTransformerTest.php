<?php

namespace Drupal\Tests\thunder_updater\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\thunder_updater\ConfigDiffTransformer;

/**
 * Automated tests for the 'thunder_updater.config_diff_transformer' service.
 *
 * @group thunder_updater
 */
class ConfigDiffTransformerTest extends UnitTestCase {

  /**
   * Test transforming of configuration to array of strings.
   *
   * @param array $config
   *   Configuration array that should be transformed.
   * @param array $expected
   *   Expected result of "transform" execution.
   *
   * @dataProvider transformDataProvider
   */
  public function testTransform(array $config, array $expected) {
    $transformer = new ConfigDiffTransformer();
    $result = $transformer->transform($config);

    $this->assertEquals($expected, $result);
  }

  /**
   * Test transforming of configuration to array of strings.
   *
   * @param array $expected
   *   Expected result of "reverseTransform" execution.
   * @param array $transformedConfig
   *   Transformed configuration array that should be reversibly transformed.
   *
   * @dataProvider transformDataProvider
   */
  public function testReverseTransform(array $expected, array $transformedConfig) {
    $transformer = new ConfigDiffTransformer();
    $result = $transformer->reverseTransform($transformedConfig);

    $this->assertEquals($expected, $result);
  }

  /**
   * Data provider for transform and reverseTransform methods test.
   *
   * @return array
   *   Return test cases for testProcessRiddleResponse.
   */
  public function transformDataProvider() {
    return [
      [[], []],
      [
        [
          'uuid' => '1234-5678-90',
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
          'uuid : s:12:"1234-5678-90";',
          'id : s:14:"test.config.id";',
          'short_text : s:2:"en";',
          'long_text : s:54:"Automated tests for the ConfigDiffTransformer service.";',
          'true_value : b:1;',
          'false_value : b:0;',
          'nested_array',
          'nested_array::flat_array',
          'nested_array::flat_array::- : s:6:"value2";',
          'nested_array::flat_array::- : s:6:"value1";',
          'nested_array::flat_array::- : s:6:"value3";',
          'nested_array::custom_key : s:5:"value";',
          'empty_array : a:0:{}',
          'empty_string : s:0:"";',
          'null_value : N;',
        ],
      ],
    ];
  }

}
