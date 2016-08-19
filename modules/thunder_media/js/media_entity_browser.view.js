/**
 * @file
 * Defines the behavior of the media entity browser view.
 */

(function ($) {

  "use strict";

  /**
   * Attaches the behavior of the media entity browser view.
   */
  Drupal.behaviors.mediaEntityBrowserView = {
    attach: function (context, settings) {
      $('.views-row', context).once().click(function () {
        var $row = $(this);
        var $input = $row.find('.views-field-entity-browser-select input');
        $input.prop('checked', !$input.prop('checked'));
        $row[$input.prop('checked') ? 'addClass' : 'removeClass']('checked');
      });
    }
  };

}(jQuery, Drupal));
