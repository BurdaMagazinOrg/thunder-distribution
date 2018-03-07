<?php

namespace Drupal\Tests\thunder\Functional;

use Behat\Mink\Exception\ElementNotFoundException;
use Drupal\thunder\ThunderBaseTest;

/**
 * Test the Thunder content list view.
 */
class ContentListTest extends ThunderBaseTest {

  /**
   * The profile to install as a basis for testing.
   *
   * @var string
   */
  protected $profile = 'thunder';

  /**
   * Tests scheduler tab is in local tasks.
   */
  public function testSchedulerLocalTask() {

    $this->logWithRole('administrator');
    $this->drupalGet('admin/content');

    $assert_session = $this->assertSession();
    $assert_session->elementTextContains('css', '#block-thunder-admin-secondary-local-tasks > nav > nav > ul', 'Overview');
    $assert_session->elementTextContains('css', '#block-thunder-admin-secondary-local-tasks > nav > nav > ul', 'Scheduled content');

    $this->drupalPostForm('admin/config/thunder_article/configuration', ['move_scheduler_local_task' => 0], 'Save configuration');

    $this->drupalGet('admin/content');

    try {
      $assert_session->elementTextNotContains('css', '#block-thunder-admin-secondary-local-tasks > nav > nav > ul', 'Scheduled content');
    }
    catch (ElementNotFoundException $exception) {
    }

    $assert_session->elementTextContains('css', '#block-thunder-admin-primary-local-tasks > nav > nav > ul', 'Scheduled');
  }

}
