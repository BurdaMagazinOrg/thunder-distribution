<?php

namespace Drupal\Tests\thunder\FunctionalJavascript;

/**
 * Testing of module integrations.
 *
 * @group Thunder
 *
 * @package Drupal\Tests\thunder\FunctionalJavascript
 */
class ModuleIntegrationTest extends ThunderJavascriptTestBase {

  use ThunderParagraphsTestTrait;
  use ThunderArticleTestTrait;
  use ThunderMetaTagTrait;

  /**
   * Column in diff table used for previous text.
   *
   * @var int
   */
  protected static $previousTextColumn = 3;

  /**
   * Column in diff table used for new text.
   *
   * @var int
   */
  protected static $newTextColumn = 6;

  /**
   * Validate diff entry for one field.
   *
   * @param string $fieldName
   *   Human defined field name.
   * @param array $previous
   *   Associative array with previous text per row.
   * @param array $previousHighlighted
   *   Previous highlighted texts.
   * @param array $new
   *   Associative array with new text per row.
   * @param array $newHighlighted
   *   New highlighted texts.
   */
  protected function validateDiff($fieldName, array $previous = [], array $previousHighlighted = [], array $new = [], array $newHighlighted = []) {
    // Check for old Text.
    $this->checkFullText($fieldName, static::$previousTextColumn, $previous);

    // Check for new Text.
    $this->checkFullText($fieldName, static::$newTextColumn, $new);

    // Check for highlighted Deleted text.
    $this->checkHighlightedText($fieldName, static::$previousTextColumn, $previousHighlighted);

    // Check for highlighted Added text.
    $this->checkHighlightedText($fieldName, static::$newTextColumn, $newHighlighted);
  }

  /**
   * Check full text in column defined by index.
   *
   * @param string $fieldName
   *   Human defined field name.
   * @param int $columnIndex
   *   Index of column in diff table that should be used to check.
   * @param array $textRows
   *   Associative array with text per row.
   */
  protected function checkFullText($fieldName, $columnIndex, array $textRows = []) {
    $page = $this->getSession()->getPage();

    foreach ($textRows as $indexRow => $expectedText) {
      $previousText = $page->find('xpath', "//tr[./td[text()=\"{$fieldName}\"]]/following-sibling::tr[{$indexRow}]/td[{$columnIndex}]")
        ->getText();

      $this->assertEquals($expectedText, htmlspecialchars_decode($previousText, ENT_QUOTES | ENT_HTML401));
    }
  }

  /**
   * Check more highlighted text in rows.
   *
   * @param string $fieldName
   *   Human defined field name.
   * @param int $columnIndex
   *   Index of column in diff table that should be used to check.
   * @param array $highlightedTextRows
   *   New highlighted texts per row.
   */
  protected function checkHighlightedText($fieldName, $columnIndex, array $highlightedTextRows) {
    $page = $this->getSession()->getPage();

    foreach ($highlightedTextRows as $indexRow => $expectedTexts) {
      foreach ($expectedTexts as $indexHighlighted => $expectedText) {
        $highlightedText = $page->find('xpath', "//tr[./td[text()=\"{$fieldName}\"]]/following-sibling::tr[{$indexRow}]/td[{$columnIndex}]/span[" . ($indexHighlighted + 1) . "]")
          ->getText();

        $this->assertEquals($expectedText, htmlspecialchars_decode($highlightedText, ENT_QUOTES | ENT_HTML401));
      }
    }
  }

