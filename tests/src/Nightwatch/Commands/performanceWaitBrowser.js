/**
 * @file
 * Waits for browser performance metrics to be sent.
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

/* eslint-disable func-names */
exports.command = function performanceWaitBrowser(maxWait) {
  const browser = this;

  maxWait = maxWait || 10000;

  browser.timeoutsAsyncScript(maxWait).executeAsync(
    function(done) {
      const checkBrowserTransaction = () => {
        if (typeof elasticApm === "undefined") {
          setTimeout(checkBrowserTransaction, 100);

          return;
        }

        const transaction = window.elasticApm.getCurrentTransaction();
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
    function() {}
  );

  return browser;
};
