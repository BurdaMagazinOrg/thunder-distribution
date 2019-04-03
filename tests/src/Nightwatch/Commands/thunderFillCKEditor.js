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

exports.command = function thunderFillCKEditor(selector, value) {
  var browser = this;

  browser
    .executeAsync(
      function (selector, value, done) {
        var elem = document.evaluate(selector, document).iterateNext();

        CKEDITOR.instances[jQuery(elem)[0].id].insertHtml(value);

        done();
      },
      [selector, value],
      function () {
      }
    );

  return browser;
};
