/**
 * @file thunder_search_api.js
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Visually mark outdaed rows and disable vbo checkbox
   *
   * @type {Object}
   */
  Drupal.behaviors.thunderSearchApiFlagOutdatedContent = {
    attach: function (context, settings) {
      var outdated = settings.thunderSearchApi || [];

      if (outdated.length) {
        $('[data-thunder-search-api-id]', context)
          .filter(function () {
            return outdated.indexOf($(this).data('thunder-search-api-id')) !== -1;
          })
          .closest('tr').css('background-color', '#fff4f4')
          .find('.views-field-views-bulk-operations-bulk-form input[type="checkbox"]')
          .prop('disabled', true);
      }
    }
  };


}(jQuery, Drupal));
