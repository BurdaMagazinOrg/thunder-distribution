<?php

/**
 * @file
 * Contains the code to generate the custom drush commands for thunder_updater.
 */

/**
 * Implements hook_drush_command().
 */
function thunder_updater_drush_command() {
  $items = [];

  $items['thunder-updater-generate-update'] = [
    'description' => 'Generate update definition for module configuration.',
    'arguments' => [
      'module' => 'Module name',
      'update-name' => 'Update name',
    ],
    'drupal dependencies' => ['thunder_updater'],
    'required-arguments' => 2,
    'examples' => [
      'drush thunder-updater-generate-update thunder_media thunder_media__update_8201' => 'Generate patch to update configuration from current installed configuration to configuration provided in files.',
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
 * @param string $updateName
 *   Update name.
 *
 * @return array
 *   Returns messages for output.
 */
function drush_thunder_updater_generate_update($module, $updateName) {
  /** @var \Drupal\thunder_updater\ConfigHandler $configHandler */
  $configHandler = Drupal::service('thunder_updater.config_handler');

  $successful = $configHandler->generatePatchFile($module, $updateName);
  $message = ($successful) ? dt('Patch file is successfully generated.') : dt('There are no changes that should be exported.');

  return [
    $message,
  ];
}
