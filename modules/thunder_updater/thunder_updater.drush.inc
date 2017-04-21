<?php

/**
 * @file
 * Contains the code to generate the custom drush commands for module.
 */

/**
 * Implements hook_drush_command().
 */
function thunder_updater_drush_command() {
  $items = [];

  $items['thunder-updater-generate-raw-data'] = [
    'description' => 'Generate update for module configuration.',
    'arguments' => [
      'module' => 'Module name',
      'destination-file' => 'Destination file',
    ],
    'drupal dependencies' => ['thunder_updater'],
    'required-arguments' => 2,
    'examples' => [
      'drush thunder-updater-generate-raw-data thunder_media raw-data.txt' => 'Generate patch to update configuration from current installed configuration to configuration provided in files.',
    ],
    'core' => ['8+'],
    'outputformat' => [
      'default' => 'list',
    ],
  ];

  return $items;
}

/**
 * Handler for "thunder-updater-generate-raw-data" drush command.
 *
 * @param string $module
 *   Module name.
 * @param string $destinationFilename
 *   Destination file name.
 *
 * @return array
 *   Returns messages for output.
 */
function drush_thunder_updater_generate_raw_data($module, $destinationFilename) {
  /** @var \Drupal\thunder_updater\ConfigHandler $configHandler */
  $configHandler = Drupal::service('thunder_updater.config_handler');

  $successful = $configHandler->generateRawData($module, $destinationFilename);
  $message = ($successful) ? dt('Patch file is successfully generated.') : dt('There are no changes that should be exported.');

  return [
    $message,
  ];
}
