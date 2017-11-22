<?php

/**
 * @file
 * Contains the code to generate the custom drush commands for thunder_updater.
 */

use Drupal\Core\Extension\Extension;

/**
 * Implements hook_drush_command().
 */
function thunder_updater_drush_command() {
  $items = [];

  $items['thunder-updater-generate-update'] = [
    'description' => 'Generate update definition for module configuration.',
    'arguments' => [
      'filename' => 'Filename of generated update',
      'modules' => 'List of modules, leave empty for all',
    ],
    'options' => [
      'filter' => 'Regex to filter the module list',
    ],
    'drupal dependencies' => ['thunder_updater'],
    'required-arguments' => 1,
    'examples' => [
      'drush thunder-updater-generate-update mypatchfile.yml --filter=/thunder/' => 'Uses all modules containing thunder in their name to generate the update file.',
      'drush thunder-updater-generate-update mypatchfile.yml thunder_article' => 'Generates the update file out of the thunder_article module.',
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
 * @param string $patchFilePath
 *   Filepath where the patch file will be created.
 * @param string $moduleList
 *   Comma-seperated.
 *
 * @return array
 *   Returns messages for output.
 */
function drush_thunder_updater_generate_update($patchFilePath, $moduleList = '') {

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
