<?php

namespace Drupal\Tests\thunder\Functional\Update;

/**
 * Tests Thunder updates from 2.0 to current.
 *
 * @group Thunder
 */
class Thunder2UpdateTest extends ThunderUpdatePathTestBase {

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

}
