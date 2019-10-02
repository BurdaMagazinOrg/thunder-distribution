<?php

namespace Drupal\Tests\thunder\FunctionalJavascript\Integration;

use Drupal\Tests\thunder\FunctionalJavascript\ThunderJavascriptTestBase;

/**
 * Tests the device preview functionality integration.
 *
 * @group Thunder
 */
class ResponsivePreview extends ThunderJavascriptTestBase {

  /**
   * Testing integration of "responsive_preview" module.
   */
  public function testDevicePreview() {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert_session */
    $assert_session = $this->assertSession();

    /** @var \Behat\Mink\Session $session */
    $session = $this->getSession();

    // Check channel page.
    $this->drupalGet('news');

    // The selection of device should create overlay with iframe to news page.
    $this->selectDevice('(//*[@id="responsive-preview-toolbar-tab"]//button[@data-responsive-preview-name])[1]');
    $assert_session->elementNotExists('xpath', '//*[@id="responsive-preview-orientation" and contains(@class, "rotated")]');
    $this->assertTrue($session->evaluateScript("jQuery('#responsive-preview-frame')[0].contentWindow.location.href.endsWith('/news')"));

    // Clicking of rotate should rotate iframe sizes.
    $current_width = $session->evaluateScript("jQuery('#responsive-preview-frame').width()");
    $current_height = $session->evaluateScript("jQuery('#responsive-preview-frame').height()");
    $this->changeDeviceRotation();
    $assert_session->elementExists('xpath', '//*[@id="responsive-preview-orientation" and contains(@class, "rotated")]');
    $this->assertEqual($current_height, $session->evaluateScript("jQuery('#responsive-preview-frame').width()"));
    $this->assertEqual($current_width, $session->evaluateScript("jQuery('#responsive-preview-frame').height()"));

    // Switching of device should keep rotation.
    $this->selectDevice('(//*[@id="responsive-preview-toolbar-tab"]//button[@data-responsive-preview-name])[last()]');
    $assert_session->elementExists('xpath', '//*[@id="responsive-preview-orientation" and contains(@class, "rotated")]');
    $this->changeDeviceRotation();
    $assert_session->elementNotExists('xpath', '//*[@id="responsive-preview-orientation" and contains(@class, "rotated")]');

    // Clicking on preview close, should remove overlay.
    $this->getSession()
      ->getPage()
      ->find('xpath', '//*[@id="responsive-preview-close"]')
      ->click();
    $this->getSession()
      ->wait(5000, "jQuery('#responsive-preview').length === 0");
    $assert_session->elementNotExists('xpath', '//*[@id="responsive-preview"]');

    $this->drupalGet('node/8/edit');

    // Using preview on entity edit should use preview page.
    $this->selectDevice('(//*[@id="responsive-preview-toolbar-tab"]//button[@data-responsive-preview-name])[1]');
    $this->assertNotEqual(-1, $session->evaluateScript("jQuery('#responsive-preview-frame')[0].contentWindow.location.href.indexOf('/node/preview/')"));
    $this->changeDeviceRotation();

    // Un-checking device from dropdown should turn off preview.
    $this->selectDevice('(//*[@id="responsive-preview-toolbar-tab"]//button[@data-responsive-preview-name])[1]');
    $this->getSession()
      ->wait(5000, "jQuery('#responsive-preview').length === 0");
    $assert_session->elementNotExists('xpath', '//*[@id="responsive-preview"]');
  }

  /**
   * Change device rotation for device preview.
   */
  protected function changeDeviceRotation() {
    $this->getSession()
      ->getPage()
      ->find('xpath', '//*[@id="responsive-preview-orientation"]')
      ->click();
    $this->assertSession()->assertWaitOnAjaxRequest();
  }

  /**
   * Select device for device preview.
   *
   * NOTE: Index starts from 1.
   *
   * @param int $xpath_device_button
   *   The index number of device in drop-down list.
   */
  protected function selectDevice($xpath_device_button) {
    $page = $this->getSession()->getPage();

    $page->find('xpath', '//*[@id="responsive-preview-toolbar-tab"]/button')
      ->click();
    $this->assertSession()->assertWaitOnAjaxRequest();

    $page->find('xpath', $xpath_device_button)->click();
    $this->assertSession()->assertWaitOnAjaxRequest();
  }

}
