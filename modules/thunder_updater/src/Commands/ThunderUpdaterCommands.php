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
   * @param string $patchFilePath
   *   Filename of generated update.
   * @param string $moduleList
   *   List of modules, leave empty for all.
   *
   * @return array
   *   Message to output.
   *
   * @command thunder:updater:generate:update
   *
   * @usage
   *   drush thunder-updater-generate-update mypatchfile.yml --filter=/thunder/
   *   Uses all modules containing thunder in their name to generate the update
   *   file
   * @validate-module-enabled thunder_updater
   * @aliases thunder-updater-generate-update
   */
  public function updaterGenerateUpdate($patchFilePath, $moduleList = '') {
    /** @var \Drupal\thunder_updater\ConfigHandler $configHandler */
    $configHandler = \Drupal::service('thunder_updater.config_handler');

    if ($moduleList) {
      $modules = explode(',', $moduleList);
    }
    else {
      $modules = array_filter(\Drupal::moduleHandler()->getModuleList(), function (Extension $extension) {
        return ($extension->getType() == 'module');
      });
      $modules = array_keys($modules);
    }
    if ($regex = drush_get_option('filter', '')) {
      $modules = array_filter($modules, function ($module) use ($regex) {
        return preg_match($regex, $module);
      });
    }

    $patchFile = $configHandler->generatePatchFile($modules);
    $message = dt('There are no changes that should be exported.');
    if (!empty($patchFile)) {
      file_put_contents($patchFilePath, $patchFile);
      $message = dt('Patch file is successfully generated.');
    }

    return [
      $message,
    ];
  }

}
