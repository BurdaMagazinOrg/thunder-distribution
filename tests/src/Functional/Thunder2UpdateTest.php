<?php

namespace Drupal\Tests\thunder\Functional;

use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\FunctionalTests\Update\UpdatePathTestBase;
use Drupal\Tests\thunder\Traits\ThunderAwsTestFixtureTrait;

/**
 * Tests Thunder updates from 2.0 to current.
 *
 * This will be disabled for the Thunder 3.0 branch as long as there are no
 * releases with update path.
 *
 * TODO: rewrite for Thunder 3.0
 *
 * @group Disabled_Thunder
 */
class Thunder2UpdateTest extends UpdatePathTestBase {
  use ThunderAwsTestFixtureTrait;

  /**
   * An array of config to skip schema checking on.
   *
   * @var array
   */
  protected $configSchemaCheckSkip = [
    'core.entity_form_display.paragraph.image.default',
    'core.entity_form_display.paragraph.video.default',
    'core.entity_view_display.liveblog_post.liveblog_post.default',
    'core.entity_view_display.paragraph.gallery.preview',
    'nexx_integration.settings',
    'views.view.fb_instant_articles',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    // This database was created from the Thunder release available from
    // https://www.drupal.org/project/thunder/releases/8.x-2.0 and an
    // interactive install and choosing to enable all the optional modules.
    $this->databaseDumpFiles = [
      $this->getTestFixture("thunder.2-0.php.gz"),
    ];
  }

  /**
   * Tests Thunder updates from 2.0 to current.
   */
  public function testUpdate() {
    // Calling the method below performs assertions that all updates have been
    // run successfully, configuration schema is correct and that the entity
    // schemas are correct.
    $this->runUpdates();
    $this->rebuildContainer();
    $this->assertTrue($this->container->get('module_handler')->moduleExists('config_selector'));
    // @todo figure out any other tests to run.
  }

  /**
   * {@inheritdoc}
   */
  public function assertConfigSchema(TypedConfigManagerInterface $typed_config, $config_name, $config_data) {
    // Skip specific config files that have incomplete or incorrect schemas.
    // @todo file issues or create updates to fix these.
    if (in_array($config_name, $this->configSchemaCheckSkip, TRUE)) {
      return;
    }
    return parent::assertConfigSchema($typed_config, $config_name, $config_data);
  }

}
