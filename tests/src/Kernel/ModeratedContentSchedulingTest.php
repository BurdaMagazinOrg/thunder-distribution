<?php

namespace Drupal\Tests\thunder\Kernel;

use Drupal\node\Entity\Node;
use Drupal\Tests\scheduler_content_moderation_integration\Kernel\SchedulerContentModerationTestBase;

/**
 * Tests publishing/unpublishing scheduling for moderated nodes.
 *
 * @group Thunder
 */
class ModeratedContentSchedulingTest extends SchedulerContentModerationTestBase {

  /**
   * Tests moderated nodes publish scheduling.
   */
  public function testPublishStateSchedule() {
    $node = Node::create([
      'type' => 'example',
      'title' => 'Published title',
      'moderation_state' => 'draft',
      'publish_on' => strtotime('yesterday'),
      'publish_state' => 'published',
    ]);
    $node->save();
    $revision_id = $node->getRevisionId();

    // Make sure node is unpublished.
    $this->assertEquals(FALSE, Node::load($node->id())->isPublished());

    $this->container->get('cron')->run();

    $node = $this->moderationInfo->getLatestRevision('node', $node->id());

    // Assert node is now published.
    $this->assertEquals(TRUE, $node->isPublished());
    $this->assertEquals('published', $node->moderation_state->value);

    // Assert only one revision is created during the operation.
    $this->assertEquals($revision_id + 1, $node->getRevisionId());

    $node->setTitle('Draft title');
    $node->moderation_state->value = 'draft';
    $node->publish_on->value = strtotime('yesterday');
    $node->publish_state->value = 'published';
    $node->save();

    $node = $this->moderationInfo->getLatestRevision('node', $node->id());
    $this->assertEquals('Draft title', $node->getTitle());
    $this->assertEquals('draft', $node->moderation_state->value);

    $this->container->get('cron')->run();

    $node = $this->moderationInfo->getLatestRevision('node', $node->id());
    $this->assertEquals(TRUE, $node->isPublished());
    $this->assertEquals('published', $node->moderation_state->value);
    $this->assertEquals('Draft title', $node->getTitle());
  }

  /**
   * Tests moderated nodes unpublish scheduling.
   */
  public function testUnpublishStateSchedule() {
    $node = Node::create([
      'type' => 'example',
      'title' => 'Published title',
      'moderation_state' => 'published',
      'unpublish_on' => strtotime('yesterday'),
      'unpublish_state' => 'archived',
    ]);
    $node->save();
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
    $node = Node::create([
      'type' => 'example',
      'title' => 'Published title',
      'moderation_state' => 'published',
    ]);
    $node->save();

    $nid = $node->id();

    // Assert node is published.
    $this->assertEquals('Published title', Node::load($nid)->getTitle());

    // Create a new pending revision and validate it's not the deault published
    // one.
    $node->setTitle('Draft title');
    $node->set('publish_on', strtotime('yesterday'));
    $node->set('moderation_state', 'draft');
    $node->set('publish_state', 'published');
    $node->save();
    $revision_id = $node->getRevisionId();

    // Test latest revision is not the published one.
    $this->assertEquals('Published title', Node::load($nid)->getTitle());

    $this->container->get('cron')->run();

    // Test latest revision is now the published one.
    $this->assertEquals('Draft title', Node::load($nid)->getTitle());

    // Assert only one revision is created during the operation.
    $this->assertEquals($revision_id + 1, Node::load($node->id())->getRevisionId());
  }

}
