/**
 * @file
 * Create text paragraph with provided HTML code.
 *
 * This provides a custom command, .paragraphs.addText()
 *
 * @param {string} fieldName
 *   The paragraphs field name.
 * @param {int} position
 *   The position where paragraph should be added.
 * @param {string} html
 *   The html for text paragraph.
 *
 * @return {object}
 *   The 'browser' object.
 */
exports.command = function addText(fieldName, position, html) {
  const browser = this;

  const fieldNameId = fieldName.replace(/_/g, "-");
  const paragraphPosition = position * 2;

  browser.paragraphs.add(fieldName, "text", position);

  browser
    .waitForElementVisible(
      `//table[contains(@id, "${fieldNameId}-values")]/tbody/tr[${paragraphPosition}]//*[contains(@id, "subform-field-text-0-value")]//iframe`,
      10000
    )
    .fillCKEditor(
      `//table[contains(@id, "${fieldNameId}-values")]/tbody/tr[${paragraphPosition}]//*[contains(@name, "[subform][field_text][0][value]")]`,
      html
    );

  return browser;
};
