/**
 * @file
 * Defines the behavior of the media entity browser view.
 */
(function ($, Drupal) {

  'use strict';

  Drupal.theme.thunderParagraphsFieldWidgetSettingsWarning = function (options) {
    return '<div class="messages messages--warning js-form-wrapper form-wrapper">' + options.message + '</div>';
  };

  /**
   * Hard-wire and disable certain paragraphs field widget settings and display
   * a warning message.
   */
  Drupal.behaviors.thunderParagraphsFieldWidgetSettings = {
    attach: function (context) {
      // Disable form elements the 'Drupal' way and add a message
      function setDisabled(message) {
        return function () {
          $(this).prop('disabled', 'disabled').parents('.form-item__field-wrapper').first().after(message)
            .parents('.form-item').first().addClass('form-disabled');
        };
      }

      var $form = $('[data-drupal-selector="edit-fields-field-paragraphs-settings-edit-form"]', context).once('paragraphsFieldWidgetSettings');
      if ($form.length) {
        var message = Drupal.theme(
          'thunderParagraphsFieldWidgetSettingsWarning',
          {message: Drupal.t('This option is disabled for the Thunder distribution because of potential data loss in combination with the inline_entity_form module.')}
        );

        // Autocollapse
        $form.find('[data-drupal-selector="edit-fields-field-paragraphs-settings-edit-form-settings-autocollapse"]')
          .val('none').each(setDisabled(message));
        // Collapse / Edit all
        $form.find('[data-drupal-selector="edit-fields-field-paragraphs-settings-edit-form-settings-features-collapse-edit-all"]')
          .prop('checked', false).each(setDisabled(message));
      }
    }
  };

}(jQuery, Drupal));
