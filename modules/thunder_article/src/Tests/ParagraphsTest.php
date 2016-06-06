<?php
/**
 * @file
 * Contains
 */

namespace Drupal\thunder_article\Tests;


use Drupal\thunder\ThunderBaseTest;

/**
 * @group Thunder
 */
class ParagraphsTest extends ThunderBaseTest{


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
    /*
    #$this->drupalPostAjaxForm(NULL, array(), 'field_paragraphs_media_add_more');
    #$this->drupalPostAjaxForm(NULL, array(), 'field_paragraphs_media_add_more');
    #$this->drupalPostAjaxForm(NULL, array(), 'field_paragraphs_quote_add_more');
    #$this->drupalPostAjaxForm(NULL, array(), 'field_paragraphs_gallery_add_more');

    $edit = [
      'field_paragraphs[1][subform][field_media][actions][bundle]' => 'image'
    ];

    $create_outer_button_selector = '//input[@type="submit" and @value="Add new Media" and @data-drupal-selector="edit-field-paragraphs-1-subform-field-media-actions-ief-add"]';
    $this->drupalPostAjaxForm(NULL, $edit, $this->getButtonName($create_outer_button_selector));


    // Create an 'image' file, upload it.
    $text = 'Trust me I\'m an image';
    file_put_contents('temporary://myImage1.jpg', $text);

  #  $create_outer_button_selector = '//input[@type="submit" and @value="Create media" and @data-drupal-selector="edit-field-paragraphs-2-subform-field-media-actions-ief-add"]';
   # $this->drupalPostAjaxForm(NULL, array(), $this->getButtonName($create_outer_button_selector));

    #$create_outer_button_selector = '//input[@type="submit" and @value="Create gallery" and @data-drupal-selector="edit-field-paragraphs-4-subform-field-media-actions-ief-add"]';
    #$this->drupalPostAjaxForm(NULL, array(), $this->getButtonName($create_outer_button_selector));
*/

    $edit = array(
      'field_channel' => 1,
      'title[0][value]' => 'Paragraphs foo',
      'field_seo_title[0][value]' => 'Paragraphs foo',
      'field_paragraphs[0][subform][field_text][0][value]' => 'Noice text',
      #    'field_paragraphs[1][subform][field_media][form][inline_entity_form][name][0][value]' => 'Image 1',
      #    'files[field_paragraphs_1_subform_field_media_form_inline_entity_form_field_image_0]' => drupal_realpath('temporary://myImage1.jpg'),
    );
    $this->drupalPostForm(NULL, $edit, t('Save and publish'));

    $node = $this->drupalGetNodeByTitle('Paragraphs foo');

    $this->drupalGet('node/' . $node->id());

    // Check the text and image after publish.
    $this->assertRaw('Noice text', 'Text was found in content');


  }
}