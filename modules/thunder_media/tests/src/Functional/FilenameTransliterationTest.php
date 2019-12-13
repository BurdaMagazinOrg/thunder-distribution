<?php

namespace Drupal\Tests\thunder_media\Functional;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\file\Entity\File;
use Drupal\Tests\thunder\Functional\ThunderTestBase;

/**
 * Tests for transliteration of file names.
 *
 * @group Thunder
 */
class FilenameTransliterationTest extends ThunderTestBase {

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

    \Drupal::service('file_system')->copy($original . '/image-1.png', PublicStream::basePath() . '/foo°.png');

    // Upload with replace to guarantee there's something there.
    $edit = [
      'file_test_replace' => FileSystemInterface::EXISTS_RENAME,
      'files[file_test_upload]' => \Drupal::service('file_system')->realpath('public://foo°.png'),
    ];
    $this->drupalPostForm('file-test/upload', $edit, $this->t('Submit'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseContains('You WIN!');

    $this->assertTrue(file_exists('temporary://foodeg.png'));

    $max_fid_after = \Drupal::database()->query('SELECT MAX(fid) AS fid FROM {file_managed}')->fetchField();

    $file = File::load($max_fid_after);

    $this->assertSame('foodeg.png', $file->getFilename());
    $this->assertSame('temporary://foodeg.png', $file->getFileUri());

  }

}
