<?php

namespace Drupal\thunder_updater\Commands;

use Drush\Commands\DrushCommands;

/**
 * Thunder updater drush commands.
 */
class ThunderUpdaterCommands extends DrushCommands {

  /**
   * Generate update definition for module configuration.
   *
   * @param string $module
   *   Module name.
   * @param string $updateName
   *   Update name.
   *
   * @command thunder:updater:generate:update
   *
   * @usage drush thunder-updater-generate-update thunder_media
   *   thunder_media__update_8099 Generate patch to update configuration from
   *   current installed configuration to configuration provided in files.
   * @validate-module-enabled thunder_updater
   * @aliases thunder-updater-generate-update
   */
  public function updaterGenerateUpdate($module, $updateName) {
    // @codingStandardsIgnoreStart
    /** @var \Drupal\thunder_updater\ConfigHandler $configHandler */
    $configHandler = \Drupal::service('thunder_updater.config_handler');
    // @codingStandardsIgnoreEnd
    $successful = $configHandler->generatePatchFile($module, $updateName);
    $message = ($successful) ? dt('Patch file is successfully generated.') : dt('There are no changes that should be exported.');

    return [
      $message,
    ];
  }

}
