/**
 * @file
 * Create Instagram paragraph with provided URL.
 *
 * This provides a custom command, .paragraphs.addInstagram()
 *
 * @param {string} fieldName
 *   The paragraphs field name.
 * @param {int} position
 *   The position where paragraph should be added.
 * @param {string} url
 *   The URL for Instagram paragraph.
 *
 * @return {object}
 *   The 'browser' object.
 */
exports.command = function addInstagram(fieldName, position, url) {
  const browser = this;

  const fieldNameId = fieldName.replace(/_/g, "-");
  const paragraphPosition = position * 2;

  browser.paragraphs.add(fieldName, "instagram", position);

  browser
    .waitForElementVisible(
      `//table[contains(@id, "${fieldNameId}-values")]/tbody/tr[${paragraphPosition}]//input[contains(@id, "subform-field-media-0-inline-entity-form-field-url-0-uri")]`,
      10000
    )
    .setValue(
      `//table[contains(@id, "${fieldNameId}-values")]/tbody/tr[${paragraphPosition}]//input[contains(@id, "subform-field-media-0-inline-entity-form-field-url-0-uri")]`,
      url
    );

  return browser;
};
