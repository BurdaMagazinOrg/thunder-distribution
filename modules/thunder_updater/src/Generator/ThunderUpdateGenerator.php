<?php

namespace Drupal\thunder_updater\Generator;

use Drupal\Console\Core\Generator\Generator;
use Drupal\Console\Extension\Manager;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\thunder_updater\ConfigHandler;

/**
 * Update hook generator for generate:thunder:update console command.
 *
 * @package Drupal\thunder_updater\Generator
 */
class ThunderUpdateGenerator extends Generator {

  /**
   * Extension manager.
   *
   * @var \Drupal\Console\Extension\Manager
   */
  protected $extensionManager;

  /**
   * Drupal\thunder_updater\ConfigHandler definition.
   *
   * @var \Drupal\thunder_updater\ConfigHandler
   */
  protected $thunderUpdaterConfigHandler;

  /**
   * Drupal\Core\Extension\ModuleHandler definition.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * AuthenticationProviderGenerator constructor.
   *
   * @param \Drupal\Console\Extension\Manager $extension_manager
   *   Extension manager.
   * @param \Drupal\thunder_updater\ConfigHandler $thunder_updater_config_handler
   *   Config handler service.
   * @param \Drupal\Core\Extension\ModuleHandler $module_handler
   *   Module handler service.
   */
  public function __construct(
    Manager $extension_manager,
    ConfigHandler $thunder_updater_config_handler,
    ModuleHandler $module_handler
  ) {
    $this->extensionManager = $extension_manager;
    $this->thunderUpdaterConfigHandler = $thunder_updater_config_handler;
    $this->moduleHandler = $module_handler;
  }

  /**
   * Get update hook function name.
   *
   * @param string $module_name
   *   Module name.
   * @param string $update_number
   *   Update number.
   *
   * @return string
   *   Returns update hook function name.
   */
  protected function getUpdateFunctionName($module_name, $update_number) {
    return $module_name . '_update_' . $update_number;
  }

  /**
   * Generate patch file for listed modules in module defined for command.
   *
   * @param string $module_name
   *   Module name where patch will be placed.
   * @param string $update_number
   *   Update number that will be used.
   * @param string $module_list
   *   Comma separated list of modules.
   * @param string $filter
   *   Regex filter for module names.
   *
   * @return bool
   *   Return if patch file is generated.
   */
  public function generateUpdate($module_name, $update_number, $module_list, $filter) {
    if ($module_list) {
      $modules = explode(',', $module_list);
    }
    else {
      $modules = array_filter($this->moduleHandler->getModuleList(), function (Extension $extension) {
        return ($extension->getType() == 'module');
      });
      $modules = array_keys($modules);
    }

    if ($filter) {
      $modules = array_filter($modules, function ($module) use ($filter) {
        return preg_match($filter, $module);
      });
    }

    // Get patch data and save it into file.
    $patch_data = $this->thunderUpdaterConfigHandler->generatePatchFile($modules);
    if (!empty($patch_data)) {
      $patch_file_path = $this->thunderUpdaterConfigHandler->getPatchFile($module_name, $this->getUpdateFunctionName($module_name, $update_number), TRUE);

      if (file_put_contents($patch_file_path, $patch_data)) {
        $this->fileQueue->addFile($patch_file_path);
        $new_code_line = count(file($patch_file_path));

        $this->countCodeLines->addCountCodeLines($new_code_line);

        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Generator Update N function.
   *
   * @param string $module
   *   Module name where update will be generated.
   * @param string $update_number
   *   Update number that will be used.
   * @param string $description
   *   Description displayed for update hook function.
   */
  public function generateHook($module, $update_number, $description = '') {
    $module_path = $this->extensionManager->getModule($module)->getPath();
    $update_file = $module_path . '/' . $module . '.install';

    $this->renderer->addSkeletonDir(__DIR__ . '/../../templates/console');

    $parameters = [
      'description' => $description,
      'module' => $module,
      'update_hook_name' => $this->getUpdateFunctionName($module, $update_number),
      'file_exists' => file_exists($update_file),
    ];

    $this->renderFile(
      'thunder_update_hook.php.twig',
      $update_file,
      $parameters,
      FILE_APPEND
    );
  }

  /**
   * Generate Checklist entry for Thunder update.
   *
   * @param string $module
   *   Module name where update will be generated.
   * @param string $update_number
   *   Update number that will be used.
   * @param string $description
   *   Checklist entry title.
   * @param string $checklist_success
   *   Checklist success message.
   * @param string $checklist_failed
   *   Checklist failed message.
   */
  public function generateChecklist($module, $update_number, $description, $checklist_success, $checklist_failed) {
    $module_path = $this->extensionManager->getModule($module)->getPath();
    $checklist_file = $module_path . '/updates.yml';

    $this->renderer->addSkeletonDir(__DIR__ . '/../../templates/console');

    $parameters = [
      'update_hook_name' => $this->getUpdateFunctionName($module, $update_number),
      'file_exists' => file_exists($checklist_file),
      'checklist_title' => $description,
      'checklist_success' => $checklist_success,
      'checklist_failed' => $checklist_failed,
    ];

    $this->renderFile(
      'thunder_update_checklist.yml.twig',
      $checklist_file,
      $parameters,
      FILE_APPEND
    );
  }

}
