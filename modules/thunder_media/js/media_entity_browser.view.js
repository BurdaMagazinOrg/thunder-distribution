/**
 * @file
 * Defines the behavior of the media entity browser view.
 */

(function ($) {

  'use strict';

  /**
   * Attaches the behavior of the media entity browser view.
   */
  Drupal.behaviors.mediaEntityBrowserView = {
    attach: function (context, settings) {

      $('.views-row', context).each(function () {
        var $row = $(this);
        var $input = $row.find('.views-field-entity-browser-select input');

        // When Auto Select functionality is enabled, then select entity
        // on click, without marking it as selected.
        if (drupalSettings.entity_browser_widget.auto_select) {
          $row.once('register-row-click').click(function (event) {
            event.preventDefault();

            $row.parents('form')
              .find('.entities-list')
              .trigger('add-entities', [[$input.val()]]);
          });
        }
        else {
          $row[$input.prop('checked') ? 'addClass' : 'removeClass']('checked');

          $row.once('register-row-click').click(function () {
            $input.prop('checked', !$input.prop('checked'));
            $row[$input.prop('checked') ? 'addClass' : 'removeClass']('checked');
          });
        }
      });
    }
  };

}(jQuery, Drupal));
