<?php

namespace Drupal\thunder_article\Tests;


use Drupal\thunder\ThunderBaseTest;

/**
 * Tests for Paragraphs.
 *
 * @group Thunder
 */
class ParagraphsTest extends ThunderBaseTest {

  /**
   * Testing adding of Complex paragraph.
   */
  public function testAddComplexParagraphs() {

    $admin_user = $this->drupalCreateUser(array(
      'create media',
      'create article content',
      'administer nodes',
      'edit own article content',
      'edit any article content',
    ));
    $this->drupalLogin($admin_user);

    // Create a new demo node.
    $this->drupalGet('node/add/article');

    $this->drupalPostAjaxForm(NULL, array(), 'field_paragraphs_text_add_more');

    $edit = array(
      'field_channel' => 1,
      'title[0][value]' => 'Paragraphs foo',
      'field_seo_title[0][value]' => 'Paragraphs foo',
      'field_paragraphs[0][subform][field_text][0][value]' => 'Noice text',
    );
    $this->drupalPostForm(NULL, $edit, t('Save and publish'));

    $node = $this->drupalGetNodeByTitle('Paragraphs foo');

    $this->drupalGet('node/' . $node->id());

    // Check the text and image after publish.
    $this->assertRaw('Noice text', 'Text was found in content');

  }

}
