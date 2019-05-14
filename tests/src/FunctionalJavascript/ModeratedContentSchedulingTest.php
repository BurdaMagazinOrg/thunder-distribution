<?php

namespace Drupal\Tests\thunder\FunctionalJavascript;

use Drupal\node\Entity\Node;

/**
 * Tests publishing/unpublishing scheduling for moderated nodes.
 *
 * @group Thunder
 */
class ModeratedContentSchedulingTest extends ThunderJavascriptTestBase {

  use ThunderArticleTestTrait;

  /**
   * Tests moderated nodes publish scheduling.
   */
  public function testPublishStateSchedule() {
    $publish_timestamp = strtotime('yesterday');
    /* @var $moderation_info \Drupal\content_moderation\ModerationInformationInterface */
    $moderation_info = $this->container->get('content_moderation.moderation_information');

    $this->articleFillNew([
      'field_channel' => 1,
      'title[0][value]' => 'Test workflow article 1 - Published',
      'field_seo_title[0][value]' => 'Massive gaining seo traffic text',
      'moderation_state[0]' => 'draft',
      'publish_on[0][value][date]' => date('Y-m-d', $publish_timestamp),
      'publish_on[0][value][time]' => date('H:i:s', $publish_timestamp),
      'publish_state[0]' => 'published',
    ]);
    $this->clickSave();

    /* @var $node \Drupal\node\Entity\Node */
    $node = $this->getNodeByTitle('Test workflow article 1 - Published');
    $revision_id = $node->getRevisionId();
    // Make sure node is unpublished.
    $this->assertEquals(FALSE, Node::load($node->id())->isPublished());
    $this->container->get('cron')->run();

    $node = $moderation_info->getLatestRevision('node', $node->id());
    // Assert node is now published.
    $this->assertEquals(TRUE, $node->isPublished());
    $this->assertEquals('published', $node->moderation_state->value);
    // Assert only one revision is created during the operation.
    $this->assertEquals($revision_id + 1, $node->getRevisionId());

    $edit_url = $node->toUrl('edit-form');
    $this->drupalGet($edit_url);
    $this->expandAllTabs();
    $this->setFieldValues($this->getSession()->getPage(), [
      'title[0][value]' => 'Test workflow article 1 - Draft',
      'moderation_state[0]' => 'draft',
      'publish_on[0][value][date]' => date('Y-m-d', $publish_timestamp),
      'publish_on[0][value][time]' => date('H:i:s', $publish_timestamp),
      'publish_state[0]' => 'published',
    ]);
    $this->clickSave();

    $node = $moderation_info->getLatestRevision('node', $node->id());
    $this->assertEquals('Test workflow article 1 - Draft', $node->getTitle());
    $this->assertEquals('draft', $node->moderation_state->value);
    $this->container->get('cron')->run();

    $node = $moderation_info->getLatestRevision('node', $node->id());
    $this->assertEquals(TRUE, $node->isPublished());
    $this->assertEquals('published', $node->moderation_state->value);
    $this->assertEquals('Test workflow article 1 - Draft', $node->getTitle());

  }

  /**
   * Tests moderated nodes unpublish scheduling.
   */
  public function testUnpublishStateSchedule() {
    $publish_timestamp = strtotime('yesterday');

    $this->articleFillNew([
      'field_channel' => 1,
      'title[0][value]' => 'Test workflow article 2 - Published',
      'field_seo_title[0][value]' => 'Massive gaining seo traffic text',
      'moderation_state[0]' => 'published',
      'unpublish_on[0][value][date]' => date('Y-m-d', $publish_timestamp),
      'unpublish_on[0][value][time]' => date('H:i:s', $publish_timestamp),
      'unpublish_state[0]' => 'unpublished',
    ]);
    $this->clickSave();

    $node = $this->getNodeByTitle('Test workflow article 2 - Published');
    $revision_id = $node->getRevisionId();
    // Make sure node is published.
    $this->assertEquals(TRUE, Node::load($node->id())->isPublished());
    $this->container->get('cron')->run();

    // Assert node is now unpublished.
    $this->assertEquals(FALSE, Node::load($node->id())->isPublished());
    // Assert only one revision is created during the operation.
    $this->assertEquals($revision_id + 1, Node::load($node->id())->getRevisionId());
  }

  /**
   * Tests publish scheduling for a draft of a published node.
   */
  public function testPublishOfDraft() {
    $publish_timestamp = strtotime('yesterday');

    $this->articleFillNew([
      'field_channel' => 1,
      'title[0][value]' => 'Test workflow article 3 - Published',
      'field_seo_title[0][value]' => 'Massive gaining seo traffic text',
      'moderation_state[0]' => 'published',
    ]);
    $this->clickSave();

    $node = $this->getNodeByTitle('Test workflow article 3 - Published');

    $nid = $node->id();
    // Assert node is published.
    $this->assertEquals('Test workflow article 3 - Published', Node::load($nid)->getTitle());

    // Create a new pending revision and validate it's not the default published
    // one.
    $this->setFieldValues($this->getSession()->getPage(), [
      'title[0][value]' => 'Test workflow article 3 - Draft',
      'moderation_state[0]' => 'draft',
      'publish_on[0][value][date]' => date('Y-m-d', $publish_timestamp),
      'publish_on[0][value][time]' => date('H:i:s', $publish_timestamp),
      'publish_state[0]' => 'published',
    ]);
    $this->clickSave();

    $node = $this->getNodeByTitle('Test workflow article 3 - Draft');
    $revision_id = $node->getRevisionId();
    // Test latest revision is not the published one.
    $this->assertEquals('Test workflow article 3 - Published', Node::load($nid)->getTitle());
    $this->container->get('cron')->run();

    // Test latest revision is now the published one.
    $this->assertEquals('Test workflow article 3 - Draft', Node::load($nid)->getTitle());
    // Assert only one revision is created during the operation.
    $this->assertEquals($revision_id + 1, Node::load($node->id())->getRevisionId());
  }

}
