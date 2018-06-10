<?php

namespace Drupal\Tests\thunder\FunctionalJavascript\Integration;

use Drupal\Tests\thunder\FunctionalJavascript\ThunderJavascriptTestBase;

/**
 * Tests integration with the amp.
 *
 * @group Thunder
 */
class AmpTest extends ThunderJavascriptTestBase {

  /**
   * Testing integration of "AMP" module and theme.
   */
  public function testAmpIntegration() {
    if (!\Drupal::service('theme_installer')->install(['thunder_amp'])) {
      $this->fail("thunder_amp theme couldn't be installed.");
      return;
    }

    $this->drupalGet('/node/6', ['query' => ['amp' => 1]]);

    // Text paragraph.
    $this->assertSession()->pageTextContains('Board Member Philipp Welte explains');

    // Image paragraph.
    $this->assertSession()->elementExists('css', '.paragraph--type--image amp-img');
    $this->assertSession()->waitForElementVisible('css', '.paragraph--type--image amp-img img');

    $this->drupalGet('/node/7', ['query' => ['amp' => 1], 'fragment' => 'development=1']);

    // Gallery paragraph.
    $this->assertSession()->elementExists('css', '.paragraph--type--gallery amp-carousel');
    // Images in gallery paragraph.
    $this->assertSession()->waitForElementVisible('css', '.paragraph--type--gallery amp-carousel amp-img');
    $this->assertSession()->elementsCount('css', '.paragraph--type--gallery amp-carousel amp-img', 5);

    // Instagram Paragraph.
    $this->assertSession()->elementExists('css', '.paragraph--type--instagram amp-instagram[data-shortcode="2rh_YmDglx"]');
    $this->assertSession()->waitForElementVisible('css', '.paragraph--type--instagram amp-instagram[data-shortcode="2rh_YmDglx"] iframe');

    // Video Paragraph.
    $this->assertSession()->elementExists('css', '.paragraph--type--video amp-youtube[data-videoid="Ksp5JVFryEg"]');
    $this->assertSession()->waitForElementVisible('css', '.paragraph--type--video amp-youtube[data-videoid="Ksp5JVFryEg"] iframe');

    // Twitter Paragraph.
    $this->assertSession()->elementExists('css', '.paragraph--type--twitter amp-twitter[data-tweetid="731057647877787648"]');
    $this->assertSession()->waitForElementVisible('css', '.paragraph--type--twitter amp-twitter[data-tweetid="731057647877787648"] iframe');

    $this->getSession()->executeScript('AMPValidationSuccess = false; console.info = function(message) { if (message === "AMP validation successful.") { AMPValidationSuccess = true } }; amp.validator.validateUrlAndLog(document.location.href, document);');
    $this->assertJsCondition('AMPValidationSuccess === true', 10000, 'AMP validation successful.');
  }

}
