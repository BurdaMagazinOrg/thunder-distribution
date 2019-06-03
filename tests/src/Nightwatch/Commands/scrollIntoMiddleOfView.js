/**
 * @file
 * Scroll element into center of the screen, so that it's visible and clickable.
 *
 * NOTE: This function works only with XPATH!!!
 *
 * This provides a custom command, .scrollIntoMiddleOfView()
 *
 * @param {string} selector
 *   The XPATH selector for element.
 *
 * @return {object}
 *   The 'browser' object.
 */
exports.command = function scrollIntoMiddleOfView(selector) {
  const browser = this;

  browser.executeAsync(
    // eslint-disable-next-line prefer-arrow-callback
    function inBrowser(selectorInBrowser, done) {
      const elem = document.evaluate(selectorInBrowser, document).iterateNext();
      const viewPortHeight = Math.max(
        document.documentElement.clientHeight,
        window.innerHeight || 0
      );
      const element = jQuery(elem);
      const scrollTop = element.offset().top - viewPortHeight / 2;
      const scrollableParent = jQuery.isFunction(element.scrollParent)
        ? element.scrollParent()
        : [];
      if (
        scrollableParent.length > 0 &&
        scrollableParent[0] !== document &&
        scrollableParent[0] !== document.body
      ) {
        scrollableParent[0].scrollTop = scrollTop;
      } else {
        window.scroll(0, scrollTop);
      }

      done();
    },
    [selector],
    () => {}
  );

  return browser;
};
