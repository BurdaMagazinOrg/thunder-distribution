/**
 * @file
 * Waits for browser performance metrics to be sent.
 *
 * This provides a custom command, .performance.waitBrowser()
 *
 * @param {int} maxWait
 *   The maximum time for waiting for browser to send data to APM Server.
 *   Default to 10000 ms.
 *
 * @return {object}
 *   The 'browser' object.
 */
exports.command = function waitBrowser(maxWait) {
  const browser = this;

  maxWait = maxWait || 10000;

  browser.timeoutsAsyncScript(maxWait).executeAsync(
    // eslint-disable-next-line prefer-arrow-callback
    function inBrowser(done) {
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
    () => {}
  );

  return browser;
};
