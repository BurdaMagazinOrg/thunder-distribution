/**
 * @file
 * Elastic APM RUM init.
 */

(function () {

  'use strict';

  /**
   * Get Cookie by name.
   *
   * @param {string} name
   *   The cookie name.
   *
   * @return {string|null}
   *   Returns cookie value or null.
   */
  var getCookie = function (name) {
    var v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
    return v ? v[2] : null;
  };

  var traceId = getCookie('traceId');

  // Init Elastic RUM.
  window.apm = elasticApm.init({
    serviceName: 'ThunderRUM',
    serverUrl: 'http://localhost:8200',
    pageLoadTransactionName: window.location.pathname,
    pageLoadTraceId: traceId,
    flushInterval: 1
  });

  elasticApm.addFilter(function (payload) {
    if (!payload.transactions) {
      return payload;
    }

    var spanId = getCookie('spanId');
    if (!spanId) {
      return payload;
    }

    payload.transactions[0].parent_id = spanId;

    return payload
  });

}());
