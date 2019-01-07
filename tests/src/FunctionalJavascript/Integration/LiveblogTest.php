<?php

namespace Drupal\Tests\thunder\FunctionalJavascript\Integration;

use Behat\Mink\Element\DocumentElement;
use Drupal\Tests\thunder\FunctionalJavascript\ThunderFormFieldTestTrait;
use Drupal\Tests\thunder\FunctionalJavascript\ThunderJavascriptTestBase;
use Drupal\Tests\thunder\FunctionalJavascript\ThunderMediaTestTrait;

/**
 * Testing integration of "liveblog" module.
 *
 * @group Thunder
 *
 * @package Drupal\Tests\thunder\FunctionalJavascript\Integration
 */
class LiveblogTest extends ThunderJavascriptTestBase {

  use ThunderFormFieldTestTrait;
  use ThunderMediaTestTrait;

  /**
   * Set the title of a liveblog post.
   *
   * @param \Behat\Mink\Element\DocumentElement $page
   *   Current active page.
   * @param string $title
   *   The title.
   */
  protected function liveblogSetTitle(DocumentElement $page, $title) {
    $this->setFieldValue($page, 'title[0][value]', $title);
  }

  /**
   * Set the body of a liveblog post.
   *
   * @param string $body
   *   The body.
   */
  protected function liveblogSetBody($body) {
    $this->fillCkEditor(
      "textarea[name='body[0][value]']",
      $body
    );
  }

  /**
   * Testing of module integration.
   */
  public function testWithPusher() {
    $pusherCredentials = json_decode(getenv('PUSHER_CREDENTIALS'), TRUE);
    if (empty($pusherCredentials)) {
      if ($this->isForkPullRequest()) {
        $this->markTestSkipped("Skip Live Blog test (missing secure environment variables)");

        return;
      }

      $this->fail("pusher credentials not provided.");
      return;
    }
    if (!\Drupal::service('module_installer')->install(['thunder_liveblog'])) {
      $this->fail("liveblog module couldn't be installed.");
      return;
    }

    // Configure Pusher.
    $this->logWithRole('administrator');

    $page = $this->getSession()->getPage();
    $this->drupalGet('admin/config/content/liveblog');

    $fieldValues = [
      'plugin_settings[app_id]' => $pusherCredentials['app_id'],
      'plugin_settings[key]' => $pusherCredentials['key'],
      'plugin_settings[secret]' => $pusherCredentials['secret'],
      'plugin_settings[cluster]' => $pusherCredentials['cluster'],
      'channel_prefix' => getenv('TRAVIS_JOB_NUMBER') ? 'travis-' . getenv('TRAVIS_JOB_NUMBER') : 'liveblog-test',
    ];
    $this->setFieldValues($page, $fieldValues);
    $this->click('input[data-drupal-selector="edit-submit"]');

    $this->waitUntilVisible('.messages--status');

    $this->logWithRole(static::$defaultUserRole);

    // Add liveblog node.
    $fieldValues = [
      'title[0][value]' => 'Test Liveblog',
      'field_highlights[values][6]' => 'element',
      'field_posts_number_initial[0][value]' => '1',
    ];

    $this->drupalGet('node/add/liveblog');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->expandAllTabs(1);
    $this->setFieldValues($this->getSession()->getPage(), $fieldValues);
    // 1 saves it as published in this case.
    $this->setPublishedStatus(TRUE);
    $this->clickSave();

    // Add first post.
    $page = $this->getSession()->getPage();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->liveblogSetTitle($page, 'Normal post');
    $this->liveblogSetBody("This is a normal text");
    $this->clickButtonDrupalSelector($page, "edit-submit");

    $this->waitUntilVisible('article[data-postid="1"]', 10000);

    // Add post with image.
    $this->liveblogSetTitle($page, 'Image post');

    $this->clickDropButton('field_embed_media_image_add_more', FALSE);

    $this->selectMedia("field_embed_media_0_subform_field_image", 'image_browser', ['media:1']);

    $this->liveblogSetBody('Very nice image post you have here!');

    $this->clickButtonDrupalSelector($page, "edit-submit");
    $this->createScreenshot($this->getScreenshotFolder() . '/ModuleIntegrationTest_Liveblog_ImagePost_' . date('Ymd_His') . '.png');

    $this->waitUntilVisible('article[data-postid="2"]', 10000);
    $this->waitUntilVisible('article[data-postid="2"] img.b-loaded', 10000);

    // Add post with twitter.
    $this->liveblogSetTitle($page, 'Twitter post');

    $this->createScreenshot($this->getScreenshotFolder() . '/ModuleIntegrationTest_Liveblog_TwitterPost_Add_' . date('Ymd_His') . '.png');
    $this->clickDropButton('field_embed_media_twitter_add_more');
    $this->waitUntilVisible('[name="field_embed_media[0][subform][field_media][0][inline_entity_form][field_url][0][uri]"]', 10000);
    $this->setFieldValue($page,
      'field_embed_media[0][subform][field_media][0][inline_entity_form][field_url][0][uri]',
      'https://twitter.com/tweetsauce/status/778001033142284288'
    );

    $this->liveblogSetBody('Very nice twitter post you have here!');

    $this->clickButtonDrupalSelector($page, "edit-submit");
    $this->createScreenshot($this->getScreenshotFolder() . '/ModuleIntegrationTest_Liveblog_TwitterPost_' . date('Ymd_His') . '.png');

    $this->waitUntilVisible('article[data-postid="3"]', 10000);
    $this->waitUntilVisible('[data-tweet-id="778001033142284288"].twitter-tweet-rendered', 10000);

    // We can't check inside Twitter widget is it loaded or not, that's why
    // plain wait is used.
    $this->getSession()->wait(5000);

    // Check site with anonymous user.
    $url = $this->getUrl();
    $this->drupalLogout();

    $this->drupalGet($url);

    $this->waitUntilVisible('article[data-postid="3"]');
    $this->assertSession()->elementNotExists('css', 'article[data-postid="2"]');
    $this->assertSession()->elementNotExists('css', 'article[data-postid="1"]');

    $this->scrollElementInView('article[data-postid="3"]');
    $this->waitUntilVisible('article[data-postid="2"]');
    $this->waitUntilVisible('article[data-postid="1"]');
  }

}
