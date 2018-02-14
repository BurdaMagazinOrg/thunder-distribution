<?php

namespace Drupal\Tests\thunder\Kernel;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\KernelTests\KernelTestBase;
use Drupal\thunder\ThunderTestLogger;
use Psr\Log\LogLevel;

/**
 * Tests the ConfigSelector.
 *
 * @group ThunderConfig
 *
 * @see \Drupal\thunder\ConfigSelector
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
  public function _testConfigSelector() {
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

  /**
   * Tests \Drupal\thunder\ConfigSelector().
   *
   * Checks indirect module uninstall dependencies.
   */
  public function _testConfigSelectorIndirectDependency() {
    /** @var \Drupal\Core\Extension\ModuleInstallerInterface $module_installer */
    $module_installer = $this->container->get('module_installer');

    // Install two modules at start, 3 configurations should be imported, where
    // only one is enabled and that one depends on
    // "thunder_config_test_depends_on_test_two", where that module depends on
    // "thunder_config_test_two".
    $module_installer->install([
      'thunder_config_test_one',
      'thunder_config_test_depends_on_test_two',
    ]);
    /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface[] $configs */
    $configs = \Drupal::entityTypeManager()->getStorage('config_test')->loadMultiple();

    $this->assertFalse($configs['feature_a_one']->status());
    $this->assertFalse($configs['feature_a_two']->status());
    $this->assertTrue($configs['feature_a_depends_on_test_two']->status());
    $this->assertArrayNotHasKey('feature_a_three', $configs);

    $this->assertLogMessages([
      '<em class="placeholder">thunder_config_test_two</em> module installed.',
      '<em class="placeholder">thunder_config_test_depends_on_test_two</em> module installed.',
      '<em class="placeholder">thunder_config_test_one</em> module installed.',
      'Configuration <a href="/admin/structure/config_test/manage/feature_a_one">Feature A version 1</a> has been disabled in favor of <a href="/admin/structure/config_test/manage/feature_a_depends_on_test_two">Feature A indirect depending on Test Two</a>.',
      'Configuration <a href="/admin/structure/config_test/manage/feature_a_two">Feature A version 2</a> has been disabled in favor of <a href="/admin/structure/config_test/manage/feature_a_depends_on_test_two">Feature A indirect depending on Test Two</a>.',
    ]);
    $this->assertMessages([
      'Configuration <a href="/admin/structure/config_test/manage/feature_a_one">Feature A version 1</a> has been disabled in favor of <a href="/admin/structure/config_test/manage/feature_a_depends_on_test_two">Feature A indirect depending on Test Two</a>.',
      'Configuration <a href="/admin/structure/config_test/manage/feature_a_two">Feature A version 2</a> has been disabled in favor of <a href="/admin/structure/config_test/manage/feature_a_depends_on_test_two">Feature A indirect depending on Test Two</a>.',
    ]);
    $this->clearLogger();

    // Uninstall "thunder_config_test_two", that will indirectly uninstall also
    // "thunder_config_test_depends_on_test_two", where all dependency are
    // removed and only requirements for "feature_a_one" are fulfilled.
    $module_installer->uninstall(['thunder_config_test_two']);
    $configs = \Drupal::entityTypeManager()->getStorage('config_test')->loadMultiple();
    $this->assertTrue($configs['feature_a_one']->status(), "Configuration: Feature A version 1 - should be enabled.");
    $this->assertArrayNotHasKey('feature_a_two', $configs);
    $this->assertArrayNotHasKey('feature_a_depends_on_test_two', $configs);
    $this->assertArrayNotHasKey('feature_a_three', $configs);
    $this->assertLogMessages([
      '<em class="placeholder">thunder_config_test_depends_on_test_two</em> module uninstalled.',
      '<em class="placeholder">thunder_config_test_two</em> module uninstalled.',
      'Configuration <a href="/admin/structure/config_test/manage/feature_a_one">Feature A version 1</a> has been enabled.',
    ]);
    $this->assertMessages(['Configuration <a href="/admin/structure/config_test/manage/feature_a_one">Feature A version 1</a> has been enabled.']);
    $this->clearLogger();
  }

  /**
   * Tests \Drupal\thunder\ConfigSelector().
   *
   * Tests installing a module that provides multiple features with multiple
   * versions.
   */
  public function testConfigSelectorMultipleFeatures() {
    /** @var \Drupal\Core\Extension\ModuleInstallerInterface $module_installer */
    $module_installer = $this->container->get('module_installer');

    $module_installer->install(['thunder_config_test_provides_multiple']);
    /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface[] $configs */
    $configs = \Drupal::entityTypeManager()->getStorage('config_test')->loadMultiple();

    $this->assertTrue($configs['feature_a_two']->status());
    // Lower priority than feature_a_two.
    $this->assertFalse($configs['feature_a_one']->status());
    // Lower priority than feature_a_two.
    $this->assertFalse($configs['feature_a_three']->status());
    // Higher priority but it is disabled in default configuration.
    $this->assertFalse($configs['feature_a_four']->status());

    $this->assertTrue($configs['feature_b_two']->status());
    $this->assertFalse($configs['feature_b_one']->status());

    $this->assertTrue($configs['feature_c_one']->status());
  }

  /**
   * Asserts the logger has messages.
   *
   * @param string[] $messages
   *   (optional) The messages we expect the logger to have. Defaults to an
   *   empty array.
   * @param string $level
   *   (optional) The log level of the expected messages. Defaults to
   *   \Psr\Log\LogLevel::INFO.
   */
  protected function assertLogMessages(array $messages = [], $level = LogLevel::INFO) {
    $this->assertEquals($messages, $this->container->get('thunder.test_logger')->getLogs($level));
  }

  /**
   * Asserts the Drupal message service has messages.
   *
   * @param array $messages
   *   (optional) The messages we expect the Drupal message service to have.
   *   Defaults to an empty array.
   * @param string $type
   *   (optional) The type of the expected messages. Defaults to 'status'.
   */
  protected function assertMessages(array $messages = [], $type = 'status') {
    $actual_messages = drupal_get_messages($type);
    if (!empty($actual_messages)) {
      $actual_messages = array_map('strval', $actual_messages[$type]);
    }
    $this->assertEquals($messages, $actual_messages);
  }

  /**
   * Clears the test logger of messages.
   */
  protected function clearLogger() {
    $this->container->get('thunder.test_logger')->clear();
  }

}
