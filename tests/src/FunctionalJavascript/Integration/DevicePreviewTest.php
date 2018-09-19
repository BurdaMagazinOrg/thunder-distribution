<?php

namespace Drupal\Tests\thunder\FunctionalJavascript\Integration;

use Drupal\Tests\thunder\FunctionalJavascript\ThunderJavascriptTestBase;

/**
 * Test for device preview integration.
 *
 * @group Thunder
 *
 * @package Drupal\Tests\thunder\FunctionalJavascript\Integration
 */
class DevicePreviewTest extends ThunderJavascriptTestBase {

  /**
   * Testing integration of "device_preview" module.
   */
  public function testDevicePreview() {
    $windowSize = $this->getWindowSize();

    // Check channel page.
    $this->drupalGet('news');

    $topChannelCssSelector = 'a[href$="burda-launches-open-source-cms-thunder"]';
    $midChannelCssSelector = 'a[href$="duis-autem-vel-eum-iriure"]';

    $windowSize['height'] = 950;
    $this->setWindowSize($windowSize);
    $this->selectDevice('iphone_7');
    $this->scrollToInDevicePreview($topChannelCssSelector);
    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test_device_preview_ch1')));
    $this->scrollToInDevicePreview($midChannelCssSelector);
    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test_device_preview_ch2')));

    $this->changeDeviceRotation();
    $this->scrollToInDevicePreview($topChannelCssSelector);
    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test_device_preview_ch3')));
    $this->scrollToInDevicePreview($midChannelCssSelector);
    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test_device_preview_ch4')));

    $windowSize['height'] = 1280;
    $this->setWindowSize($windowSize);
    $this->selectDevice('ipad_air_2');
    $this->scrollToInDevicePreview($topChannelCssSelector);
    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test_device_preview_ch5')));
    $this->scrollToInDevicePreview($midChannelCssSelector);
    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test_device_preview_ch6')));

    $this->changeDeviceRotation();
    $this->scrollToInDevicePreview($topChannelCssSelector);
    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test_device_preview_ch7')));
    $this->scrollToInDevicePreview($midChannelCssSelector);
    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test_device_preview_ch8')));

    $this->getSession()->getPage()->find('xpath', '//*[@id="responsive-preview-close"]')->click();

    // Testing of preview for single article.
    $topArticleCssSelector = '#block-thunder-base-content div.node__meta';
    $midArticleCssSelector = '#block-thunder-base-content div.field__items > div.field__item:nth-child(3)';
    $bottomArticleCssSelector = 'div.shariff';

    // Wait for CSS easing javascript.
    $waitTopImage = "jQuery('#block-thunder-base-content div.field__items > div.field__item:nth-child(1) img.b-loaded').css('opacity') === '1'";
    $waitMidGallery = "jQuery('#block-thunder-base-content div.field__items > div.field__item:nth-child(3) div.slick-active img.b-loaded').css('opacity') === '1'";

    $this->drupalGet('node/8/edit');

    $windowSize['height'] = 950;
    $this->setWindowSize($windowSize);
    $this->selectDevice('iphone_7');
    $this->scrollToInDevicePreview($topArticleCssSelector);
    $this->getSession()->wait(5000, $waitTopImage);
    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test_device_preview_ar1')));
    $this->scrollToInDevicePreview($midArticleCssSelector);
    $this->getSession()->wait(5000, $waitMidGallery);
    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test_device_preview_ar2')));
    $this->scrollToInDevicePreview($bottomArticleCssSelector);
    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test_device_preview_ar3')));

    $this->changeDeviceRotation();
    $this->scrollToInDevicePreview($topArticleCssSelector);
    $this->getSession()->wait(5000, $waitTopImage);
    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test_device_preview_ar4')));
    $this->scrollToInDevicePreview($midArticleCssSelector);
    $this->getSession()->wait(5000, $waitMidGallery);
    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test_device_preview_ar5')));
    $this->scrollToInDevicePreview($bottomArticleCssSelector);
    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test_device_preview_ar6')));

    $windowSize['height'] = 1280;
    $this->setWindowSize($windowSize);
    $this->selectDevice('ipad_air_2');
    $this->scrollToInDevicePreview($topArticleCssSelector);
    $this->getSession()->wait(5000, $waitTopImage);
    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test_device_preview_ar7')));
    $this->scrollToInDevicePreview($midArticleCssSelector);
    $this->getSession()->wait(5000, $waitMidGallery);
    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test_device_preview_ar8')));
    $this->scrollToInDevicePreview($bottomArticleCssSelector);
    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test_device_preview_ar9')));

    $this->changeDeviceRotation();
    $this->scrollToInDevicePreview($topArticleCssSelector);
    $this->getSession()->wait(5000, $waitTopImage);
    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test_device_preview_ar10')));
    $this->scrollToInDevicePreview($midArticleCssSelector);
    $this->getSession()->wait(5000, $waitMidGallery);
    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test_device_preview_ar11')));
    $this->scrollToInDevicePreview($bottomArticleCssSelector);
    $this->assertTrue($this->compareScreenToImage($this->getScreenshotFile('test_device_preview_ar12')));
  }

  /**
   * Select device for device preview.
   *
   * @param string $deviceId
   *   Identifier name for device.
   */
  protected function selectDevice($deviceId) {
    $page = $this->getSession()->getPage();

    $page->find('xpath', '//*[@id="responsive-preview-toolbar-tab"]/button')
      ->click();
    $this->assertSession()->assertWaitOnAjaxRequest();

    $page->find('xpath', "//*[@id=\"responsive-preview-toolbar-tab\"]//button[@data-responsive-preview-name=\"{$deviceId}\"]")
      ->click();
    $this->assertSession()->assertWaitOnAjaxRequest();
  }

  /**
   * Scroll to CSS selector element inside device preview.
   *
   * @param string $cssSelector
   *   CSS selector to scroll in view.
   */
  protected function scrollToInDevicePreview($cssSelector) {
    $this->getSession()->switchToIFrame('responsive-preview-frame');
    $this->assertSession()->assertWaitOnAjaxRequest();

    $this->scrollElementInView($cssSelector);
    $this->assertSession()->assertWaitOnAjaxRequest();

    $this->getSession()->switchToIFrame();
    $this->assertSession()->assertWaitOnAjaxRequest();
  }

  /**
   * Change device rotation for device preview.
   */
  protected function changeDeviceRotation() {
    $this->getSession()->getPage()->find('xpath', '//*[@id="responsive-preview-orientation"]')->click();
    $this->assertSession()->assertWaitOnAjaxRequest();
  }

}
