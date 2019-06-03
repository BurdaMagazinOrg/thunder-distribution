/**
 * @file
 * Sets tag for element. It can be existing tag or new one.
 *
 * This provides a custom command, .fillCKEditor()
 *
 * @param {string} selector
 *   The element selector.
 * @param {string} value
 *   The HTML value that will be filled to CKEditor.
 *
 * @return {object}
 *   The 'browser' object.
 */
exports.command = function fillCKEditor(selector, value) {
  const browser = this;

  browser.executeAsync(
    // eslint-disable-next-line prefer-arrow-callback
    function inBrowser(selectorInBrowser, valueInBrowser, done) {
      const elem = document.evaluate(selectorInBrowser, document).iterateNext();

      CKEDITOR.instances[jQuery(elem)[0].id].insertHtml(valueInBrowser);

      done();
    },
    [selector, value],
    () => {}
  );

  return browser;
};