  /**
   * Testing integration of "diff" module.
   */
  public function testDiffModule() {

    $this->drupalGet('node/7/edit');

    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    $teaserField = $page->find('xpath', '//*[@data-drupal-selector="edit-field-teaser-text-0-value"]');
    $initialTeaserText = $teaserField->getValue();
    $teaserText = 'Start with Text. ' . $initialTeaserText . ' End with Text.';
    $teaserField->setValue($teaserText);

    $this->clickButtonDrupalSelector($page, 'edit-field-teaser-media-current-items-0-remove-button');
    $this->selectMedia('field_teaser_media', 'image_browser', ['media:1']);

    $newParagraphText = 'One Ring to rule them all, One Ring to find them, One Ring to bring them all and in the darkness bind them!';
    $this->addTextParagraph('field_paragraphs', $newParagraphText);

    $this->addImageParagraph('field_paragraphs', ['media:5']);

    $this->clickArticleSave();

    $this->drupalGet('node/7/revisions');

    $lastLeftRadio = $page->find('xpath', '//table[contains(@class, "diff-revisions")]/tbody//tr[last()]//input[@name="radios_left"]');
    $lastLeftRadio->click();

    // Open diff page.
    $page->find('xpath', '//*[@data-drupal-selector="edit-submit"]')->click();

    // Validate that diff is correct.
    $this->validateDiff(
      'Teaser Text',
      ['1' => $initialTeaserText],
      [],
      ['1' => $teaserText],
      ['1' => ['Start with Text.', '. End with Text']]
    );

    $this->validateDiff(
      'Teaser Media',
      ['1' => 'DrupalCon Logo'],
      ['1' => ['DrupalCon Logo']],
      ['1' => 'Thunder'],
      ['1' => ['Thunder']]
    );

    $this->validateDiff(
      'Paragraphs > Text',
      ['1' => ''],
      [],
      ['1' => '<p>' . $newParagraphText . '</p>', '2' => ''],
      []
    );

    $this->validateDiff(
      'Paragraphs > Image',
      ['1' => ''],
      [],
      ['1' => 'Thunder City'],
      []
    );
  }

  /**
   * Testing integration of "access_unpublished" module.
   */
  public function testAccessUnpublished() {

    // Create article and save it as unpublished.
    $this->articleFillNew([
      'field_channel' => 1,
      'title[0][value]' => 'Article 1',
      'field_seo_title[0][value]' => 'Article 1',
    ]);
    $this->addTextParagraph('field_paragraphs', 'Article Text 1');
    $this->clickArticleSave(2);

    // Edit article and generate access unpubplished token.
    $this->drupalGet('node/10/edit');
    $this->expandAllTabs();
    $page = $this->getSession()->getPage();
    $page->find('xpath', '//*[@data-drupal-selector="edit-generate-token"]')
      ->click();
    $this->waitUntilVisible('[data-drupal-selector="edit-token-table-1-link"]', 5000);
    $copyToClipboard = $page->find('xpath', '//*[@data-drupal-selector="edit-token-table-1-link"]');
    $tokenUrl = $copyToClipboard->getAttribute('data-clipboard-text');

    // Log-Out and check that URL with token works, but not URL without it.
    $this->drupalLogout();
    $this->drupalGet($tokenUrl);
    $this->assertSession()->pageTextContains('Article Text 1');
    $this->drupalGet('article-1');
    $noAccess = $this->xpath('//h1[contains(@class, "page-title")]//span[text() = "403"]');
    $this->assertEquals(1, count($noAccess));

    // Log-In and delete token -> check page can't be accessed.
    $this->logWithRole(static::$defaultUserRole);
    $this->drupalGet('node/10/edit');
    $this->drupalGet('access_unpublished/delete/1');
    $this->drupalGet('node/10/edit');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->clickArticleSave(2);

    // Log-Out and check that URL with token doesn't work anymore.
    $this->drupalLogout();
    $this->drupalGet($tokenUrl);
    $noAccess = $this->xpath('//h1[contains(@class, "page-title")]//span[text() = "403"]');
    $this->assertEquals(1, count($noAccess));

    // Log-In and publish article.
    $this->logWithRole(static::$defaultUserRole);
    $this->drupalGet('node/10/edit');
    $this->clickArticleSave(3);

    // Log-Out and check that URL to article works.
    $this->drupalLogout();
    $this->drupalGet('article-1');
    $this->assertSession()->pageTextContains('Article Text 1');
  }

  /**
   * Testing integration of "metatag_facebook" module.
   */
  public function testFacebookMetaTags() {

    $facebookMetaTags = $this->generateMetaTagConfiguration([
      [
        'facebook fb:admins' => 'zuck',
        'facebook fb:pages' => 'some-fancy-fb-page-url',
        'facebook fb:app_id' => '1121151812167212,1121151812167213',
      ],
    ]);

    // Create Article with facebook meta tags and check it.
    $fieldValues = $this->generateMetaTagFieldValues($facebookMetaTags, 'field_meta_tags[0]');
    $fieldValues += [
      'field_channel' => 1,
      'title[0][value]' => 'Test FB MetaTags Article',
      'field_seo_title[0][value]' => 'Facebook MetaTags',
      'field_teaser_text[0][value]' => 'Facebook MetaTags Testing',
    ];
    $this->articleFillNew($fieldValues);
    $this->clickArticleSave();

    $this->checkMetaTags($facebookMetaTags);
  }

}
