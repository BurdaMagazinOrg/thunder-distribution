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

exports.command = function performanceMeasurementStart(serverUrl, serviceName, transactionName, domain) {
  var browser = this;

  browser
    .perform(function () {
      var apmInstance = browser.apm.start({
        serverUrl: serverUrl,
        serviceName: serviceName
      });

      browser.apmDomain = domain;
      browser.apmTrans = apmInstance.startTransaction(transactionName, 'test');
      browser.apmSpans = [];

      browser.setCookie({
        domain: domain,
        expiry: 3533274000,
        httpOnly: false,
        name: 'traceId',
        path: '/',
        value: browser.apmTrans.traceId
      });
    });

  return browser;
};
