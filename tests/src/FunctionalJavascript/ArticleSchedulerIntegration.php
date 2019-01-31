<?php

namespace Drupal\Tests\thunder\FunctionalJavascript;

/**
 * Tests content moderation and scheduling..
 *
 * @group Thunder
 */
class ArticleSchedulerIntegration extends ThunderJavascriptTestBase {

  use ThunderArticleTestTrait;

  /**
   * Test Creation of Article.
   */
  public function testSchedulerAccess() {
    $this->logWithRole('restricted_editor');
    $this->articleFillNew([
      'field_channel' => 1,
      'title[0][value]' => 'Scheduler integration testing',
      'field_seo_title[0][value]' => 'Scheduler integration testing seo title',
    ]);
    $this->assertSession()->elementNotExists('xpath', '//*[@data-drupal-selector="edit-publish-on-wrapper"]');

    $this->clickSave();

    $node = $this->getNodeByTitle('Scheduler integration testing');
    $edit_url = $node->toUrl('edit-form');

    // Add schedule data using editor.
    $this->logWithRole('editor');

    $this->drupalGet($edit_url);
    $this->expandAllTabs();
    $publish_timestamp = strtotime('-1 days');
    $this->setFieldValues($this->getSession()->getPage(), [
      'publish_on[0][value][date]' => date('Y-m-d', $publish_timestamp),
      'publish_on[0][value][time]' => date('H:i:s', $publish_timestamp),
      'publish_state[0]' => 'published',
    ]);
    $this->clickSave();

    // Test restricted editor access.
    $this->logWithRole('restricted_editor');
    $this->drupalGet($edit_url);
    $this->assertEquals(1, count($this->xpath('//h1[contains(@class, "page-title")]//span[text() = "403"]')));

    $this->container->get('cron')->run();

    $this->drupalGet($edit_url);
    $this->assertEquals(1, count($this->xpath('//h1[contains(@class, "page-title")]//em[text() = "Edit Article"]')));

  }

}
