<?php

/**
 * @file
 * Contains the code to generate the custom drush commands for module.
 */

/**
 * Implements hook_drush_command().
 */
function thunder_updater_drush_command() {
  $items = array();

  $items['thunder-updater-generate-patch'] = [
    'description' => 'Generate patch for module configuration.',
    'arguments' => [
      'module' => 'Module name',
      'version' => 'Version name for patch. (fe. 8.x-1.x-beta11-beta10)',
      'type' => 'Type of patch can be [normal|reverse]. Default: reverse',
    ],
    'drupal dependencies' => ['thunder_updater'],
    'aliases' => ['tugp'],
    'required-arguments' => 2,
    'examples' => [
      'drush thunder-updater-generate-patch thunder_media 8.x-1.x-beta11-beta10' => 'Generate reverse patch between beta11 and beta10 version.',
    ],
    'core' => ['8+'],
    'outputformat' => [
      'default' => 'list',
    ],
  ];

  $items['thunder-updater-apply-patch'] = [
    'description' => 'Generate patch for module configuration.',
    'arguments' => [
      'module' => 'Module name',
      'version' => 'Version name for patch. (fe. 8.x-1.x-beta11-beta10)',
    ],
    'drupal dependencies' => ['thunder_updater'],
    'aliases' => ['tuap'],
    'required-arguments' => 2,
    'examples' => [
      'drush thunder-updater-apply-patch thunder_media 8.x-1.x-beta11-beta10' => 'Apply patch for update from version beta10 to beta11.',
    ],
    'core' => ['8+'],
    'outputformat' => [
      'default' => 'list',
    ],
  ];

  return $items;
}

/**
 * Handler for "thunder-updater-generate-patch" drush command.
 */
function drush_thunder_updater_generate_patch($module, $version, $type = 'reverse') {
  /** @var \Drupal\thunder_updater\Updater $updater */
  $updater = Drupal::service('thunder_updater.updater');

  $fileName = $updater->generatePatch($module, $version, $type);
  $message = ($fileName) ? (dt('Patch file is generated at') . ': ' . $fileName) : dt('There are no changes that can be exported.');

  return [
    $message,
  ];
}

/**
 * Handler for "thunder-updater-apply-patch" drush command.
 */
function drush_thunder_updater_apply_patch($module, $version) {
  /** @var \Drupal\thunder_updater\Updater $updater */
  $updater = Drupal::service('thunder_updater.updater');

  $report = [];
  try {
    $updateReport = $updater->applyPatch($module, $version);

    foreach ($updateReport as $updateEntry) {
      $report[] = $updateEntry['action'] . ': ' . $updateEntry['config'];
    }
  }
  catch (Exception $e) {
    $report[] = dt('Exception occurred with message') . ': ' . $e->getMessage();
  }

  if (empty($report)) {
    $report[] = dt('There is nothing to be applied by this patch.');
  }

  return $report;
}
