/**
 * @file
 * Start performance measurement for test.
 *
 * This provides a custom command, .performanceMeasurementStart()
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

exports.command = function performanceMeasurementStart(
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
        expiry: 3533274000,
        httpOnly: false,
        path: "/",
        name: "traceId",
        value: browser.apmTrans.traceId
      })
      .setCookie({
        domain,
        expiry: 3533274000,
        httpOnly: false,
        path: "/",
        name: "serverUrl",
        value: serverUrl
      })
      .setCookie({
        domain,
        expiry: 3533274000,
        httpOnly: false,
        path: "/",
        name: "branchTag",
        value: process.env.THUNDER_BRANCH
      })
      .performanceSetTag("branch", process.env.THUNDER_BRANCH);
  });

  return browser;
};
