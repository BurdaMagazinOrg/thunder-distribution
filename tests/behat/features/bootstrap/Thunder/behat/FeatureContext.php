<?php

namespace Thunder\behat;

use Behat\Behat\Context\SnippetAcceptingContext;
use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Class FeatureContext.
 *
 * Defines application features from the specific context.
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext {

  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {
  }

  /**
   * Set screen size before test.
   *
   * @BeforeScenario
   */
  public function beforeScenario() {
    $this->getSession()->getDriver()->resizeWindow(1680, 3150);
  }

  /**
   * Get defined Region.
   *
   * @param string $region
   *   Region name defined in YAML file.
   *
   * @return \Behat\Mink\Element\NodeElement|mixed|null
   *   Returns element when found, otherwise throws exception.
   *
   * @throws \Exception
   */
  public function getRegion($region) {
    $session = $this->getSession();
    $regionObj = $session->getPage()->find('region', $region);
    if (!$regionObj) {
      throw new \Exception(
        sprintf(
          'No region "%s" found on the page %s.',
          $region,
          $session->getCurrentUrl()
        )
      );
    }

    return $regionObj;
  }

  /**
   * Wait for AJAX to finish, so that content on page is updated.
   *
   * @Given I wait for page to load content
   */
  public function iWaitForPageToUpdate() {
    $this->getSession()
      ->wait(5000, '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))');
  }

  /**
   * Checks on drop-down button option in defined region.
   *
   * @param string $option
   *   The option text of the button to be pressed.
   * @param string $region
   *   The region in which the button should be pressed.
   *
   * @throws \Exception
   *   If region or button within it cannot be found.
   *
   * @When I press :option for drop-down button in the :region( region)
   */
  public function iClickOnDropDownButtonOptionInRegion($option, $region) {
    $regionObj = $this->getRegion($region);

    // Click drop-down button.
    $toggleButton = $regionObj->find('css', '.dropbutton-toggle');
    if (empty($toggleButton)) {
      throw new \Exception(
        sprintf(
          'The drop-down button was not found in the region "%s" on the page %s',
          $region,
          $this->getSession()->getCurrentUrl()
        )
      );
    }
    $toggleButton->click();

    // Select option.
    $dropdownOption = $regionObj->find('named', array('button', $option));
    if (empty($dropdownOption)) {
      throw new \Exception(
        sprintf(
          'The drop-down option "%s" was not found in the region "%s" on the page %s',
          $option,
          $this->getSession()->getCurrentUrl()
        )
      );
    }

    $dropdownOption->click();
  }

  /**
   * File value in CKEditor from defined region.
   *
   * @param string $value
   *   Text that will be written in CKEditor.
   * @param string $region
   *   The region that contains CKEditor.
   *
   * @throws \Exception
   *
   * @Then I fill CKEditor with :value in the :region( region)
   */
  public function iFillInCkEditorInRegion($value, $region) {
    $regionObj = $this->getRegion($region);

    // Find CKEditor.
    $ckEditor = $regionObj->find('css', '.form-textarea');

    if (empty($ckEditor)) {
      throw new \Exception(
        sprintf(
          'CKEditor was not found in the region "%s" on the page %s',
          $region,
          $this->getSession()->getCurrentUrl()
        )
      );
    }
    $ckEditorId = $ckEditor->getAttribute('id');

    $this->getSession()
      ->executeScript("CKEDITOR.instances[\"$ckEditorId\"].setData(\"$value\");");
  }

  /**
   * Expand/Collapse option in defined region.
   *
   * @param string $option
   *   The option text of the button to be pressed.
   * @param string $region
   *   The region in which the menu option should be expanded/collapsed.
   *
   * @throws \Exception
   *   If region or menu option within it cannot be found.
   *
   * @When I expand/collapse :option option in the :region( region)
   */
  public function iToggleOptionInRegion($option, $region) {
    $regionObj = $this->getRegion($region);

    // Find menu option to expand/collapse.
    $xpathQuery = "//details/child::summary[text() = '$option']";
    $menuOption = $regionObj->find('xpath', $xpathQuery);

    // Sometimes it's rendered as link tag.
    if (empty($menuOption)) {
      $xpathQuery = "//details/summary/child::a[text() = '$option']";
      $menuOption = $regionObj->find('xpath', $xpathQuery);
    }

    if (empty($menuOption)) {
      throw new \Exception(
        sprintf(
          'Unable to find option "%s" in the region "%s" on the page %s',
          $option,
          $region,
          $this->getSession()->getCurrentUrl()
        )
      );
    }

    $menuOption->click();
  }

  /**
   * Upload file to drop zone of Entity selector.
   *
   * Mimic functionality by exposing file field and uploading over it.
   *
   * @param string $path
   *   File name used to be uploaded in Drop files field.
   *
   * @throws \Exception
   *   If file field is not found for drop down.
   *
   * @When I drop the file :path in drop zone and select it
   */
  public function dropFileInSelectEntities($path) {

    // Select entity browser iframe.
    $iframe = $this->getSession()
      ->getPage()
      ->find('css', 'iframe.entity-browser-modal-iframe');
    if (empty($iframe)) {
      throw new \Exception(
        sprintf(
          'Unable to find entity browser iframe on page %s',
          $this->getSession()->getCurrentUrl()
        )
      );
    }
    $iframeName = $iframe->getAttribute('name');

    // Go into iframe scope from Entity Browsers.
    $this->getSession()->switchToIFrame($iframeName);

    // Wait that iframe is loaded and jQuery is available.
    $this->getSession()->wait(10000, '(typeof jQuery !== "undefined")');

    // Click all tabs until we find upload Tab.
    $tabLinks = $this->getSession()->getPage()->findAll('css', '.eb-tabs a');
    if (empty($tabLinks)) {
      throw new \Exception(
        sprintf(
          'Unable to find tabs in entity browser iframe on page %s',
          $this->getSession()->getCurrentUrl()
        )
      );
    }

    // Click all tabs until input file field for upload is found.
    $fileFieldSelector = "input[type='file'].dz-hidden-input";
    foreach ($tabLinks as $tabLink) {
      /* @var \Behat\Mink\Element\NodeElement $tabLink */
      $tabLink->click();

      $fileField = $this->getSession()
        ->getPage()
        ->find('css', $fileFieldSelector);

      if (!empty($fileField)) {
        break;
      }
    }

    if (empty($fileField)) {
      throw new \Exception(
        sprintf(
          'The drop-down file field was not found on the page %s',
          $this->getSession()->getCurrentUrl()
        )
      );
    }

    // Make file field visible and isolate possible problems with "multiple".
    $this->getSession()
      ->executeScript('jQuery("' . $fileFieldSelector . '").show(0).css("visibility","visible").width(200).height(30).removeAttr("multiple");');

    // Generate full path to file.
    if ($this->getMinkParameter('files_path')) {
      $fullPath = rtrim(realpath($this->getMinkParameter('files_path')), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $path;
      if (is_file($fullPath)) {
        $path = $fullPath;
      }
    }

    $fileField->attachFile($path);

    // Wait for file to upload and use press Select button.
    $this->iWaitForPageToUpdate();

    // Wait up to 10 sec that "Submit" button is active.
    $this->getSession()->wait(
      10000,
      '(typeof jQuery === "undefined" || !jQuery(\'input[name="op"]\').is(":disabled"))'
    );

    // Go back to Page scope.
    $this->getSession()->switchToWindow();

    // Click Select button - inside iframe.
    $this->getSession()
      ->executeScript('document.querySelector(\'iframe[name="' . $iframeName . '"]\').contentWindow.jQuery(\'input[name="op"]\').click();');

    // Wait up to 10 sec that main page is loaded with new selected images.
    $this->getSession()->wait(
      10000,
      '(typeof jQuery === "undefined" || jQuery(\'.button.js-form-submit.form-submit[value="Remove"]\').length > 0)'
    );
  }

  /**
   * Check that alt text for image exists is displayed.
   *
   * NOTE: We specify a regex to allow escaped quotes in the alt text.
   *
   * @param string $text
   *   Alt text that should be checked.
   * @param string $region
   *   The region that contains image.
   *
   * @throws \Exception
   *
   * @Then /^I should see the image alt "(?P<text>(?:[^"]|\\")*)" in the "(?P<region>[^"]*)" region$/
   */
  public function assertAltRegion($text, $region) {
    $regionObj = $this->getRegion($region);
    $element = $regionObj->find('css', 'img');
    if (empty($element)) {
      throw new \Exception(sprintf('No alt text matching "%s" in the "%s" region on the page %s', $text, $region, $this->getSession()
        ->getCurrentUrl()));
    }
    $tmp = $element->getAttribute('alt');
    if ($text == $tmp) {
      $result = $text;
    }
    if (empty($result)) {
      throw new \Exception(sprintf('No alt text matching "%s" in the "%s" region on the page %s', $text, $region, $this->getSession()
        ->getCurrentUrl()));
    }
  }

  /**
   * Asserts that an image is present and not broken.
   *
   * @param string $region
   *   The region where image should appear.
   *
   * @throws \Exception
   *
   * @Then I should see an image in the :region region
   */
  public function assertValidImageRegion($region) {
    $regionObj = $this->getRegion($region);

    // In order to give browser chance to load image, wait for 10sec.
    $elements = $regionObj->waitFor(10, function () use ($regionObj) {
      return $regionObj->findAll('css', 'img');
    });

    if (empty($elements)) {
      throw new \Exception(sprintf('Image was not found in the "%s" region on the page %s', $region, $this->getSession()
        ->getCurrentUrl()));
    }

    $src = $elements[0]->getAttribute('src');
    if (empty($src)) {
      $src = $elements[0]->getAttribute('srcset');
    }

    if (!empty($src)) {
      $params = array('http' => array('method' => 'HEAD'));
      $context = stream_context_create($params);
      $file_uri = file_create_url(ltrim($src, '/'));
      $fp = @fopen($file_uri, 'rb', FALSE, $context);

      if (!$fp) {
        throw new \Exception(sprintf('Unable to download <img src="%s"> in the "%s" region on the page %s', $src, $region, $this->getSession()
          ->getCurrentUrl()));
      }

      $meta = stream_get_meta_data($fp);
      fclose($fp);
      if ($meta === FALSE) {
        throw new \Exception(sprintf('Error reading from <img src="%s"> in the "%s" region on the page %s', $src, $region, $this->getSession()
          ->getCurrentUrl()));
      }

      $wrapper_data = $meta['wrapper_data'];
      $found = FALSE;
      if (is_array($wrapper_data)) {
        foreach ($wrapper_data as $header) {
          if (substr(strtolower($header), 0, 19) == 'content-type: image') {
            $found = TRUE;
          }
        }
      }

      if (!$found) {
        throw new \Exception(sprintf('Not a valid image <img src="%s"> in the "%s" region on the page %s', $src, $region, $this->getSession()
          ->getCurrentUrl()));
      }
    }
    else {
      throw new \Exception(sprintf('No image had no src="..." attribute in the "%s" region on the page %s', $region, $this->getSession()
        ->getCurrentUrl()));
    }
  }

}
