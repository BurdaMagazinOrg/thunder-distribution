/**
 * @file
 * Create image paragraph.
 *
 * This provides a custom command, .paragraphs.addImage()
 *
 * Config can be following:
 * {
 *    selectIndex: <index of image in list of entity browser>,
 *    uploadImage: '<path to image to upload>'
 * }
 *
 * @param {string} fieldName
 *   The paragraphs field name.
 * @param {int} position
 *   The position where paragraph should be added.
 * @param {object} config
 *   The config for image paragraph.
 *
 * @return {object}
 *   The 'browser' object.
 */
exports.command = function addImage(fieldName, position, config) {
  const browser = this;

  const fieldNameId = fieldName.replace(/_/g, "-");
  const paragraphPosition = position * 2;

  browser.paragraphs.add(fieldName, "image", position);

  browser
    .waitForElementVisible(
      `//table[contains(@id, "${fieldNameId}-values")]/tbody/tr[${paragraphPosition}]//*[contains(@id, "subform-field-image-entity-browser-entity-browser-open-modal")]`,
      10000
    )
    .click(
      `//table[contains(@id, "${fieldNameId}-values")]/tbody/tr[${paragraphPosition}]//*[contains(@id, "subform-field-image-entity-browser-entity-browser-open-modal")]`
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
        // eslint-disable-next-line prefer-arrow-callback
        function inBrowser(done) {
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
        () => {}
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
      `//table[contains(@id, "${fieldNameId}-values")]/tbody/tr[${paragraphPosition}]//img`,
      10000
    );

  return browser;
};
