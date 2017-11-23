<?php

namespace Drupal\Tests\thunder\FunctionalJavascript\Integration;

use Drupal\Tests\thunder\FunctionalJavascript\ThunderArticleTestTrait;
use Drupal\Tests\thunder\FunctionalJavascript\ThunderJavascriptTestBase;
use Drupal\Tests\thunder\FunctionalJavascript\ThunderParagraphsTestTrait;

/**
 * Test for access unpublished integration.
 *
 * @group Thunder
 *
 * @package Drupal\Tests\thunder\FunctionalJavascript\Integration
 */
class AccessUnpublishedTest extends ThunderJavascriptTestBase {

  use ThunderArticleTestTrait;
  use ThunderParagraphsTestTrait;

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
    $this->setPublishedStatus(FALSE);
    $this->clickSave();
    // Edit article and generate access unpubplished token.
    $this->drupalGet('node/10/edit');
    $this->expandAllTabs();
    $page = $this->getSession()->getPage();
    $this->scrollElementInView('[data-drupal-selector="edit-generate-token"]');
    $page->find('xpath', '//*[@data-drupal-selector="edit-generate-token"]')
      ->click();
    $this->waitUntilVisible('[data-drupal-selector="edit-token-table-1-link"]', 5000);
    $copyToClipboard = $page->find('xpath', '//*[@data-drupal-selector="edit-token-table-1-link"]');
    $tokenUrl = $copyToClipboard->getAttribute('data-clipboard-text');

    // Log-Out and check that URL with token works, but not URL without it.
    $loggedInUser = $this->loggedInUser;
    $this->drupalLogout();
    $this->drupalGet($tokenUrl);
    $this->assertSession()->pageTextContains('Article Text 1');
    $this->drupalGet('/article-1');
    $noAccess = $this->xpath('//h1[contains(@class, "page-title")]//span[text() = "403"]');
    $this->assertEquals(1, count($noAccess));
/*

    // Log-In and delete token -> check page can't be accessed.
    $this->drupalLogin($loggedInUser);
    $this->drupalGet('node/10/edit');
    $this->clickButtonDrupalSelector($page, 'edit-token-table-1-operation');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->clickSave();

    // Log-Out and check that URL with token doesn't work anymore.
    $this->drupalLogout();
    $this->drupalGet($tokenUrl);
    $noAccess = $this->xpath('//h1[contains(@class, "page-title")]//span[text() = "403"]');
    $this->assertEquals(1, count($noAccess));

    // Log-In and publish article.
    $this->drupalLogin($loggedInUser);
    $this->drupalGet('node/10/edit');
    $this->setPublishedStatus(TRUE);
    $this->clickSave();

    // Log-Out and check that URL to article works.
    $this->drupalLogout();
    $this->drupalGet('article-1');
    $this->assertSession()->pageTextContains('Article Text 1');*/
  }

}
