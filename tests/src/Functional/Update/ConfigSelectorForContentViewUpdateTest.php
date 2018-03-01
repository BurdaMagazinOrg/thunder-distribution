<?php

namespace Drupal\Tests\thunder\Functional\Update;

use Drupal\views\Entity\View;

/**
 * Test the behavior for adding config selector to the content view.
 *
 * @group Thunder
 */
class ConfigSelectorForContentViewUpdateTest extends ThunderUpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    // This database was created from the Thunder release available from
    // https://www.drupal.org/project/thunder/releases/8.x-2.14 and an
    // interactive install and choosing to enable all the optional modules.
    $this->databaseDumpFiles = [
      $this->getTestFixture("thunder.2.14.php.gz"),
    ];
  }

  /**
   * Tests Thunder updates from 2.0 to current.
   */
  public function testFreshInstallBehavior() {
    // Calling the method below performs assertions that all updates have been
    // run successfully, configuration schema is correct and that the entity
    // schemas are correct.
    $this->runUpdates();

    $content_view = View::load('content');
    $this->assertNotNull($content_view, 'The content view exists');
    $this->assertFalse(in_array('content_lock', $content_view->getDependencies()['module']), 'Content view is without content_lock dependency');
    $this->assertNotNull($content_view->getThirdPartySettings('config_selector'), 'Third party settings for config_selector are present');
    $this->assertArraySubset(['feature' => 'thunder_content_view', 'priority' => 0], $content_view->getThirdPartySettings('config_selector'));

    $content_lock_view = View::load('content_content_lock');
    $this->assertNotNull($content_lock_view, 'The content_content_lock view exists');
    $this->assertTrue(in_array('content_lock', $content_lock_view->getDependencies()['module']), 'Content lock view is has a content_lock dependency');
    $this->assertNotNull($content_lock_view->getThirdPartySettings('config_selector'), 'Third party settings for config_selector are present');
    $this->assertArraySubset(['feature' => 'thunder_content_view', 'priority' => 1], $content_lock_view->getThirdPartySettings('config_selector'));
  }

}
