<?php

namespace Drupal\Tests\thunder\FunctionalJavascript;

/**
 * Testing of Integrations with other modules.
 *
 * @group Thunder
 *
 * @package Drupal\Tests\thunder\FunctionalJavascript
 */
class IntegrationTest extends ThunderJavascriptTestBase {

  use ThunderArticleTestTrait;
  use ThunderParagraphsTestTrait;

  /**
   * Testing of integration with access_unpublished module.
   */
  public function testAccessUnpublished() {

    // Create article and save it as unpublished.
    $this->articleFillNew([
      'title[0][value]' => 'Article 1',
      'field_seo_title[0][value]' => 'Article 1',
    ]);
    $this->getSession()->getPage()->selectFieldOption('field_channel', 1);
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

}
