/**
 * @file
 * Ends performance measurement for a test.
 *
 * This provides a custom command, .performanceMeasurementEnd()
 *
 * @return {object}
 *   The 'browser' object.
 */

exports.command = function performanceMeasurementEnd() {
  const browser = this;

  browser
    .performanceWaitBrowser()
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
