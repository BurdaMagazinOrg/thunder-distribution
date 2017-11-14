(function (Drupal, $) {
  'use strict';

  Drupal.behaviors.length_indicator = {
    attach: function (context, settings) {
      $(context)
        .find('.length-indicator-enabled')
        .once('length-indicator')
        .each(function (index, element) {
          var $el = $(element);
          var total = $el.data('total');

          new Indicator($el, $el.closest('.form-wrapper'), total);
        }
      );
    }
  };

  function Indicator($el, $context, total) {
    this.$el = $el;

    this.total = total;

    this.allIndicators = $context.find('.indicator');
    this.cursor = $context.find('.cursor');

    var self = this;
    this.$el.on('input', function (e) {
      self.setCursorAndActiveIndicator();
    });
    this.setCursorAndActiveIndicator();
  }

  Indicator.prototype.setCursorAndActiveIndicator = function () {
    var length = this.$el.val().length;
    var position = (length / this.total) * 100;

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
