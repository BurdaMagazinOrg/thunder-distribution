<?php

namespace Drupal\thunder_updater;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * Helper service for logging in update hooks provided by Thunder.
 *
 * It provides output of logs to HTML, when update is executed over update.php.
 * And it also provides output of logs for Drush command, when update is
 * executed over drush command.
 *
 * @package Drupal\thunder_updater
 */
class UpdateLogger extends AbstractLogger {

  /**
   * Container for logs.
   *
   * @var array
   */
  protected $logs = [];

  /**
   * Mapping from Psr to Drush log level.
   *
   * @var array
   */
  protected static $psrDrushLogLevels = [
    LogLevel::INFO => 'ok',
  ];

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []) {
    $this->logs[] = [$level, $message, $context];
  }

  /**
   * Clear logs and returns currenlty collected logs.
   *
   * @return array
   *   Returns collected logs, since last clear.
   */
  protected function cleanLogs() {
    $logs = $this->logs;
    $this->logs = [];

    return $logs;
  }

  /**
   * Output logs in format suitable for HTML and clear logs too.
   *
   * @return string
   *   Returns HTML.
   */
  protected function outputHtml() {
    $fullLog = '';

    $currentLogs = $this->cleanLogs();
    foreach ($currentLogs as $logEntry) {
      $fullLog .= $logEntry[1] . '<br /><br />';
    }

    return $fullLog;
  }

  /**
   * Output logs in format suitable for drush command and clear logs too.
   *
   * @throws \RuntimeException
   *   When method is not executed in drush environment.
   */
  protected function outputDrush() {
    // Check for "drush_log" should be done by caller.
    if (!function_exists('drush_log')) {
      throw new \RuntimeException('Required global method "drush_log" is not available.');
    }

    $currentLogs = $this->cleanLogs();
    foreach ($currentLogs as $logEntry) {
      if (isset(static::$psrDrushLogLevels[$logEntry[0]])) {
        $drushLogLevel = static::$psrDrushLogLevels[$logEntry[0]];
      }
      else {
        $drushLogLevel = $logEntry[0];
      }

      drush_log($logEntry[1], $drushLogLevel);
    }
  }

  /**
   * Output log result, depending on channel used and clean log.
   *
   * @return string
   *   Returns HTML string in case of non drush execution.
   */
  public function output() {
    if (function_exists('drush_log') && PHP_SAPI === 'cli') {
      $this->outputDrush();

      return '';
    }

    return $this->outputHtml();
  }

}
