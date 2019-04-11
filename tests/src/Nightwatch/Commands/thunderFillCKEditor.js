/**
 * @file
 * Sets tag for element. It can be existing tag or new one.
 *
 * This provides a custom command, .thunderFillCKEditor()
 *
 * @param {string} selector
 *   The element selector.
 * @param {string} value
 *   The HTML value that will be filled to CKEditor.
 *
 * @return {object}
 *   The 'browser' object.
 */

/* eslint-disable func-names */
exports.command = function thunderFillCKEditor(selector, value) {
  const browser = this;

  browser.executeAsync(
    function(selectorInBrowser, valueInBrowser, done) {
      const elem = document.evaluate(selectorInBrowser, document).iterateNext();

      CKEDITOR.instances[jQuery(elem)[0].id].insertHtml(valueInBrowser);

      done();
    },
    [selector, value],
    function() {}
  );

  return browser;
};
