(function (Drupal, $) {
  'use strict';

  Drupal.behaviors.length_indicator = {
    attach: function (context, settings) {
      $(context)
        .find('.length-indicator-enabled')
        .once('length-indicator')
        .each(function (index, element) {
          var $el = $(element);
          var optimin = $el.data('optimin');
          var optimax = $el.data('optimax');
          var tolerance = $el.data('tolerance');

          new Indicator($el, $el.closest('.form-wrapper'), optimin, optimax, tolerance);
        }
      );
    }
  };

  function Indicator($el, $context, optimin, optimax, tolerance) {
    this.$el = $el;

    this.settings = {
      min: optimin - tolerance,
      optimin: optimin,
      optimax: optimax,
      max: optimax + tolerance
    };

    this.allIndicators = $context.find('.indicator');
    this.cursor = $context.find('.cursor');

    this.scaleIndicators();

    var self = this;
    this.$el.on('input', function (e) {
      self.setCursorAndActiveIndicator();
    });
    this.setCursorAndActiveIndicator();
  }

  Indicator.prototype.scaleIndicators = function () {
    var total = this.settings.max + this.settings.min;

    var width = (this.settings.min / total) * 100;
    this.allIndicators.eq(0).css('width', width + '%').data('pos', 0);
    // Adding +1 to make max inclusive.
    this.allIndicators.eq(4).css('width', width + '%').data('pos', this.settings.max + 1);
    var last = width;

    width = (this.settings.optimin / total) * 100;
    this.allIndicators.eq(1).css('width', (width - last) + '%').data('pos', this.settings.min);
    last = width;

    width = (this.settings.optimax / total) * 100;
    this.allIndicators.eq(2).css('width', (width - last) + '%').data('pos', this.settings.optimin);
    last = width;

    width = (this.settings.max / total) * 100;
    // Adding +1 to make optimax inclusive.
    this.allIndicators.eq(3).css('width', (width - last) + '%').data('pos', this.settings.optimax + 1);
  };

  Indicator.prototype.setCursorAndActiveIndicator = function () {
    var length = this.$el.val().length;
    var max = this.settings.max + this.settings.min;
    var position = (length / max) * 100;

    position = position < 100 ? position : 100;
    this.cursor.css('left', position + '%');

    this.allIndicators.removeClass('active');

    var coloredIndicator = this.allIndicators.eq(0);
    for (var i = 1; i < this.allIndicators.length; i++) {
      var indicator = this.allIndicators.eq(i);
      if (length >= indicator.data('pos')) {
        coloredIndicator = indicator;
      }
      else {
        break;
      }
    }
    coloredIndicator.addClass('active');
  };

})(Drupal, jQuery);
