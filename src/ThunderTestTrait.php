<?php

namespace Drupal\thunder;

use Drupal\Component\Utility\Crypt;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\Request;

/**
 * Use this trait to reuse an existing database.
 */
trait ThunderTestTrait {

  /**
   * {@inheritdoc}
   */
  public function installDrupal() {
    $this->initUserSession();
    $this->prepareSettings();
    $this->doInstall();
    $this->initSettings();
    $request = Request::createFromGlobals();
    $container = $this->initKernel($request);
    $this->initConfig($container);
    $this->installModulesFromClassProperty($container);
    $this->rebuildAll();
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareSettings() {
    parent::prepareSettings();

    // Remember the profile which was used.
    $settings['settings']['install_profile'] = (object) [
      'value' => $this->profile,
      'required' => TRUE,
    ];
    // Generate a hash salt.
    $settings['settings']['hash_salt'] = (object) [
      'value'    => Crypt::randomBytesBase64(55),
      'required' => TRUE,
    ];

    // Since the installer isn't run, add the database settings here too.
    $settings['databases']['default'] = (object) [
      'value' => Database::getConnectionInfo(),
      'required' => TRUE,
    ];

    $this->writeSettings($settings);
  }

  /**
   * {@inheritdoc}
   */
  protected function doInstall() {

    if (!empty($_SERVER['thunderDumpFile'])) {
      $file = $_SERVER['thunderDumpFile'];
      // Load the database.
      if (substr($file, -3) == '.gz') {
        $file = "compress.zlib://$file";
      }
      require $file;
    }
    else {
      parent::doInstall();
    }
  }

  /**
   * LogIn with defined role assigned to user.
   *
   * @param string $role
   *   Role name that will be assigned to user.
   */
  protected function logWithRole($role) {
    $editor = $this->drupalCreateUser();
    $editor->addRole($role);
    $editor->save();
    $this->drupalLogin($editor);
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown() {
    /** @var \Drupal\Core\Database\Query\SelectInterface $query */
    $query = \Drupal::database()->select('watchdog', 'w')
      ->fields('w')
      ->condition('severity', 5, '<');

    // Check that there are no warnings in the log after installation.
    // $this->assertEqual($query->countQuery()->execute()->fetchField(), 0);.
    if ($query->countQuery()->execute()->fetchField()) {
      // Output all errors for modules tested.
      $errors = [];
      foreach ($query->execute()->fetchAll() as $row) {
        $errors[] = Unicode::truncate(Html::decodeEntities(strip_tags($this->formatMessage($row))), 256, TRUE, TRUE);
      }
      throw new \Exception(print_r(array_unique($errors), TRUE));
    }

    parent::tearDown();
  }

  /**
   * Formats a database log entry.
   *
   * @param object $row
   *   Database row object.
   *
   * @return bool|\Drupal\Core\StringTranslation\TranslatableMarkup|string
   *   String of the database row.
   */
  protected function formatMessage($row) {
    // Check for required properties.
    if (isset($row->message, $row->variables)) {
      $variables = @unserialize($row->variables);
      // Messages without variables or user specified text.
      if ($variables === NULL) {
        $message = Xss::filterAdmin($row->message);
      }
      elseif (!is_array($variables)) {
        $message = t('Log data is corrupted and cannot be unserialized: @message', ['@message' => Xss::filterAdmin($row->message)]);
      }
      // Message to translate with injected variables.
      else {
        // @codingStandardsIgnoreStart
        $message = t(Xss::filterAdmin($row->message), $variables);
        // @codingStandardsIgnoreEnd
      }
    }
    else {
      $message = FALSE;
    }
    return $message;
  }

}
