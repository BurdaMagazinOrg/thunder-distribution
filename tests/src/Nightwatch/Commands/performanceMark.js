/**
 * @file
 * Stop last measurement mark and start new performance measurement mark.
 *
 * This provides a custom command, .performanceMark()
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
