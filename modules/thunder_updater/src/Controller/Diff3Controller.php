<?php

namespace Drupal\thunder_updater\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\thunder_updater\Updater;

/**
 * Class Diff3Controller.
 *
 * @package Drupal\thunder_updater\Controller
 */
class Diff3Controller extends ControllerBase {

  protected $updater;

  /**
   * {@inheritdoc}
   */
  public function __construct(Updater $updater) {
    $this->updater = $updater;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('thunder_updater.updater')
    );
  }

  /**
   * Generate patch from changed configuration.
   *
   * It compares Base vs. Active configuration and creates patch with defined
   * name in patch folder.
   *
   * @param string $module_name
   *   Module name that will be used to generate patch for it.
   * @param string $version_name
   *   Suffix name for patch. Usually version number.
   * @param string $patch_type
   *   Defined how diff will be calculated.
   *
   * @return array
   *   Rendering array with displayed full path to file.
   */
  public function generatePatch($module_name, $version_name, $patch_type) {
    $fileName = $this->updater->generatePatch($module_name, $version_name, $patch_type);

    return [
      '#type' => 'markup',
      '#markup' => ($fileName) ? $this->t('File is generated: <pre>:file_name</pre>', [':file_name' => $fileName]) : $this->t('There are no changes that can be exported.'),
    ];
  }

  /**
   * Apply patch for module and version defined.
   *
   * @param string $module_name
   *   Module name that will be used to generate patch for it.
   * @param string $version_name
   *   Suffix name for patch. Usually version number.
   *
   * @return array
   *   Returns rendering array with result of execution.
   *
   * @throws \Exception
   *   When it's not possible to apply patch.
   */
  public function applyPatch($module_name, $version_name) {
    $updateReport = $this->updater->applyPatch($module_name, $version_name);

    $html = '';

    foreach ($updateReport as $updateEntry) {
      $html .= $updateEntry['action'] . ': ' . $updateEntry['config'] . '<br />';
    }

    return [
      '#type' => 'markup',
      '#markup' => $html,
    ];
  }

}
