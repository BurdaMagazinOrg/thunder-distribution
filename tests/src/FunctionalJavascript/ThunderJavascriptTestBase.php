<?php

namespace Drupal\Tests\thunder\FunctionalJavascript;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Exception\DriverException;
use Drupal\FunctionalJavascriptTests\DrupalSelenium2Driver;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\thunder\Traits\ThunderTestTrait;
use Zumba\GastonJS\Exception\DeadClient;
use Zumba\Mink\Driver\PhantomJSDriver;

/**
 * Base class for Thunder Javascript functional tests.
 *
 * @package Drupal\Tests\thunder\FunctionalJavascript
 */
abstract class ThunderJavascriptTestBase extends WebDriverTestBase {

  use ThunderTestTrait;
  use ThunderImageCompareTestTrait;

  /**
   * Modules to enable.
   *
   * The test runner will merge the $modules lists from this class, the class
   * it extends, and so on up the class hierarchy. It is not necessary to
   * include modules in your list that a parent class has already declared.
   *
   * @var string[]
   *
   * @see \Drupal\Tests\BrowserTestBase::installDrupal()
   */
  protected static $modules = ['thunder_demo', 'content_moderation'];

  /**
   * The profile to install as a basis for testing.
   *
   * @var string
   */
  protected $profile = 'thunder';

  /**
   * Directory path for saving screenshots.
   *
   * @var string
   */
  protected $screenshotDirectory = '/tmp/thunder-travis-ci';

  /**
   * Default user login role used for testing.
   *
   * @var string
   */
  protected static $defaultUserRole = 'editor';

