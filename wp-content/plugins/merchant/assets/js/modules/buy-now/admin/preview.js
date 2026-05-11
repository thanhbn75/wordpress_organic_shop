"use strict";

(function ($) {
  'use strict';

  var buyNowPreview = {
    autoSelect: false,
    smallSize: {
      'padding_top_bottom': 5,
      'padding_left_right': 10
    },
    mediumSize: {
      'padding_top_bottom': 12,
      'padding_left_right': 24
    },
    largeSize: {
      'padding_top_bottom': 15,
      'padding_left_right': 30
    },
    /**
     * Initialize event listeners
     */
    init: function init() {
      this.updateButtonSize();
      $(document).on('change input', '.merchant-field-button-size select', this.updateButtonSize.bind(this));
      // Listen to both number inputs AND range sliders
      $(document).on('change input', '.merchant-field-padding_top_bottom input[type=number]', this.resetSizeField.bind(this));
      $(document).on('change input', '.merchant-field-padding_left_right input[type=number]', this.resetSizeField.bind(this));
      $(document).on('change input', '.merchant-field-padding_top_bottom input[type=range]', this.resetSizeField.bind(this));
      $(document).on('change input', '.merchant-field-padding_left_right input[type=range]', this.resetSizeField.bind(this));
    },
    /**
     * Rule: When selecting any value from select field, update number values based on selection
     */
    updateButtonSize: function updateButtonSize() {
      // Skip if this was triggered by programmatic select change (when setting to "custom")
      if (this.autoSelect) {
        return;
      }
      var size = $('.merchant-field-button-size select').val();

      // Rule: When selecting any value from select field, update number values based on selection
      if (size !== 'custom') {
        // Update padding values based on selected size (without triggering change event to prevent resetSizeField)
        $('.merchant-field-padding_top_bottom input[type=number]').val(this[size + 'Size']['padding_top_bottom']);
        $('.merchant-field-padding_left_right input[type=number]').val(this[size + 'Size']['padding_left_right']);
      }
    },
    /**
     * Rule: When updating number values manually, set select field to "custom"
     */
    resetSizeField: function resetSizeField() {
      // Rule: When updating number values manually, set select field to "custom"
      var field = $('.merchant-field-button-size select'),
        currentValue = field.val();
      if (currentValue !== 'custom') {
        // Set flag to prevent updateButtonSize from running when we set to "custom"
        this.autoSelect = true;
        field.val('custom').trigger('change');
        this.autoSelect = false;
      }
    }
  };
  $(document).ready(function () {
    buyNowPreview.init();
  });
})(jQuery);