<?php

namespace Drupal\Tests\thunder\FunctionalJavascript;

/**
 * Trait for manipulation of meta tag configuration and meta tags on page.
 *
 * @package Drupal\Tests\thunder\FunctionalJavascript
 */
trait ThunderMetaTagTrait {

  /**
   * Get field name for meta tag.
   *
   * @param string $metaTagName
   *   Meta tag name.
   * @param string $groupName
   *   Group name where meta tag belongs (fe. basic, advanced, open_graph, ..)
   * @param string $fieldNamePrefix
   *   Field name prefix (fe. field_meta_tags[0])
   *
   * @return string
   *   Full meta tag field name that can be used to set value for it.
   */
  protected function getMetaTagFieldName($metaTagName, $groupName = '', $fieldNamePrefix = '') {
    // Based on examples, this way of forming field name works properly.
    $fieldName = str_replace(['.', ':'], '_', $metaTagName);

    if (empty($groupName) && empty($fieldNamePrefix)) {
      return $fieldName;
    }

    return $fieldNamePrefix . '[' . $groupName . '][' . $fieldName . ']';
  }

  /**
   * Verify that meta tag values defined in configuration are properly set.
   *
   * @param array $metaTagConfiguration
   *   Meta tag configuration.
   */
  public function checkMetaTags(array $metaTagConfiguration) {
    // Check on article are custom meta tags properly populated.
    foreach ($metaTagConfiguration as $metaTagName => $value) {
      $metaTag = explode(' ', $metaTagName);

      if ($metaTag[1] == 'title') {
        $this->assertSession()->elementContains('xpath', '//head/title', $value);
      }
      else {
        $this->checkMetaTag($metaTag[1], $value);
      }
    }
  }

  /**
   * Check single meta tag on page.
   *
   * @param string $name
   *   Meta tag name.
   * @param string $value
   *   Meta tag value.
   */
  protected function checkMetaTag($name, $value) {
    $htmlValue = htmlentities($value);

    $checkXPath = "@content='{$htmlValue}'";
    if (strpos($value, 'LIKE:') === 0) {
      $valueToCheck = substr($htmlValue, strlen('LIKE:'));

      $checkXPath = "contains(@content, '{$valueToCheck}')";
    }

    $this->assertSession()
      ->elementExists('xpath', "//head/meta[(@name='{$name}' or @property='{$name}') and {$checkXPath}]");
  }

  /**
   * Generate meta tag configuration.
   *
   * @param array $configuration
   *   Meta tag configuration.
   *
   * @return array
   *   Generated meta tag configuration.
   */
  public function generateMetaTagConfiguration(array $configuration) {
    $metaTagConfigs = [];

    foreach ($configuration as $config) {
      $metaTagConfigs = array_merge($metaTagConfigs, $config);
    }

    foreach ($metaTagConfigs as $metaTagName => $metaTagValue) {
      if ($metaTagValue === '[random]') {
        $metaTagConfigs[$metaTagName] = $this->getRandomGenerator()->word(10);
      }
    }

    return $metaTagConfigs;
  }

  /**
   * Generate field name and field value mappings for meta tag configuration.
   *
   * @param array $configuration
   *   Meta tag configuration.
   * @param string $fieldNamePrefix
   *   Field name prefix (fe. field_meta_tags[0])
   *
   * @return array
   *   List with field names and values for it.
   */
  public function generateMetaTagFieldValues(array $configuration, $fieldNamePrefix = '') {
    $fieldValues = [];

    foreach ($configuration as $metaTagName => $metaTagValue) {
      $metaTag = explode(' ', $metaTagName);

      if (!empty($fieldNamePrefix)) {
        $fieldValues[$this->getMetaTagFieldName($metaTag[1], $metaTag[0], $fieldNamePrefix)] = $metaTagValue;
      }
      else {
        $fieldValues[$this->getMetaTagFieldName($metaTag[1])] = $metaTagValue;
      }
    }

    return $fieldValues;
  }

  /**
   * Replace tokens inside meta tag configuration.
   *
   * @param array $configuration
   *   Meta tag configuration.
   * @param array $tokens
   *   Tokens that should be replaced in configuration.
   *
   * @return array
   *   Returns meta tag configuration with replace tokens.
   */
  public function replaceTokens(array $configuration, array $tokens) {
    foreach ($configuration as $metaTagName => $metaTagValue) {
      foreach ($tokens as $tokenName => $tokenValue) {
        if (strpos($metaTagValue, $tokenName) !== FALSE) {
          $configuration[$metaTagName] = str_replace($tokenName, $tokenValue, $metaTagValue);
        }
      }
    }

    return $configuration;
  }

}
