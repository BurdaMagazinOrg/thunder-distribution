<?php

namespace Drupal\Tests\thunder\FunctionalJavascript;

use Behat\Mink\Driver\Selenium2Driver;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AnonymousUserSession;
use Drupal\FunctionalJavascriptTests\JavascriptTestBase;
use Drupal\Tests\BrowserTestBase;

/**
 * Base class for Thunder Javascript functional tests.
 *
 * @package Drupal\Tests\thunder\FunctionalJavascript
 */
abstract class ThunderJavascriptTestBase extends JavascriptTestBase {

  /**
   * The profile to install as a basis for testing.
   *
   * @var string
   */
  protected $profile = 'thunder';

  /**
   * {@inheritdoc}
   */
  protected $minkDefaultDriverClass = Selenium2Driver::class;

  /**
   * Directory path for saving screenshots.
   *
   * @var string
   */
  protected $screenshotDirectory = '/tmp/thunder-travis-ci';

  /**
   * {@inheritdoc}
   */
  protected function initMink() {
    // Set up the template cache used by the PhantomJS mink driver.
    $path = $this->tempFilesDirectory . DIRECTORY_SEPARATOR . 'browsertestbase-templatecache';

    $this->minkDefaultDriverArgs = [
      'firefox',
      NULL,
      'http://127.0.0.1:4444/wd/hub',
    ];

    if (!file_exists($path)) {
      mkdir($path);
    }

    try {
      return BrowserTestBase::initMink();
    }
    catch (Exception $e) {
      $this->markTestSkipped('An unexpected error occurred while starting Mink: ' . $e->getMessage());
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function drupalLogin(AccountInterface $account) {
    if ($this->loggedInUser) {
      $this->drupalLogout();
    }

    $this->drupalGet('user');
//    $this->assertSession()->statusCodeEquals(200);
    $this->submitForm(array(
      'name' => $account->getUsername(),
      'pass' => $account->passRaw,
    ), t('Log in'));

    // @see BrowserTestBase::drupalUserIsLoggedIn()
    $account->sessionId = $this->getSession()->getCookie($this->getSessionName());
    $this->assertTrue($this->drupalUserIsLoggedIn($account), SafeMarkup::format('User %name successfully logged in.', array('name' => $account->getUsername())));

    $this->loggedInUser = $account;
    $this->container->get('current_user')->setAccount($account);
  }

  /**
   * {@inheritdoc}
   */
  protected function drupalLogout() {
    // Make a request to the logout page, and redirect to the user page, the
    // idea being if you were properly logged out you should be seeing a login
    // screen.
    $assert_session = $this->assertSession();
    $this->drupalGet('user/logout', array('query' => array('destination' => 'user')));
    $assert_session->fieldExists('name');
    $assert_session->fieldExists('pass');

    // @see BrowserTestBase::drupalUserIsLoggedIn()
    unset($this->loggedInUser->sessionId);
    $this->loggedInUser = FALSE;
    $this->container->get('current_user')->setAccount(new AnonymousUserSession());
  }

  protected function setUp() {

    parent::setUp();

    $editor = $this->drupalCreateUser();
    $editor->addRole('editor');
    $editor->save();
    $this->drupalLogin($editor);
  }

  protected function openIefComplex($fieldName) {

    $page = $this->getSession()->getPage();

    $selector = "div[data-drupal-selector='edit-" . str_replace('_', '-', $fieldName) . "-wrapper'] > div";

    $this->assertSession()->elementExists('css', $selector);

    $iefForm = $page->find('css', $selector);

    $iefId = $iefForm->getAttribute('id');

    $page->pressButton(str_replace('inline-entity-form', 'ief', $iefId) . '-add');

    $this->assertSession()->assertWaitOnAjaxRequest();
  }

  /**
   * Waits and asserts that a given element is visible.
   *
   * @param string $selector
   *   The CSS selector.
   * @param int $timeout
   *   (Optional) Timeout in milliseconds, defaults to 1000.
   * @param string $message
   *   (Optional) Message to pass to assertJsCondition().
   */
  protected function waitUntilVisible($selector, $timeout = 1000, $message = '') {
    $condition = "jQuery('" . $selector . ":visible').length > 0";
    $this->assertJsCondition($condition, $timeout, $message);
  }

  /**
   * Get directory for saving of screenshots.
   *
   * Directory will be created if it does not already exist.
   *
   * @return string
   *   Return directory path to store screenshots.
   *
   * @throws \Exception
   */
  protected function getScreenshotFolder() {
    if (!is_dir($this->screenshotDirectory)) {
      if (mkdir($this->screenshotDirectory, 0777, TRUE) === FALSE) {
        throw new \Exception('Unable to create directory: ' . $this->screenshotDirectory);
      }
    }

    return realpath($this->screenshotDirectory);
  }

  /**
   * Scroll element with defined css selector in middle of browser view.
   *
   * @param string $cssSelector
   *   CSS Selector for element that should be centralized.
   */
  protected function scrollElementInView($cssSelector) {
    $this->getSession()->executeScript('var viewPortHeight = Math.max(document.documentElement.clientHeight, window.innerHeight || 0); var elementTop = jQuery(\'' . $cssSelector . '\').offset().top; window.scroll(0, elementTop-(viewPortHeight/2));');
  }
}