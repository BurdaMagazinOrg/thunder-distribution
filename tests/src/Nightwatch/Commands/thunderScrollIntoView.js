/**
 * @file
 * Scroll element into center of the screen, so that it's visible and clickable.
 *
 * NOTE: This function works only with XPATH!!!
 *
 * This provides a custom command, .thunderScrollIntoView()
 *
 * @param {string} selector
 *   The XPATH selector for element.
 *
 * @return {object}
 *   The 'browser' object.
 */

exports.command = function thunderScrollIntoView(selector) {
  var browser = this;

  browser
    .executeAsync(
      function (selector, done) {

        var elem = document.evaluate(selector, document).iterateNext();
        var viewPortHeight = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
        var element = jQuery(elem);
        var scrollTop = element.offset().top - (viewPortHeight / 2);
        var scrollableParent = jQuery.isFunction(element.scrollParent) ? element.scrollParent() : [];
        if (scrollableParent.length > 0 && scrollableParent[0] !== document && scrollableParent[0] !== document.body) {
          scrollableParent[0].scrollTop = scrollTop
        }
        else {
          window.scroll(0, scrollTop);
        }

        done();
      },
      [selector],
      function () {
      }
    );

  return browser;
};
