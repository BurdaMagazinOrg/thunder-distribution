/**
 * @file
 * Defines the behavior of the media entity browser view.
 */
(function ($, Drupal) {

  'use strict';

  Drupal.theme.thunderParagraphsFieldWidgetSettingsWarning = function (options) {
    var message = Drupal.t('The !option option is not supported for the Thunder distribution because of potential data loss in combination with the inline_entity_form module. If you want to use it, make sure to remove all inline entity forms from your paragraph types.', {'!option': options.option});
    return '<div class="messages messages--warning js-form-wrapper form-wrapper">' + message + '</div>';
  };

  /**
   * Display warning message for certain paragraphs field widget settings.
   */
  Drupal.behaviors.thunderParagraphsFieldWidgetSettings = {
    attach: function (context) {

      var $form = $('[data-drupal-selector="edit-fields-field-paragraphs-settings-edit-form"]', context).once('paragraphsFieldWidgetSettings');
      if ($form.length) {
        // Autocollapse
        $form.find('[data-drupal-selector="edit-fields-field-paragraphs-settings-edit-form-settings-autocollapse"]')
          .parents('.form-item__field-wrapper').first().after(Drupal.theme('thunderParagraphsFieldWidgetSettingsWarning', {option: Drupal.t('Autocollapse')}));
        // Collapse / Edit all
        $form.find('[data-drupal-selector="edit-fields-field-paragraphs-settings-edit-form-settings-features-collapse-edit-all"]')
          .parents('.form-item__field-wrapper').first().after(Drupal.theme('thunderParagraphsFieldWidgetSettingsWarning', {option: Drupal.t('Collapse / Edit all')}));
      }
    }
  };

}(jQuery, Drupal));
