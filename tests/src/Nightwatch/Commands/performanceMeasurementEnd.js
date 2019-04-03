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
  var browser = this;

  browser
    .performanceWaitBrowser()
    .perform(function () {
      var span = browser.apmSpans.pop();

      while (span) {
        span.end();

        span = browser.apmSpans.pop();
      }
    })
    .perform(function () {
      browser.apmTrans.end();
    });

  return browser;
};
