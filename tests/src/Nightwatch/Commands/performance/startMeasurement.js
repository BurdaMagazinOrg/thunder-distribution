/**
 * @file
 * Start performance measurement for test.
 *
 * This provides a custom command, .performance.startMeasurement()
 *
 * @param {string} serverUrl
 *   The Elastic APM server URL.
 * @param {string} serviceName
 *   The service name used to display time spans inside Kibana APM.
 * @param {string} transactionName
 *   The transaction name used for tagging logged data.
 * @param {string} domain
 *   The testing host domain name.
 *
 * @return {object}
 *   The 'browser' object.
 */

exports.command = function startMeasurement(
  serverUrl,
  serviceName,
  transactionName,
  domain
) {
  const browser = this;

  browser.perform(() => {
    const apmInstance = browser.apm.start({
      serverUrl,
      serviceName
    });

    browser.apmDomain = domain;
    browser.apmTrans = apmInstance.startTransaction(transactionName, "test");
    browser.apmSpans = [];

    browser
      .setCookie({
        domain,
        httpOnly: false,
        path: "/",
        name: "traceId",
        value: browser.apmTrans.traceId
      })
      .setCookie({
        domain,
        httpOnly: false,
        path: "/",
        name: "serverUrl",
        value: serverUrl
      })
      .setCookie({
        domain,
        httpOnly: false,
        path: "/",
        name: "branchTag",
        value: process.env.THUNDER_BRANCH
      })
      .performance.setLabel("branch", process.env.THUNDER_BRANCH);
  });

  return browser;
};
