<?php

namespace Drupal\Tests\thunder\FunctionalJavascript\Integration;

use Drupal\Tests\thunder\FunctionalJavascript\ThunderJavascriptTestBase;
use Drupal\Tests\thunder\FunctionalJavascript\ThunderFormFieldTestTrait;

/**
 * Test for update hook changes.
 *
 * @group Thunder
 *
 * @package Drupal\Tests\thunder\FunctionalJavascript\Integration
 */
class InlineEntityFormTest extends ThunderJavascriptTestBase {

  use ThunderFormFieldTestTrait;

  /**
   * Test saving collapsed gallery paragraph.
   *
   * Test saving changes in inline entity form using the
   * inline_entity_form_simple widget inside gallery paragraph when the
   * paragraph form is collapsed.
   *
   * Demo Article (node Id: 7) is used for testing.
   */
  public function testGalleryCollapse() {

    // Test saving inline entity form when collapsing paragraph form.
    $this->drupalGet("node/7/edit");
    $page = $this->getSession()->getPage();

    // Edit gallery paragraph.
    $this->clickButtonCssSelector($page, '[data-drupal-selector="field-paragraphs-0-edit-2"]');
    $this->setFieldValue($page, 'field_paragraphs[0][subform][field_media][0][inline_entity_form][name][0][value]', 'New gallery name before collapse');

    // Collapse parargraph form.
    $this->clickButtonCssSelector($page, '[name="field_paragraphs_0_collapse"]');
    $this->clickSave();

    // Re-open edit form, value has changed.
    $this->drupalGet("node/7/edit");
    $this->assertSession()
      ->pageTextContains('New gallery name before collapse');
  }

  /**
   * Test saving collapsed video paragraph.
   *
   * Test saving changes in inline entity form using the
   * inline_entity_form_simple_plus widget inside video paragraph when the
   * paragraph form is collapsed.
   *
   * Demo Article (node Id: 7) is used for testing.
   */
  public function testVideoCollapse() {

    // Test saving inline entity form when collapsing paragraph form.
    $this->drupalGet("node/7/edit");
    $page = $this->getSession()->getPage();

    // Edit video paragraph.
    $this->clickButtonCssSelector($page, '[data-drupal-selector="field-paragraphs-2-edit-2"]');
    $this->setFieldValue($page, 'field_paragraphs[2][subform][field_video][current][items][0][display][name][0][value]', 'New video name before collapse');

    // Collapse parargraph form.
    $this->clickButtonCssSelector($page, '[name="field_paragraphs_2_collapse"]');
    $this->clickSave();

    // Re-open edit form, value has changed.
    $this->drupalGet("node/7/edit");
    $this->assertSession()->pageTextContains('New video name before collapse');
  }

}
