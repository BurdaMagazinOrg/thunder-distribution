/**
 * @file
 * Ends last performance measurement mark.
 *
 * This provides a custom command, .performance.endMark()
 *
 * @return {object}
 *   The 'browser' object.
 */
exports.command = function endMark() {
  const browser = this;

  browser.performance.waitBrowser().perform(() => {
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
      httpOnly: false,
      name: "spanId",
      path: "/",
      value: span.id
    });
    browser.apmSpans.push(span);
  });

  return browser;
};
