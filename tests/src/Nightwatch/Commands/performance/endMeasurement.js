/**
 * @file
 * Ends performance measurement for a test.
 *
 * This provides a custom command, .performance.endMeasurement()
 *
 * @return {object}
 *   The 'browser' object.
 */
exports.command = function endMeasurement() {
  const browser = this;

  browser.performance.waitBrowser();

  browser
    .perform(() => {
      let span = browser.apmSpans.pop();

      while (span) {
        span.end();

        span = browser.apmSpans.pop();
      }
    })
    .perform(() => {
      browser.apmTrans.end();
    });

  return browser;
};
