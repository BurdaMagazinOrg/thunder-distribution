<?php

namespace Drupal\Tests\scheduler_content_moderation_integration\Kernel;

use Drupal\node\Entity\Node;

/**
 * Test covering the UnPublishedStateConstraintValidator.
 *
 * @coversDefaultClass \Drupal\scheduler_content_moderation_integration\Plugin\Validation\Constraint\UnPublishStateConstraintValidator
 *
 * @group scheduler
 */
class UnPublishedStateConstraintTest extends SchedulerContentModerationTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $user = $this->createMock('Drupal\Core\Session\AccountInterface');
    $user->method('hasPermission')->willReturn(TRUE);
    $this->container->set('current_user', $user);
  }

  /**
   * Test published to unpublished transition.
   *
   * Test valid scheduled publishing state to valid scheduled un-publish
   * state transitions.
   *
   * @covers ::validate
   */
  public function testValidPublishStateToUnPublishStateTransition() {
    $node = Node::create([
      'type' => 'example',
      'title' => 'Test title',
      'moderation_state' => 'draft',
      'unpublish_on' => strtotime('+3 days'),
      'publish_on' => strtotime('+2 days'),
      'unpublish_state' => 'archived',
      'publish_state' => 'published',
    ]);

    $violations = $node->validate();
    $this->assertCount(0, $violations);
  }

  /**
   * Test an invalid un-publish transition.
   *
   * Test an invalid un-publish transition from a nodes current moderation
   * state.
   *
   * @cover ::validate
   */
  public function testInvalidUnPublishStateTransition() {
    $node = Node::create([
      'type' => 'example',
      'title' => 'Test title',
      'moderation_state' => 'draft',
      'unpublish_on' => strtotime('tomorrow'),
      'unpublish_state' => 'archived',
    ]);

    $violations = $node->validate();

    $this->assertCount(1, $violations);
    $this->assertEquals('The scheduled un-publishing state of <em class="placeholder">archived</em> is not a valid transition from the current moderation state of <em class="placeholder">draft</em> for this content.', $violations->get(0)->getMessage());
  }

  /**
   * Test invalid transition.
   *
   * Test invalid transition from scheduled published to scheduled un-published
   * state.
   *
   * @covers ::validate
   */
  public function testInvalidPublishStateToUnPublishStateTransition() {
    $this->workflow->getTypePlugin()
      ->addState('published_2', 'Published 2')
      ->addTransition('published_2', 'Published 2', ['draft'], 'published_2');

    $config = $this->workflow->getTypePlugin()->getConfiguration();
    $config['states']['published_2']['published'] = TRUE;
    $config['states']['published_2']['default_revision'] = TRUE;

    $this->workflow->getTypePlugin()->setConfiguration($config);
    $this->workflow->save();

    $node = Node::create([
      'type' => 'example',
      'title' => 'Test title',
      'moderation_state' => 'draft',
      'publish_on' => strtotime('tomorrow'),
      'unpublish_on' => strtotime('+2 days'),
      'unpublish_state' => 'archived',
      'publish_state' => 'published_2',
    ]);

    $violations = $node->validate();

    $this->assertCount(1, $violations);
    $this->assertEquals('The scheduled un-publishing state of <em class="placeholder">archived</em> is not a valid transition from the scheduled publishing state of <em class="placeholder">published_2</em>.', $violations->get(0)->getMessage());
  }

}
