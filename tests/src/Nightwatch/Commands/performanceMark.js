/**
 * @file
 * Start performance measurement for test.
 *
 * This provides a custom command, .perfPageLoad()
 *
 * @param {string} markName
 *   The mark name used for naming of time spans.
 *
 * @return {object}
 *   The 'browser' object.
 */

exports.command = function performanceMark(markName) {
  var browser = this;

  browser
    .performanceMarkEnd()
    .performanceMarkStart(markName);

  return browser;
};
