<?php

/**
 * @file
 * Contains actions for Behat Tests.
 */

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;

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
    $this->getSession()->wait(5000, '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))');
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
    $elements = $regionObj->findAll('css', 'img');
    if (empty($elements)) {
      throw new \Exception(sprintf('No image was not found in the "%s" region on the page %s', $region, $this->getSession()
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
