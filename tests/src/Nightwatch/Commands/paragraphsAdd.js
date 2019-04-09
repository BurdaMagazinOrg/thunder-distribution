/**
 * @file
 * Create different types of paragraphs with provided configuration.
 *
 * This provides a custom command, .paragraphsAdd()
 *
 * Different paragraph types require different config. Here are configs for
 * different paragraphs types:
 *
 * - Text:
 * {
 *    text: '<HTML markup that will be used to fill CKEditor>'
 * }
 *
 * - Image:
 * {
 *    selectIndex: <index of image in list of entity browser>
 * }
 *
 * - Instagram:
 * {
 *   url: '<Instagram URL>'
 * }
 *
 * @param {string} fieldName
 *   The paragraphs field name.
 * @param {string} type
 *   The paragraphs type.
 * @param {int} position
 *   The position where paragraph should be added.
 * @param {object} config
 *   The configuration for paragraph.
 *
 * @return {object}
 *   The 'browser' object.
 */

exports.command = function paragraphsAdd(fieldName, type, position, config) {
  var browser = this;

  var fieldNameId = fieldName.replace(/_/g, '-');
  var newParagraphPosition = position * 2;

  // Text Paragraph.
  if (type === 'text') {
    browser
      .paragraphsAddEmpty(fieldName, type, position)
      .waitForElementVisible('//table[contains(@id, "' + fieldNameId + '-values")]/tbody/tr[' + newParagraphPosition + ']//*[contains(@id, "subform-field-text-0-value")]//iframe', 10000)
      .thunderFillCKEditor('//table[contains(@id, "' + fieldNameId + '-values")]/tbody/tr[' + newParagraphPosition + ']//*[contains(@name, "[subform][field_text][0][value]")]', config.text);

    return browser;
  }

  // Image Paragraph.
  if (type === 'image') {
    browser
      .paragraphsAddEmpty(fieldName, type, position)
      .waitForElementVisible('//table[contains(@id, "' + fieldNameId + '-values")]/tbody/tr[' + newParagraphPosition + ']//*[contains(@id, "subform-field-image-entity-browser-entity-browser-open-modal")]', 10000)
      .click('//table[contains(@id, "' + fieldNameId + '-values")]/tbody/tr[' + newParagraphPosition + ']//*[contains(@id, "subform-field-image-entity-browser-entity-browser-open-modal")]')
      .waitForElementVisible('//*[@id="entity_browser_iframe_image_browser"]', 10000)
      .frame('entity_browser_iframe_image_browser')
      .waitForElementVisible('//*[@id="entity-browser-image-browser-form"]/div[1]/div[2]/div[' + config.selectIndex + ']/div[1]/span/img', 10000)
      .click('//*[@id="entity-browser-image-browser-form"]/div[1]/div[2]/div[' + config.selectIndex + ']')
      .click('//*[@id="edit-submit"]')
      .frame()
      .waitForElementVisible('//table[contains(@id, "' + fieldNameId + '-values")]/tbody/tr[' + newParagraphPosition + ']//img', 10000);

    return browser;
  }

  // Instagram Paragraph.
  if (type === 'instagram') {
    browser
      .paragraphsAddEmpty(fieldName, type, position)
      .waitForElementVisible('//table[contains(@id, "' + fieldNameId + '-values")]/tbody/tr[' + newParagraphPosition + ']//input[contains(@id, "subform-field-media-0-inline-entity-form-field-url-0-uri")]', 10000)
      .setValue('//table[contains(@id, "' + fieldNameId + '-values")]/tbody/tr[' + newParagraphPosition + ']//input[contains(@id, "subform-field-media-0-inline-entity-form-field-url-0-uri")]', config.url);

    return browser;
  }

  return browser;
};
