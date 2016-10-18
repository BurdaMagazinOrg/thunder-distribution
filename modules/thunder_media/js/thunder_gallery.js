/**
 * @file
 * Media related javascripts.
 */

(function ($) {
  /**
   * Registers behaviours related to thunder media.
   */
  Drupal.behaviors.fixGallery = {
    attach: function (context) {
      $('.media-gallery img').height($('.media-gallery img').attr('height'));
    }
  };

}(jQuery, Drupal, drupalSettings));