  /**
   * {@inheritdoc}
   */
  protected function initMink() {
    if ($this->minkDefaultDriverClass === DrupalSelenium2Driver::class) {
      $host = '127.0.0.1:4444';
      if (!empty(getenv('THUNDER_WEBDRIVER_HOST'))) {
        $host = getenv('THUNDER_WEBDRIVER_HOST');
      }
      $this->minkDefaultDriverArgs = ['chrome', NULL, "http://$host/wd/hub"];
    }
    elseif ($this->minkDefaultDriverClass === PhantomJSDriver::class) {
      // Set up the template cache used by the PhantomJS mink driver.
      $path = $this->tempFilesDirectory . DIRECTORY_SEPARATOR . 'browsertestbase-templatecache';
      $this->minkDefaultDriverArgs = [
        'http://127.0.0.1:8510',
        $path,
      ];
      if (!file_exists($path)) {
        mkdir($path);
      }
    }

    try {
      return BrowserTestBase::initMink();
    }
    catch (DeadClient $e) {
      $this->markTestSkipped('PhantomJS is either not installed or not running. Start it via phantomjs --ssl-protocol=any --ignore-ssl-errors=true vendor/jcalderonzumba/gastonjs/src/Client/main.js 8510 1024 768&');
    }
    catch (DriverException $e) {
      if ($this->minkDefaultDriverClass === DrupalSelenium2Driver::class) {
        $this->markTestSkipped("The test wasn't able to connect to your webdriver instance. For more information read core/tests/README.md.\n\nThe original message while starting Mink: {$e->getMessage()}");
      }
      else {
        throw $e;
      }
    }
    catch (\Exception $e) {
      $this->markTestSkipped('An unexpected error occurred while starting Mink: ' . $e->getMessage());
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->logWithRole(static::$defaultUserRole);

    // Set window width/height.
    $windowSize = $this->getWindowSize();
    $this->getSession()->getDriver()->resizeWindow($windowSize['width'], $windowSize['height']);

    // Set flag to generate screenshots instead of comparing them.
    if (!empty($_SERVER['generateMode'])) {
      $this->setGenerateMode(strtolower($_SERVER['generateMode']) === 'true');
    }
  }

  /**
   * Get base window size.
   *
   * @return array
   *   Return
   */
  protected function getWindowSize() {
    return [
      'width' => 1280,
      'height' => 768,
    ];
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
  public function waitUntilVisible($selector, $timeout = 1000, $message = '') {
    $condition = "jQuery('" . $selector . ":visible').length > 0";
    $this->assertJsCondition($condition, $timeout, $message);
  }

  /**
   * Wait for images to load.
   *
   * This functionality is sometimes need, because positions of elements can be
   * changed in middle of execution and make problems with execution of clicks
   * or other position depending actions. Image property complete is used.
   *
   * @param string $cssSelector
   *   Css selector, but without single quotes.
   * @param int $total
   *   Total number of images that should selected with provided css selector.
   * @param int $time
   *   Waiting time, by default 10sec.
   */
  public function waitForImages($cssSelector, $total, $time = 10000) {
    $this->getSession()
      ->wait($time, "jQuery('{$cssSelector}').filter(function(){return jQuery(this).prop('complete');}).length === {$total}");
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
    $dir = $this->screenshotDirectory;

    // Use Travis Job ID for sub folder.
    $travisId = getenv('TRAVIS_JOB_ID');
    if (!empty($travisId)) {
      $dir .= '/' . $travisId;
    }

    if (!is_dir($dir)) {
      if (mkdir($dir, 0777, TRUE) === FALSE) {
        throw new \Exception('Unable to create directory: ' . $dir);
      }
    }

    return realpath($dir);
  }

  /**
   * Scroll element with defined css selector in middle of browser view.
   *
   * @param string $cssSelector
   *   CSS Selector for element that should be centralized.
   */
  public function scrollElementInView($cssSelector) {
    $this->getSession()
      ->executeScript('
        var viewPortHeight = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
        var element = jQuery(\'' . addcslashes($cssSelector, '\'') . '\');
        var scrollTop = element.offset().top - (viewPortHeight/2);
        var scrollableParent = jQuery.isFunction(element.scrollParent) ? element.scrollParent() : [];
        if (scrollableParent.length > 0 && scrollableParent[0] !== document && scrollableParent[0] !== document.body) { scrollableParent[0].scrollTop = scrollTop } else { window.scroll(0, scrollTop); };
      ');
  }

  /**
   * Click on Button based on Drupal selector (data-drupal-selector).
   *
   * @param \Behat\Mink\Element\DocumentElement $page
   *   Current active page.
   * @param string $drupalSelector
   *   Drupal selector.
   * @param bool $waitAfterAction
   *   Flag to wait for AJAX request to finish after click.
   */
  public function clickButtonDrupalSelector(DocumentElement $page, $drupalSelector, $waitAfterAction = TRUE) {
    $this->clickButtonCssSelector($page, '[data-drupal-selector="' . $drupalSelector . '"]', $waitAfterAction);
  }

  /**
   * Click on Button based on Drupal selector (data-drupal-selector).
   *
   * @param \Behat\Mink\Element\DocumentElement $page
   *   Current active page.
   * @param string $cssSelector
   *   Drupal selector.
   * @param bool $waitAfterAction
   *   Flag to wait for AJAX request to finish after click.
   */
  public function clickButtonCssSelector(DocumentElement $page, $cssSelector, $waitAfterAction = TRUE) {
    $this->scrollElementInView($cssSelector);
    $editButton = $page->find('css', $cssSelector);
    $editButton->click();

    if ($waitAfterAction) {
      $this->assertSession()->assertWaitOnAjaxRequest();
    }
  }

  /**
   * Click on Ajax Button based on CSS selector.
   *
   * Ajax buttons handler is triggered on "mousedown" event, so it has to be
   * triggered over JavaScript.
   *
   * @param string $cssSelector
   *   CSS selector.
   * @param bool $waitAfterAction
   *   Flag to wait for AJAX request to finish after click.
   */
  public function clickAjaxButtonCssSelector($cssSelector, $waitAfterAction = TRUE) {
    $this->scrollElementInView($cssSelector);
    $this->getSession()->executeScript("jQuery('{$cssSelector}').trigger('mousedown');");

    if ($waitAfterAction) {
      $this->assertSession()->assertWaitOnAjaxRequest();
    }
  }

  /**
   * Click a button within a dropdown button field.
   *
   * @param string $fieldName
   *   The [name] attribute of the button to be clicked.
   * @param bool $toggle
   *   Whether the dropdown button should be expanded before clicking.
   */
  protected function clickDropButton($fieldName, $toggle = TRUE) {
    $page = $this->getSession()->getPage();

    if ($toggle) {
      $toggleButtonXpath = '//ul[.//*[@name="' . $fieldName . '"]]/li[contains(@class,"dropbutton-toggle")]/button';
      $toggleButton = $page->find('xpath', $toggleButtonXpath);
      $toggleButton->click();
      $this->assertSession()->assertWaitOnAjaxRequest();
    }

    $this->scrollElementInView('[name="' . $fieldName . '"]');

    $page->pressButton($fieldName);
    $this->assertSession()->assertWaitOnAjaxRequest();
  }

  /**
   * Assert page title.
   *
   * @param string $expectedTitle
   *   Expected title.
   */
  protected function assertPageTitle($expectedTitle) {
    $driver = $this->getSession()->getDriver();
    if ($driver instanceof Selenium2Driver) {
      $actualTitle = $driver->getWebDriverSession()->title();

      static::assertEquals($expectedTitle, $actualTitle, 'Title found');
    }
    else {
      $this->assertSession()->titleEquals($expectedTitle);
    }
  }

  /**
   * Fill CKEditor field.
   *
   * @param string $ckEditorCssSelector
   *   CSS selector for CKEditor.
   * @param string $text
   *   Text that will be filled into CKEditor.
   */
  public function fillCkEditor($ckEditorCssSelector, $text) {
    $ckEditorId = $this->getCkEditorId($ckEditorCssSelector);

    $this->getSession()
      ->getDriver()
      ->executeScript("CKEDITOR.instances[\"$ckEditorId\"].insertHtml(\"$text\");");
  }

  /**
   * Select CKEditor element.
   *
   * @param string $ckEditorCssSelector
   *   CSS selector for CKEditor.
   * @param int $childIndex
   *   The child index under the node.
   */
  public function selectCkEditorElement($ckEditorCssSelector, $childIndex) {
    $ckEditorId = $this->getCkEditorId($ckEditorCssSelector);

    $this->getSession()
      ->getDriver()
      ->executeScript("let selection = CKEDITOR.instances[\"$ckEditorId\"].getSelection(); selection.selectElement(selection.root.getChild($childIndex)); var ranges = selection.getRanges(); ranges[0].setEndBefore(ranges[0].getBoundaryNodes().endNode); selection.selectRanges(ranges);");
  }

  /**
   * Assert that CKEditor instance contains correct data.
   *
   * @param string $ckEditorCssSelector
   *   CSS selector for CKEditor.
   * @param string $expectedContent
   *   The expected content.
   */
  public function assertCkEditorContent($ckEditorCssSelector, $expectedContent) {
    $ckEditorId = $this->getCkEditorId($ckEditorCssSelector);
    $ckEditorContent = $this->getSession()
      ->getDriver()
      ->evaluateScript("return CKEDITOR.instances[\"$ckEditorId\"].getData();");

    static::assertEquals($expectedContent, $ckEditorContent);
  }

  /**
   * Set value directly to field value, without formatting applied.
   *
   * @param string $fieldName
   *   Field name.
   * @param string $rawValue
   *   Raw value for field.
   */
  public function setRawFieldValue($fieldName, $rawValue) {
    // Set date over jQuery, because browser drivers handle input value
    // differently. fe. (Firefox will set it as "value" for field, but Chrome
    // will use it as text for that input field, and in that case final value
    // depends on format used for input field. That's why it's better to set it
    // directly to value, independently from format used.
    $this->getSession()
      ->executeScript("jQuery('[name=\"{$fieldName}\"]').val('{$rawValue}')");
  }

  /**
   * Expand all tabs on page.
   *
   * It goes up to level 3 by default.
   *
   * @param int $maxLevel
   *   Max depth of nested collapsed tabs.
   */
  public function expandAllTabs($maxLevel = 3) {
    $jsScript = 'jQuery(\'details.js-form-wrapper.form-wrapper:not([open]) > summary\').click().length';

    $numOfOpen = $this->getSession()->evaluateScript($jsScript);
    $this->assertSession()->assertWaitOnAjaxRequest();

    for ($i = 0; $i < $maxLevel && $numOfOpen > 0; $i++) {
      $numOfOpen = $this->getSession()->evaluateScript($jsScript);
      $this->assertSession()->assertWaitOnAjaxRequest();
    }
  }

  /**
   * Execute Cron over UI.
   */
  public function runCron() {
    $this->drupalGet('admin/config/system/cron');

    $this->getSession()
      ->getPage()
      ->find('xpath', '//input[@name="op"]')
      ->click();
  }

  /**
   * Click article save.
   */
  protected function clickSave() {
    $page = $this->getSession()->getPage();

    $page->find('xpath', '//div[@data-drupal-selector="edit-actions"]/input[@id="edit-submit"]')
      ->click();
  }

  /**
   * Set entity status.
   *
   * TRUE - Published.
   * FALSE - Unpublished.
   *
   * @param bool $status
   *   Entity published or not.
   */
  protected function setPublishedStatus($status = TRUE) {

    $page = $this->getSession()->getPage();

    $this->scrollElementInView('#edit-status-value');

    if ($status) {
      $page->find('xpath', '//*[@id="edit-status-value"]')
        ->check();
    }
    else {
      $page->find('xpath', '//*[@id="edit-status-value"]')
        ->uncheck();
    }
  }

  /**
   * Set moderation state.
   *
   * @param string $state
   *   State id.
   */
  protected function setModerationState($state) {

    $page = $this->getSession()->getPage();

    $page->find('xpath', '//*[@id="edit-moderation-state-0"]')
      ->selectOption($state);
  }

  /**
   * Checks if pull request is from fork.
   *
   * @return bool
   *   Returns if pull request is from Fork.
   */
  protected function isForkPullRequest() {
    $pullRequestSlag = getenv('TRAVIS_PULL_REQUEST_SLUG');
    $repoSlag = getenv('TRAVIS_REPO_SLUG');

    return (!empty($pullRequestSlag) && $pullRequestSlag !== $repoSlag);
  }

  /**
   * Get CKEditor id from css selector.
   *
   * @param string $ckEditorCssSelector
   *   CSS selector for CKEditor.
   *
   * @return string
   *   CKEditor ID.
   */
  protected function getCkEditorId($ckEditorCssSelector) {
    // Since CKEditor requires some time to initialize, we are going to wait for
    // CKEditor instance to be ready before we continue and return ID.
    $this->getSession()->wait(10000, "(waitForCk = CKEDITOR.instances[jQuery(\"{$ckEditorCssSelector}\").attr('id')]) && waitForCk.instanceReady");

    $ckEditor = $this->getSession()->getPage()->find(
      'css',
      $ckEditorCssSelector
    );

    return $ckEditor->getAttribute('id');
  }

}
