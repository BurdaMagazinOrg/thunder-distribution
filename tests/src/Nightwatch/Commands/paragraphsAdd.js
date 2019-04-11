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
 *    selectIndex: <index of image in list of entity browser>,
 *    uploadImage: '<path to image to upload>'
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

/* eslint-disable func-names */
exports.command = function paragraphsAdd(fieldName, type, position, config) {
  const browser = this;

  const fieldNameId = fieldName.replace(/_/g, "-");
  const newParagraphPosition = position * 2;

  // Text Paragraph.
  if (type === "text") {
    browser
      .paragraphsAddEmpty(fieldName, type, position)
      .waitForElementVisible(
        `//table[contains(@id, "${fieldNameId}-values")]/tbody/tr[${newParagraphPosition}]//*[contains(@id, "subform-field-text-0-value")]//iframe`,
        10000
      )
      .thunderFillCKEditor(
        `//table[contains(@id, "${fieldNameId}-values")]/tbody/tr[${newParagraphPosition}]//*[contains(@name, "[subform][field_text][0][value]")]`,
        config.text
      );

    return browser;
  }

  // Image Paragraph.
  if (type === "image") {
    browser
      .paragraphsAddEmpty(fieldName, type, position)
      .waitForElementVisible(
        `//table[contains(@id, "${fieldNameId}-values")]/tbody/tr[${newParagraphPosition}]//*[contains(@id, "subform-field-image-entity-browser-entity-browser-open-modal")]`,
        10000
      )
      .click(
        `//table[contains(@id, "${fieldNameId}-values")]/tbody/tr[${newParagraphPosition}]//*[contains(@id, "subform-field-image-entity-browser-entity-browser-open-modal")]`
      )
      .waitForElementVisible(
        '//*[@id="entity_browser_iframe_image_browser"]',
        10000
      )
      .frame("entity_browser_iframe_image_browser");

    // Make selection of provided image index.
    if (config.selectIndex) {
      browser
        .waitForElementVisible(
          `//*[@id="entity-browser-image-browser-form"]/div[1]/div[2]/div[${
            config.selectIndex
          }]/div[1]/span/img`,
          10000
        )
        .click(
          `//*[@id="entity-browser-image-browser-form"]/div[1]/div[2]/div[${
            config.selectIndex
          }]`
        );
    }

    // Upload image and use it.
    if (config.uploadImage) {
      browser
        .click('//a[text()="Import image"]')
        .waitForElementVisible('//*[@id="edit-upload"]/div/a', 10000)
        .executeAsync(
          function(done) {
            const elem = document
              .evaluate('//input[@type="file"]', document)
              .iterateNext();

            // Make upload field visible!!! This is workaround, so that we can
            // use browser.setValue() later, to upload file.
            jQuery(elem)
              .show(0)
              .css("visibility", "visible")
              .width(200)
              .height(30)
              .removeAttr("multiple");

            done();
          },
          [],
          function() {}
        )
        .setValue('//input[@type="file"]', config.uploadImage)
        .waitForElementVisible(
          '//*[contains(@id, "ajax-wrapper--")]/div/div/div[1]/div[1]/div/img',
          10000
        );
    }

    browser
      .click('//*[@id="edit-submit"]')
      .frame()
      .waitForElementVisible(
        `//table[contains(@id, "${fieldNameId}-values")]/tbody/tr[${newParagraphPosition}]//img`,
        10000
      );

    return browser;
  }

  // Instagram Paragraph.
  if (type === "instagram") {
    browser
      .paragraphsAddEmpty(fieldName, type, position)
      .waitForElementVisible(
        `//table[contains(@id, "${fieldNameId}-values")]/tbody/tr[${newParagraphPosition}]//input[contains(@id, "subform-field-media-0-inline-entity-form-field-url-0-uri")]`,
        10000
      )
      .setValue(
        `//table[contains(@id, "${fieldNameId}-values")]/tbody/tr[${newParagraphPosition}]//input[contains(@id, "subform-field-media-0-inline-entity-form-field-url-0-uri")]`,
        config.url
      );

    return browser;
  }

  return browser;
};
