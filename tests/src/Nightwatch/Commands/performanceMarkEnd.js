/**
 * @file
 * Ends last performance measurement mark.
 *
 * This provides a custom command, .performanceMarkEnd()
 *
 * @return {object}
 *   The 'browser' object.
 */

exports.command = function performanceMarkEnd() {
  const browser = this;

  browser.performanceWaitBrowser().perform(() => {
    let span = browser.apmSpans.pop();

    if (!span) {
      return;
    }

    span.end();

    // Set spanId to current active span, if there is any.
    span = browser.apmSpans.pop();

    if (!span) {
      return;
    }

    browser.setCookie({
      domain: browser.apmDomain,
      expiry: 3533274000,
      httpOnly: false,
      name: "spanId",
      path: "/",
      value: span.id
    });
    browser.apmSpans.push(span);
  });

  return browser;
};
