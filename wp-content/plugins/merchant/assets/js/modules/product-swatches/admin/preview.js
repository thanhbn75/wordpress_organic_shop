"use strict";

(function ($) {
  'use strict';

  window.merchantProductSwatchesAdminPreview = {
    /**
     * Initialize the preview label visibility functionality
     */
    init: function init() {
      this.bindEvents();
      this.initialize();
    },
    /**
     * Bind event listeners for the switcher
     */
    bindEvents: function bindEvents() {
      var self = this;
      $(document).on('change', 'input[name="merchant[hide_attribute_label_single]"]', function () {
        self.toggleLabels();
      });
    },
    /**
     * Initialize label visibility on page load
     */
    initialize: function initialize() {
      var self = this;
      // Initialize immediately
      self.toggleLabels();
      // Also run after a short delay to ensure preview is loaded
      setTimeout(function () {
        // self.toggleLabels();
      }, 100);
    },
    /**
     * Toggle label visibility based on switcher state
     */
    toggleLabels: function toggleLabels() {
      var $switcher = $('input[name="merchant[hide_attribute_label_single]"]');
      var $preview = $('.mrc-preview-single-product-elements');
      if (!$switcher.length || !$preview.length) {
        return;
      }
      var isChecked = $switcher.is(':checked');
      var $labels = $preview.find('.variations th.label');
      var $values = $preview.find('.variations td.value');
      if (isChecked) {
        $labels.hide();
        $values.attr('colspan', '2');
      } else {
        $labels.show().removeAttr('style'); // Remove inline style to ensure visibility
        $values.removeAttr('colspan');
      }
    }
  };
  $(document).ready(function () {
    merchantProductSwatchesAdminPreview.init(); // Initialize the preview label visibility functionality
  });
})(jQuery);