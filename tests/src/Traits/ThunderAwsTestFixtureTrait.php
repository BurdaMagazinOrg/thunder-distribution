<?php

namespace Drupal\Tests\thunder\Traits;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Site\Settings;
use GuzzleHttp\Client;

/**
 * Trait to download test fixtures from AWS.
 *
 * Objects should be uploaded to the thunder-public bucket and placed in the
 * test_fixtures directory. To enable the SHA1 checking the object should have
 * the "x-amz-meta-sha" metadata value set to the SHA1 of the file. This
 * prevents unnecessary downloads of the file.
 */
trait ThunderAwsTestFixtureTrait {

  /**
   * Gets a test fixture from AWS.
   *
   * @param string $filename
   *   The test fixture filename.
   *
   * @return string
   *   The local path to the test fixture.
   */
  protected function getTestFixture($filename) {
    // Statically cache to prevent unnecessary requests.
    static $files = [];
    if (!isset($files[$filename])) {
      $local_dir = sys_get_temp_dir() . '/thunder_test_fixtures';
      @mkdir($local_dir);
      $local = $local_dir . '/' . $filename;
      $remote = 'https://s3-eu-west-1.amazonaws.com/thunder-public-files/test_fixtures/' . $filename;

      $client = $this->getHttpClient();
      if (!file_exists($local) || sha1_file($local) !== $client->head($remote)->getHeaderLine('x-amz-meta-sha')) {
        $client->get($remote, ['sink' => $local]);
      }
      $files[$filename] = $local;
    }
    return $files[$filename];
  }

  /**
   * Creates a HTTP client.
   *
   * Does not use the container because it is not always available in testing.
   *
   * @return \GuzzleHttp\Client
   *   The HTTP client.
   */
  protected function getHttpClient() {
    $default_config = [
      // Security consideration: we must not use the certificate authority
      // file shipped with Guzzle because it can easily get outdated if a
      // certificate authority is hacked. Instead, we rely on the certificate
      // authority file provided by the operating system which is more likely
      // going to be updated in a timely fashion. This overrides the default
      // path to the pem file bundled with Guzzle.
      'verify' => TRUE,
      'timeout' => 30,
      'headers' => [
        'User-Agent' => 'Drupal/' . \Drupal::VERSION . ' (+https://www.drupal.org/) ' . \GuzzleHttp\default_user_agent(),
      ],
      // Security consideration: prevent Guzzle from using environment variables
      // to configure the outbound proxy.
      'proxy' => [
        'http' => NULL,
        'https' => NULL,
        'no' => [],
      ],
    ];

    $config = NestedArray::mergeDeep($default_config, Settings::get('http_client_config', []));

    return new Client($config);
  }

}
