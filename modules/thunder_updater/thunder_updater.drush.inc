<?php

/**
 * @file
 * Contains the code to generate the custom drush commands for module.
 */

use Drupal\config_update\ConfigReverter;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\NullStorage;
use Drupal\thunder_updater\Updater;

/**
 * Implements hook_drush_command().
 */
function thunder_updater_drush_command() {
  $items = array();

  $items['thunder-updater-generate-update'] = [
    'description' => 'Generate update for module configuration.',
    'arguments' => [
      'module' => 'Module name',
      'version' => 'Version name for update. (fe. 8.x-1.x-beta10-beta11)',
      'type' => 'Type of patch can be [normal|reverse]. Default: reverse',
    ],
    'options' => [
      'active-dir' => 'Directory with active configuration (new configuration).',
      'base-dir' => 'Directory with base configuration (old configuration).',
    ],
    'drupal dependencies' => ['thunder_updater'],
    'aliases' => ['tugu'],
    'required-arguments' => 2,
    'examples' => [
      'drush thunder-updater-generate-update thunder_media 8.x-1.x-beta10-beta11' => 'Generate reverse patch between beta11 and beta10 version.',
      'drush thunder-updater-generate-update thunder_media 8.x-1.x-rc1 --base-dir=modules/thunder_media/config/install_old_beta11 --active-dir=modules/thunder_media/config/install' => 'Generate reverse patch between rc1 and beta11 version, but files from defined folders are used instead of base and active configuration.',
    ],
    'core' => ['8+'],
    'outputformat' => [
      'default' => 'list',
    ],
  ];

  $items['thunder-updater-update-module'] = [
    'description' => 'Apply update for module configuration.',
    'arguments' => [
      'module' => 'Module name',
      'versions' => 'Version names of updates. (fe. 8.x-1.x-beta10-beta11,8.x-1.x-beta11-rc1)',
    ],
    'drupal dependencies' => ['thunder_updater'],
    'aliases' => ['tuum'],
    'required-arguments' => 2,
    'examples' => [
      'drush thunder-updater-update-module thunder_media 8.x-1.x-beta10-beta11,8.x-1.x-beta11-rc1' => 'Apply updates from version beta10 to rc1 for thunder_media module.',
    ],
    'core' => ['8+'],
    'outputformat' => [
      'default' => 'list',
    ],
  ];

  $items['thunder-updater-update-all'] = [
    'description' => 'Apply update for configuration of all thunder modules.',
    'arguments' => [
      'versions' => 'Version names of updates. (fe. 8.x-1.x-beta10-beta11,8.x-1.x-beta11-rc1)',
    ],
    'drupal dependencies' => ['thunder_updater'],
    'aliases' => ['tuua'],
    'required-arguments' => 1,
    'examples' => [
      'drush thunder-updater-update-all 8.x-1.x-beta10-beta11,8.x-1.x-beta11-rc1' => 'Apply updates from version beta10 to rc1 for all thunder modules.',
    ],
    'core' => ['8+'],
    'outputformat' => [
      'default' => 'list',
    ],
  ];

  return $items;
}

/**
 * Handler for "thunder-updater-generate-update" drush command.
 *
 * @param string $module
 *   Module name.
 * @param string $version
 *   Version name.
 * @param string $type
 *   Type of patch (normal|reverse).
 *
 * @return array
 *   Returns messages for output.
 */
function drush_thunder_updater_generate_update($module, $version, $type = 'reverse') {
  $activeDir = drush_get_option('active-dir', '');
  $baseDir = drush_get_option('base-dir', '');

  if (!empty($baseDir) || !empty($activeDir)) {
    $activeStorage = Drupal::service('config.storage');
    if (!empty($activeDir)) {
      $activeStorage = new FileStorage($activeDir);
    }

    $baseStorage = Drupal::service('config_update.extension_storage');
    if (!empty($baseDir)) {
      $baseStorage = new FileStorage($baseDir);
    }

    $configUpdater = new ConfigReverter(
      Drupal::service('entity.manager'),
      $activeStorage,
      $baseStorage,
      new NullStorage(),
      Drupal::service('config.factory'),
      Drupal::service('event_dispatcher')
    );

    /** @var \Drupal\thunder_updater\Updater $updater */
    $updater = new Updater(
      Drupal::service('thunder_updater.logger'),
      Drupal::service('thunder_updater.config_diff'),
      Drupal::service('config_update.config_list'),
      $configUpdater,
      Drupal::service('thunder_updater.merge_storage'),
      Drupal::service('thunder_updater.merge_config_reverter'),
      Drupal::service('thunder_updater.patch_handler')
    );
  }
  else {
    /** @var \Drupal\thunder_updater\Updater $updater */
    $updater = Drupal::service('thunder_updater.updater');
  }

  $fileName = $updater->generateUpdate($module, $version, $type);
  $message = ($fileName) ? (dt('Patch file is generated at') . ': ' . $fileName) : dt('There are no changes that can be exported.');

  return [
    $message,
  ];
}

/**
 * Handler for "thunder-updater-update-module" drush command.
 *
 * @param string $module
 *   Module name.
 * @param string $versions
 *   Version names separated with comma.
 *
 * @return array
 *   Returns messages for output.
 */
function drush_thunder_updater_update_module($module, $versions) {
  /** @var \Drupal\thunder_updater\Updater $updater */
  $updater = Drupal::service('thunder_updater.updater');

  $report = [];
  try {
    $updateReport = $updater->executeUpdate($module, $versions);

    foreach ($updateReport as $updateEntry) {
      $report[] = $updateEntry['action'] . ': ' . $updateEntry['config'];
    }
  }
  catch (Exception $e) {
    $report[] = dt('Exception occurred with message') . ': ' . $e->getMessage();
  }

  if (empty($report)) {
    $report[] = dt('There is no update that can be applied.');
  }

  return $report;
}

/**
 * Handler for "thunder-updater-update-all" drush command.
 *
 * @param string $versions
 *   Version names separated with comma.
 *
 * @return array
 *   Returns messages for output.
 */
function drush_thunder_updater_update_all($versions) {
  /** @var \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler */
  $moduleHandler = Drupal::service('module_handler');

  $report = [];
  $listOfModules = $moduleHandler->getModuleList();
  foreach ($listOfModules as $module) {
    $moduleName = $module->getName();
    if (strpos($moduleName, 'thunder_') === 0) {
      $report[] = dt('Updating module: ' . $moduleName);

      $singleUpdateReport = drush_thunder_updater_update_module($moduleName, $versions);

      $report = array_merge($report, $singleUpdateReport);
    }
  }

  return $report;
}
