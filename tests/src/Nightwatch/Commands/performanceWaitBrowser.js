/**
 * @file
 * Waits for Browser RUM performance metrics to be sent.
 *
 * This provides a custom command, .performanceWaitBrowser()
 *
 * @param {int} maxWait
 *   The maximum time for waiting for browser to send data to APM Server.
 *   Default to 10000 ms.
 *
 * @return {object}
 *   The 'browser' object.
 */

exports.command = function performanceWaitBrowser(maxWait) {
  var browser = this;

  maxWait = maxWait || 10000;

  browser
    .timeoutsAsyncScript(maxWait)
    .executeAsync(function (done) {
        var checkBrowserTransaction = function () {
          if (typeof elasticApm === "undefined") {
            setTimeout(checkBrowserTransaction, 100);

            return;
          }

          var transaction = elasticApm.getCurrentTransaction();
          if (!transaction) {
            setTimeout(checkBrowserTransaction, 100);

            return;
          }

          if (transaction.type === "page-load" && !transaction.ended) {
            setTimeout(checkBrowserTransaction, 100);

            return;
          }

          // TODO: Ensure that not page-load transactions are also sent before navigating to new page!
          setTimeout(done, 0);
        };

        setTimeout(checkBrowserTransaction, 200);
      },
      [],
      function () {
      }
    );

  return browser;
};
