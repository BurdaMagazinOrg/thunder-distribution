<?php

namespace Drupal\Tests\thunder\Kernel;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\KernelTests\KernelTestBase;
use Drupal\thunder\ThunderTestLogger;
use Psr\Log\LogLevel;

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
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    parent::register($container);
    ThunderTestLogger::register($container);
  }


  /**
   * Tests \Drupal\thunder\ConfigSelector().
   */
  public function testConfigSelector() {
    /** @var \Drupal\Core\Extension\ModuleInstallerInterface $module_installer */
    $module_installer = $this->container->get('module_installer');

    // Install a module that has configuration with thunder third party settings
    // for the ConfigSelector.
    $module_installer->install(['thunder_config_test_one']);
    /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface[] $configs */
    $configs = \Drupal::entityTypeManager()->getStorage('config_test')->loadMultiple();
    $this->assertTrue($configs['feature_a_one']->status());
    $this->assertArrayNotHasKey('feature_a_two', $configs);
    $this->assertArrayNotHasKey('feature_a_three', $configs);
    $this->assertLogMessages(['<em class="placeholder">thunder_config_test_one</em> module installed.']);
    $this->assertMessages([]);
    $this->clearLogger();

    // Install another module that will cause config_test.dynamic.feature_a_two
    // to be installed. This configuration has a higher priority than
    // config_test.dynamic.feature_a_one. Therefore, feature_a_one will be
    // disabled and feature_a_two will be enabled.
    $module_installer->install(['thunder_config_test_two']);
    $configs = \Drupal::entityTypeManager()->getStorage('config_test')->loadMultiple();
    $this->assertFalse($configs['feature_a_one']->status());
    $this->assertTrue($configs['feature_a_two']->status());
    $this->assertArrayNotHasKey('feature_a_three', $configs);
    $this->assertLogMessages([
      '<em class="placeholder">thunder_config_test_two</em> module installed.',
      'Configuration <a href="/admin/structure/config_test/manage/feature_a_one">Feature A version 1</a> has been disabled in favor of <a href="/admin/structure/config_test/manage/feature_a_two">Feature A version 2</a>.',
    ]);
    $this->assertMessages(['Configuration <a href="/admin/structure/config_test/manage/feature_a_one">Feature A version 1</a> has been disabled in favor of <a href="/admin/structure/config_test/manage/feature_a_two">Feature A version 2</a>.']);
    $this->clearLogger();

    // Install another module that will cause
    // config_test.dynamic.feature_a_three to be installed. This configuration
    // has a higher priority than config_test.dynamic.feature_a_one but a lower
    // priority than config_test.dynamic.feature_a_two. Therefore,
    // feature_a_three will be disabled and feature_a_two will still be enabled.
    $module_installer->install(['thunder_config_test_three']);
    $configs = \Drupal::entityTypeManager()->getStorage('config_test')->loadMultiple();
    $this->assertFalse($configs['feature_a_one']->status());
    $this->assertTrue($configs['feature_a_two']->status());
    $this->assertFalse($configs['feature_a_three']->status());
    $this->assertLogMessages([
      '<em class="placeholder">thunder_config_test_three</em> module installed.',
      'Configuration <a href="/admin/structure/config_test/manage/feature_a_three">Feature A version 3</a> has been disabled in favor of <a href="/admin/structure/config_test/manage/feature_a_two">Feature A version 2</a>.',
    ]);
    $this->assertMessages(['Configuration <a href="/admin/structure/config_test/manage/feature_a_three">Feature A version 3</a> has been disabled in favor of <a href="/admin/structure/config_test/manage/feature_a_two">Feature A version 2</a>.']);
    $this->clearLogger();

    // Uninstall a module causing config_test.dynamic.feature_a_two to be
    // removed. Since config_test.dynamic.feature_a_three has the next highest
    // priority it will be enabled.
    $module_installer->uninstall(['thunder_config_test_two']);
    $configs = \Drupal::entityTypeManager()->getStorage('config_test')->loadMultiple();
    $this->assertFalse($configs['feature_a_one']->status());
    $this->assertArrayNotHasKey('feature_a_two', $configs);
    $this->assertTrue($configs['feature_a_three']->status());
    $this->assertLogMessages([
      '<em class="placeholder">thunder_config_test_two</em> module uninstalled.',
      'Configuration <a href="/admin/structure/config_test/manage/feature_a_three">Feature A version 3</a> has been enabled.',
    ]);
    $this->assertMessages(['Configuration <a href="/admin/structure/config_test/manage/feature_a_three">Feature A version 3</a> has been enabled.']);
    $this->clearLogger();

    // Install the module that will cause config_test.dynamic.feature_a_two to
    // be installed again. This configuration has a higher priority than
    // config_test.dynamic.feature_a_one and
    // config_test.dynamic.feature_a_three. Therefore, feature_a_three will be
    // disabled and feature_a_two will be enabled.
    $module_installer->install(['thunder_config_test_two']);
    $configs = \Drupal::entityTypeManager()->getStorage('config_test')->loadMultiple();
    $this->assertFalse($configs['feature_a_one']->status());
    $this->assertTrue($configs['feature_a_two']->status());
    $this->assertFalse($configs['feature_a_three']->status());
    $this->assertLogMessages([
      '<em class="placeholder">thunder_config_test_two</em> module installed.',
      'Configuration <a href="/admin/structure/config_test/manage/feature_a_three">Feature A version 3</a> has been disabled in favor of <a href="/admin/structure/config_test/manage/feature_a_two">Feature A version 2</a>.',
    ]);
    $this->assertMessages(['Configuration <a href="/admin/structure/config_test/manage/feature_a_three">Feature A version 3</a> has been disabled in favor of <a href="/admin/structure/config_test/manage/feature_a_two">Feature A version 2</a>.']);
    $this->clearLogger();

    // Manually disable config_test.dynamic.feature_a_two and enable
    // config_test.dynamic.feature_a_one.
    $configs['feature_a_two']->setStatus(FALSE)->save();
    $configs['feature_a_one']->setStatus(TRUE)->save();
    // Uninstalling thunder_config_test_two will not enable
    // config_test.dynamic.feature_a_three because
    // config_test.dynamic.feature_a_one is enabled.
    $module_installer->uninstall(['thunder_config_test_two']);
    $configs = \Drupal::entityTypeManager()->getStorage('config_test')->loadMultiple();
    $this->assertTrue($configs['feature_a_one']->status());
    $this->assertArrayNotHasKey('feature_a_two', $configs);
    $this->assertFalse($configs['feature_a_three']->status());
    $this->assertLogMessages([
      '<em class="placeholder">thunder_config_test_two</em> module uninstalled.',
    ]);
    $this->assertMessages([]);
    $this->clearLogger();

    // Install the module that will cause config_test.dynamic.feature_a_two to
    // be installed again. This configuration has a higher priority than
    // config_test.dynamic.feature_a_one and
    // config_test.dynamic.feature_a_three. Therefore, feature_a_one will be
    // disabled and feature_a_two will be enabled.
    $module_installer->install(['thunder_config_test_two']);
    $configs = \Drupal::entityTypeManager()->getStorage('config_test')->loadMultiple();
    $this->assertFalse($configs['feature_a_one']->status());
    $this->assertTrue($configs['feature_a_two']->status());
    $this->assertFalse($configs['feature_a_three']->status());
    $this->assertLogMessages([
      '<em class="placeholder">thunder_config_test_two</em> module installed.',
      'Configuration <a href="/admin/structure/config_test/manage/feature_a_one">Feature A version 1</a> has been disabled in favor of <a href="/admin/structure/config_test/manage/feature_a_two">Feature A version 2</a>.',
    ]);
    $this->assertMessages(['Configuration <a href="/admin/structure/config_test/manage/feature_a_one">Feature A version 1</a> has been disabled in favor of <a href="/admin/structure/config_test/manage/feature_a_two">Feature A version 2</a>.']);
    $this->clearLogger();

    // Uninstalling the module that config_test.dynamic.feature_a_three depends
    // on does not change which config is enabled.
    $module_installer->uninstall(['thunder_config_test_three']);
    $configs = \Drupal::entityTypeManager()->getStorage('config_test')->loadMultiple();
    $this->assertFalse($configs['feature_a_one']->status());
    $this->assertTrue($configs['feature_a_two']->status());
    $this->assertArrayNotHasKey('feature_a_three', $configs);
    $this->assertLogMessages([
      '<em class="placeholder">thunder_config_test_three</em> module uninstalled.',
    ]);
    $this->assertMessages([]);
    $this->clearLogger();

    // Uninstalling the module that config_test.dynamic.feature_a_two depends
    // on means that as the last remaining config,
    // config_test.dynamic.feature_a_one is enabled.
    $module_installer->uninstall(['thunder_config_test_two']);
    $configs = \Drupal::entityTypeManager()->getStorage('config_test')->loadMultiple();
    $this->assertTrue($configs['feature_a_one']->status());
    $this->assertArrayNotHasKey('feature_a_two', $configs);
    $this->assertArrayNotHasKey('feature_a_three', $configs);
    $this->assertLogMessages([
      '<em class="placeholder">thunder_config_test_two</em> module uninstalled.',
      'Configuration <a href="/admin/structure/config_test/manage/feature_a_one">Feature A version 1</a> has been enabled.',
    ]);
    $this->assertMessages(['Configuration <a href="/admin/structure/config_test/manage/feature_a_one">Feature A version 1</a> has been enabled.']);
    $this->clearLogger();

    // Install the module that will cause config_test.dynamic.feature_a_four to
    // be created. This configuration has a higher priority than
    // config_test.dynamic.feature_a_one but is disabled by default. Therefore,
    // feature_a_one will be still be enabled and feature_a_four will be
    // disabled.
    $module_installer->install(['thunder_config_test_four']);
    $configs = \Drupal::entityTypeManager()->getStorage('config_test')->loadMultiple();
    $this->assertTrue($configs['feature_a_one']->status());
    $this->assertArrayNotHasKey('feature_a_two', $configs);
    $this->assertArrayNotHasKey('feature_a_three', $configs);
    $this->assertFalse($configs['feature_a_four']->status());
    $this->assertLogMessages([
      '<em class="placeholder">thunder_config_test_four</em> module installed.',
    ]);
    $this->assertMessages([]);
    $this->clearLogger();

    // Uninstalling the module that will cause config_test.dynamic.feature_a_one
    // to be removed. This will cause config_test.dynamic.feature_a_four to be
    // enabled.
    $module_installer->uninstall(['thunder_config_test_one']);
    $configs = \Drupal::entityTypeManager()->getStorage('config_test')->loadMultiple();
    $this->assertArrayNotHasKey('feature_a_one', $configs);
    $this->assertArrayNotHasKey('feature_a_two', $configs);
    $this->assertArrayNotHasKey('feature_a_three', $configs);
    $this->assertTrue($configs['feature_a_four']->status());
    $this->assertLogMessages([
      '<em class="placeholder">thunder_config_test_one</em> module uninstalled.',
      'Configuration <a href="/admin/structure/config_test/manage/feature_a_four">Feature A version 4</a> has been enabled.',
    ]);
    $this->assertMessages(['Configuration <a href="/admin/structure/config_test/manage/feature_a_four">Feature A version 4</a> has been enabled.']);
    $this->clearLogger();

    // Installing the module that will cause config_test.dynamic.feature_a_one
    // to be create. This will cause config_test.dynamic.feature_a_four to still
    // be enabled and feature_a_one will be disabled.
    $module_installer->install(['thunder_config_test_one']);
    $configs = \Drupal::entityTypeManager()->getStorage('config_test')->loadMultiple();
    $this->assertFalse($configs['feature_a_one']->status());
    $this->assertArrayNotHasKey('feature_a_two', $configs);
    $this->assertArrayNotHasKey('feature_a_three', $configs);
    $this->assertTrue($configs['feature_a_four']->status());
    $configs['feature_a_four']->setStatus(FALSE)->save();
    $this->assertLogMessages([
      '<em class="placeholder">thunder_config_test_one</em> module installed.',
      'Configuration <a href="/admin/structure/config_test/manage/feature_a_one">Feature A version 1</a> has been disabled in favor of <a href="/admin/structure/config_test/manage/feature_a_four">Feature A version 4</a>.',
    ]);
    $this->assertMessages(['Configuration <a href="/admin/structure/config_test/manage/feature_a_one">Feature A version 1</a> has been disabled in favor of <a href="/admin/structure/config_test/manage/feature_a_four">Feature A version 4</a>.']);
    $this->clearLogger();

    // Because both config_test.dynamic.feature_a_one and
    // config_test.dynamic.feature_a_four are disabled, uninstalling a module
    // should not enable feature_a_four even though feature_a_one is deleted.
    $module_installer->uninstall(['thunder_config_test_one']);
    $configs = \Drupal::entityTypeManager()->getStorage('config_test')->loadMultiple();
    $this->assertArrayNotHasKey('feature_a_one', $configs);
    $this->assertArrayNotHasKey('feature_a_two', $configs);
    $this->assertArrayNotHasKey('feature_a_three', $configs);
    $this->assertFalse($configs['feature_a_four']->status());
    $this->assertLogMessages([
      '<em class="placeholder">thunder_config_test_one</em> module uninstalled.',
    ]);
    $this->assertMessages([]);
    $this->clearLogger();
  }

  protected function assertLogMessages($messages = [], $level = LogLevel::INFO) {
    if ($messages != $this->container->get('thunder.test_logger')->getLogs($level)) {
      var_dump($this->container->get('thunder.test_logger')->getLogs($level));
    }
    $this->assertEquals($messages, $this->container->get('thunder.test_logger')->getLogs($level));
  }

  protected function assertMessages($messages = [], $type = 'status') {
    $actual_messages = drupal_get_messages($type);
    if (!empty($actual_messages)) {
      $actual_messages = array_map('strval', $actual_messages[$type]);
    }
    $this->assertEquals($messages, $actual_messages);
  }

  protected function clearLogger() {
    $this->container->get('thunder.test_logger')->clear();
  }

}
