<?php

namespace Drupal\Tests\thunder_media\Functional;

use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\file\Entity\File;
use Drupal\thunder\ThunderBaseTest;

/**
 * Tests for transliteration of file names.
 *
 * @group Thunder
 */
class FilenameTransliterationTest extends ThunderBaseTest {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['file_test', 'file'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {

    parent::setUp();

    $this->config('thunder_media.settings')
      ->set('enable_filename_transliteration', TRUE)
      ->save();
  }

  /**
   * Test for transliteration of file name.
   */
  public function testFileTransliteration() {

    $account = $this->drupalCreateUser(['access site reports']);
    $this->drupalLogin($account);

    $original = drupal_get_path('module', 'simpletest') . '/files';

    file_unmanaged_copy($original . '/image-1.png', PublicStream::basePath() . '/foo°.png');

    // Upload with replace to guarantee there's something there.
    $edit = [
      'file_test_replace' => FILE_EXISTS_RENAME,
      'files[file_test_upload]' => drupal_realpath('public://foo°.png'),
    ];
    $this->drupalPostForm('file-test/upload', $edit, t('Submit'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseContains('You WIN!');

    $this->assertTrue(file_exists('temporary://foodeg.png'));

    $max_fid_after = db_query('SELECT MAX(fid) AS fid FROM {file_managed}')->fetchField();

    $file = File::load($max_fid_after);

    $this->assertSame('foodeg.png', $file->getFilename());
    $this->assertSame('temporary://foodeg.png', $file->getFileUri());

  }

}
