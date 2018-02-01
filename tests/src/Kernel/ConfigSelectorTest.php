<?php

namespace Drupal\Tests\thunder\Kernel;

use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\KernelTests\KernelTestBase;

/**
 * @group ThunderConfig
 */
class ConfigSelectorTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['system', 'config_test'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    // We need to hack the profile module in.
    $config = $this->config('core.extension');
    $module = $config->get('module');
    $module['thunder'] = 1000;
    $config
      ->set('module', $module)
      ->set('profile', 'thunder')
      ->save();
    $listing = new ExtensionDiscovery(\Drupal::root());
    $module_list = $listing->scan('profile');

    $module_handler = $this->container->get('module_handler');
    $module_handler->addProfile('thunder', $module_list['thunder']->getPath());

    // Update the kernel to make their services available.
    $extensions = $module_handler->getModuleList();
    $this->container->get('kernel')->updateModules($extensions, $extensions);

    // Ensure isLoaded() is TRUE in order to make
    // \Drupal\Core\Theme\ThemeManagerInterface::render() work.
    // Note that the kernel has rebuilt the container; this $module_handler is
    // no longer the $module_handler instance from above.
    $module_handler = $this->container->get('module_handler');
    $module_handler->reload();
  }

  /**
   *
   */
  public function testConfigSelector() {
    // The profile needs to be installed.
    $this->enableModules(['thunder']);

    /** @var \Drupal\Core\Extension\ModuleInstallerInterface $module_installer */
    $module_installer = $this->container->get('module_installer');

    $module_installer->install(['thunder_config_test_one']);
    /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface[] $configs */
    $configs = \Drupal::entityTypeManager()->getStorage('config_test')->loadMultiple();
    $this->assertTrue($configs['feature_a_one']->status());

    $module_installer->install(['thunder_config_test_two']);
    $configs = \Drupal::entityTypeManager()->getStorage('config_test')->loadMultiple();
    $this->assertFalse($configs['feature_a_one']->status());
    $this->assertTrue($configs['feature_a_two']->status());

    $module_installer->install(['thunder_config_test_three']);
    $configs = \Drupal::entityTypeManager()->getStorage('config_test')->loadMultiple();
    $this->assertFalse($configs['feature_a_one']->status());
    $this->assertTrue($configs['feature_a_two']->status());
    $this->assertFalse($configs['feature_a_three']->status());
  }

}
