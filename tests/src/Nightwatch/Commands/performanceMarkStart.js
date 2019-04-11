/**
 * @file
 * Sets performance measurement mark.
 *
 * This provides a custom command, .performanceMarkStart()
 *
 * @param {string} markName
 *   The mark name used for naming of time spans.
 *
 * @return {object}
 *   The 'browser' object.
 */

exports.command = function performanceMarkStart(markName) {
  const browser = this;

  browser.perform(() => {
    const span = browser.apmTrans.startSpan(markName);
    span.setTag("branch", process.env.THUNDER_BRANCH);

    browser.apmSpans.push(span);

    browser.setCookie({
      domain: browser.apmDomain,
      expiry: 3533274000,
      httpOnly: false,
      name: "spanId",
      path: "/",
      value: span.id
    });
  });

  return browser;
};
