<?php
/**
 * @file
 * Contains
 */

namespace Drupal\thunder_media\Tests;


use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\thunder\ThunderBaseTest;

/**
 * @group Thunder
 */
class FilenameTransliterationTest extends ThunderBaseTest {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('file_test', 'file');

  protected function setUp() {

    parent::setUp();

    $this->config('thunder_media.settings')
      ->set('enable_filename_transliteration', TRUE)
      ->save();
  }


  public function testFileTransliteration() {

    $account = $this->drupalCreateUser(array('access site reports'));
    $this->drupalLogin($account);

    $original = drupal_get_path('module', 'simpletest') . '/files';

    file_unmanaged_copy($original . '/image-1.png', PublicStream::basePath() . '/foo°.png');

    // Upload with replace to guarantee there's something there.
    $edit = array(
      'file_test_replace' => FILE_EXISTS_RENAME,
      'files[file_test_upload]' => drupal_realpath('public://foo°.png'),
    );
    $this->drupalPostForm('file-test/upload', $edit, t('Submit'));
    $this->assertResponse(200, 'Received a 200 response for posted test file.');
    $this->assertRaw(t('You WIN!'), 'Found the success message.');

    $this->assertTrue(file_exists('temporary://foodeg.png'));

    $max_fid_after = db_query('SELECT MAX(fid) AS fid FROM {file_managed}')->fetchField();

    $file = file_load($max_fid_after);

    $this->assertIdentical('foodeg.png', $file->getFilename());
    $this->assertIdentical('temporary://foodeg.png', $file->getFileUri());

  }
}
