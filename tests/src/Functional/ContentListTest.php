<?php

namespace Drupal\Tests\thunder\Functional;

use Drupal\thunder\ThunderBaseTest;

/**
 * Test the Thunder content list view.
 *
 * @group Thunder
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

    $primaryMenuBlockSelector = '#block-thunder-admin-primary-local-tasks > nav > nav > ul';
    $secondaryMenuBlockSelector = '#block-thunder-admin-secondary-local-tasks > nav > nav > ul';

    $assert_session = $this->assertSession();
    $assert_session->elementTextNotContains('css', $primaryMenuBlockSelector, 'Scheduled');
    $assert_session->elementTextContains('css', $secondaryMenuBlockSelector, 'Overview');
    $assert_session->elementTextContains('css', $secondaryMenuBlockSelector, 'Scheduled content');
    $assert_session->elementTextContains('css', $secondaryMenuBlockSelector, 'Locked content');

    $this->drupalPostForm('admin/config/thunder_article/configuration', ['move_scheduler_local_task' => 0], 'Save configuration');

    $this->drupalGet('admin/content');

    $assert_session->elementTextNotContains('css', $secondaryMenuBlockSelector, 'Scheduled content');
    $assert_session->elementTextContains('css', $primaryMenuBlockSelector, 'Scheduled');
    $assert_session->elementTextContains('css', $secondaryMenuBlockSelector, 'Locked content');

    $this->drupalPostForm('admin/config/thunder_article/configuration', ['move_scheduler_local_task' => 1], 'Save configuration');

    $this->drupalGet('admin/content');

    $assert_session->elementTextNotContains('css', $primaryMenuBlockSelector, 'Scheduled');
    $assert_session->elementTextContains('css', $secondaryMenuBlockSelector, 'Scheduled content');

    $this->drupalPostForm('admin/config/thunder_article/configuration', ['move_scheduler_local_task' => 0], 'Save configuration');

    $this->drupalGet('admin/content');

    $assert_session->elementTextContains('css', $primaryMenuBlockSelector, 'Scheduled');
    $assert_session->elementTextNotContains('css', $secondaryMenuBlockSelector, 'Scheduled content');

    // Uninstall the scheduler and the links should be gone.
    $this->container->get('module_installer')->uninstall(['scheduler']);
    $this->drupalGet('admin/content');

    $assert_session->elementTextNotContains('css', $primaryMenuBlockSelector, 'Scheduled');
    $assert_session->elementTextNotContains('css', $secondaryMenuBlockSelector, 'Scheduled content');

  }

}
