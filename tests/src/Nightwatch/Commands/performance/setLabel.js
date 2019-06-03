/**
 * @file
 * Sets labels for performance test execution.
 *
 * This provides a custom command, .performance.setLabel()
 *
 * @param {string} name
 *   The label name.
 * @param {string} value
 *   The label value.
 *
 * @return {object}
 *   The 'browser' object.
 */

exports.command = function setLabel(name, value) {
  const browser = this;

  browser.perform(() => {
    browser.apmTrans.setLabel(name, value);
  });

  return browser;
};
