'use strict';

var merchant = merchant || {};
merchant.modules = merchant.modules || {};
(function ($) {
  merchant.modules.quickView = {
    init: function init() {
      var self = this;
      var $modals = $('.merchant-quick-view-modal');
      if (!$modals.length) {
        return;
      }
      $modals.each(function () {
        var $modal = $(this);
        var $closeButton = $modal.find('.merchant-quick-view-close-button');
        var $inner = $modal.find('.merchant-quick-view-inner');
        var $content = $modal.find('.merchant-quick-view-content');
        var $overlay = $modal.find('.merchant-quick-view-overlay');
        var isOpen = false;
        $(document).on('click', '.merchant-quick-view-button', function (e) {
          e.preventDefault();
          $content.empty();
          $modal.addClass('merchant-show');
          $modal.addClass('merchant-loading');
          isOpen = true;
          $.post(window.merchant.setting.ajax_url, {
            action: 'merchant_quick_view_content',
            nonce: window.merchant.setting.nonce,
            product_id: $(this).data('product-id')
          }, function (response) {
            if (response.success && isOpen) {
              $content.html(response.data);
              $inner.addClass('merchant-show');
              $modal.removeClass('merchant-loading');
              var $gallery = $content.find('.woocommerce-product-gallery');
              wc_single_product_params.zoom_enabled = window.merchant.setting.quick_view_zoom;
              if (typeof $.fn.wc_product_gallery === 'function' && $gallery.length) {
                $gallery.trigger('wc-product-gallery-before-init', [$gallery.get(0), wc_single_product_params]);

                // Force-enable the gallery slider in case the theme/plugin has removed it using `remove_theme_support( 'wc-product-gallery-slider' )`
                wc_single_product_params['flexslider_enabled'] = '1';
                $gallery.wc_product_gallery(wc_single_product_params);
                $gallery.trigger('wc-product-gallery-after-init', [$gallery.get(0), wc_single_product_params]);
              }
              var $variations = $content.find('.variations_form');
              if (typeof $.fn.wc_variation_form === 'function' && $variations.length) {
                $variations.each(function () {
                  $(this).wc_variation_form();
                });
              }
              if (window.botiga && window.botiga.productSwatch) {
                window.botiga.productSwatch.init();
              }
              if (window.botiga && window.botiga.qtyButton) {
                window.botiga.qtyButton.init('quick-view');
              }

              // Initialize AJAX add to cart if enabled.
              if (window.merchant.setting.ajax_add_to_cart) {
                self.initAjaxAddToCart($content, $modal);
              }
            } else {
              $content.html(response.data);
              $inner.addClass('merchant-show');
              $modal.removeClass('merchant-loading');
            }
            window.dispatchEvent(new Event('merchant.quickview.ajax.loaded'));
          }).fail(function (xhr, textStatus) {
            $content.html(textStatus);
            $inner.addClass('merchant-show');
            $modal.removeClass('merchant-loading');
          });
        });
        $overlay.on('click', function (e) {
          e.preventDefault();
          $closeButton.trigger('click');
        });
        $closeButton.on('click', function (e) {
          e.preventDefault();
          isOpen = false;
          $modal.removeClass('merchant-show');
          $inner.removeClass('merchant-show');
        });
      });
    },
    /**
     * Initialize AJAX add to cart handler.
     * 
     * @param {jQuery} $content The modal content container.
     * @param {jQuery} $modal The modal container.
     */
    initAjaxAddToCart: function initAjaxAddToCart($content, $modal) {
      var self = this;

      // Remove any existing handlers to prevent duplicates.
      $content.off('submit', 'form.cart');

      // Handle add to cart form submission.
      $content.on('submit', 'form.cart', function (e) {
        // Access the native submitter.
        var submitter = e.originalEvent ? e.originalEvent.submitter : null;
        var $submitter = $(submitter);
        var isAddToCart = false;

        // 1. If triggered by Enter key (no submitter), assume Add to Cart (standard behavior)
        // Note: You can add extra checks here for document.activeElement if you want to be extra safe for "Enter on Buy Now", 
        // but typically Enter in input fields implies the default action (Add to Cart).
        if (!submitter) {
          isAddToCart = true;
        }
        // 2. Check if submitter is explicitly the "Add to Cart" button
        // WooCommerce standard Add to Cart usually has name="add-to-cart" or class="single_add_to_cart_button"
        else if ($submitter.attr('name') === 'add-to-cart' || $submitter.hasClass('single_add_to_cart_button')) {
          isAddToCart = true;
        }

        // If it's NOT an Add to Cart action (e.g. it's a Buy Now button), allow default submission
        if (!isAddToCart) {
          return;
        }
        e.preventDefault();
        e.stopImmediatePropagation(); // Prevent other handlers from firing.

        var $form = $(this);
        var $button = $form.find('button[type="submit"], input[type="submit"]');
        var $addToCartButton = $form.find('.single_add_to_cart_button');

        // Use add to cart button if available, otherwise use first submit button.
        if (!$addToCartButton.length) {
          $addToCartButton = $button.first();
        }

        // Prevent duplicate submissions.
        if ($addToCartButton.hasClass('loading') || $addToCartButton.prop('disabled')) {
          return false;
        }

        // Get form data.
        var formData = $form.serializeArray();
        var data = {
          action: 'merchant_quick_view_add_to_cart',
          nonce: window.merchant.setting.nonce
        };

        // Check if this is a grouped product (has quantity fields with array notation like quantity[14]).
        var isGroupedProduct = false;
        $.each(formData, function (i, field) {
          if (field.name && field.name.indexOf('quantity[') === 0) {
            isGroupedProduct = true;
            return false; // Break the loop.
          }
        });

        // Convert form data to object, but handle quantity specially.
        $.each(formData, function (i, field) {
          // Skip standalone quantity field - we'll handle it separately for non-grouped products.
          if (field.name === 'quantity') {
            return;
          }
          // Skip add-to-cart field - we'll handle product ID separately.
          if (field.name === 'add-to-cart') {
            return;
          }
          // For grouped products, quantity[ID] fields are added as-is.
          // Variation attributes are sent as-is (attribute_pa_color, etc.).
          data[field.name] = field.value;
        });

        // Get product ID - try multiple sources (most reliable first).
        // 1. Extract from product wrapper div ID (format: product-{id}) - this is always present in the modal.
        var $productWrapper = $form.closest('[id^="product-"]');
        if ($productWrapper.length) {
          var productIdMatch = $productWrapper.attr('id').match(/product-(\d+)/);
          if (productIdMatch && productIdMatch[1]) {
            data.product_id = productIdMatch[1];
          }
        }

        // 2. Try to get it directly from the form input.
        if (!data.product_id) {
          var addToCartInput = $form.find('input[name="add-to-cart"]');
          if (addToCartInput.length && addToCartInput.val()) {
            data.product_id = addToCartInput.val();
          }
        }

        // Ensure product_id is set, otherwise we can't proceed.
        if (!data.product_id) {
          console.error('Quick View: Could not determine product ID');
          return false;
        }

        // Get variation ID if it's a variable product.
        if ($form.find('.variations_form').length) {
          var variationId = $form.find('input[name="variation_id"]').val();
          if (variationId) {
            data.variation_id = parseInt(variationId, 10);
          }
        }

        // Get quantity and ensure it's a number (not a string).
        // Only add standalone quantity for non-grouped products.
        // Grouped products use quantity[product_id] format which is already in the data object.
        if (!isGroupedProduct) {
          delete data.quantity; // Remove if it was included in serialized data.
          var quantityInput = $form.find('input[name="quantity"]');
          var quantity = quantityInput.length ? parseInt(quantityInput.val(), 10) : 1;
          if (isNaN(quantity) || quantity < 1) {
            quantity = 1;
          }
          data.quantity = quantity;
        } else {
          // For grouped products, ensure no standalone quantity field exists.
          delete data.quantity;
        }

        // Show loading state.
        var originalText = $addToCartButton.html();
        var originalDisabled = $addToCartButton.prop('disabled');
        $addToCartButton.addClass('loading').prop('disabled', true);
        if ($addToCartButton.is('button')) {
          $addToCartButton.data('original-text', originalText);
          $addToCartButton.html('<span class="spinner"></span>');
        }

        // Send AJAX request.
        $.ajax({
          type: 'POST',
          url: window.merchant.setting.ajax_url,
          data: data,
          success: function success(response) {
            self.handleAddToCartSuccess(response, $addToCartButton, originalText, originalDisabled, $content, $modal);
          },
          error: function error(xhr, textStatus, errorThrown) {
            self.handleAddToCartError(xhr, textStatus, errorThrown, $addToCartButton, originalText, originalDisabled, $content);
          }
        });
        return false;
      });
    },
    /**
     * Handle successful add to cart response.
     * 
     * @param {Object} response AJAX response.
     * @param {jQuery} $button The add to cart button.
     * @param {string} originalText Original button text.
     * @param {boolean} originalDisabled Original disabled state.
     * @param {jQuery} $content The modal content container.
     * @param {jQuery} $modal The modal container.
     */
    handleAddToCartSuccess: function handleAddToCartSuccess(response, $button, originalText, originalDisabled, $content, $modal) {
      // Remove loading state.
      $button.removeClass('loading').prop('disabled', originalDisabled);
      if ($button.is('button') && $button.data('original-text')) {
        $button.html($button.data('original-text'));
      }
      if (response.success && response.data) {
        // Update cart fragments.
        if (response.data.fragments) {
          $.each(response.data.fragments, function (selector, html) {
            $(selector).replaceWith(html);
          });
        }

        // Trigger WooCommerce events.
        $(document.body).trigger('added_to_cart', [response.data.fragments, response.data.cart_hash, $button, 'quick-view']);
        $(document.body).trigger('wc_fragment_refresh');

        // Show success message.
        if (response.data.message) {
          // Remove existing notices.
          $content.find('.merchant-quick-view-inner .woocommerce-error, .woocommerce-message, .woocommerce-info').remove();
          // trigger event for third party integration
          $(document).trigger('merchant_quick_view_add_to_cart_success', [response.data.message, $content]);
        }
      } else {
        // Handle unexpected response.
        this.handleAddToCartError(null, 'error', 'Unexpected response format', $button, originalText, originalDisabled, $content);
      }
    },
    /**
     * Handle add to cart error response.
     * 
     * @param {Object} xhr XMLHttpRequest object.
     * @param {string} textStatus Status text.
     * @param {string} errorThrown Error thrown.
     * @param {jQuery} $button The add to cart button.
     * @param {string} originalText Original button text.
     * @param {boolean} originalDisabled Original disabled state.
     * @param {jQuery} $content The modal content container.
     */
    handleAddToCartError: function handleAddToCartError(xhr, textStatus, errorThrown, $button, originalText, originalDisabled, $content) {
      // Remove loading state.
      $button.removeClass('loading').prop('disabled', originalDisabled);
      if ($button.is('button') && $button.data('original-text')) {
        $button.html($button.data('original-text'));
      }

      // Get error message.
      var errorMessage = '';
      if (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
        errorMessage = xhr.responseJSON.data.message;
      } else if (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.notices) {
        // Extract error from notices.
        var notices = xhr.responseJSON.data.notices;
        if (notices.error && notices.error.length > 0) {
          errorMessage = notices.error[0].notice;
        }
      } else {
        errorMessage = errorThrown || textStatus || 'An error occurred while adding the product to cart.';
      }

      // Remove existing notices.
      $content.find('.woocommerce-error, .woocommerce-message, .woocommerce-info').remove();

      // Add error message.
      var $error = $('<div class="woocommerce-error" role="alert"></div>').text(errorMessage);
      $content.find('form.cart').before($error);

      // Scroll to error if needed.
      $error[0].scrollIntoView({
        behavior: 'smooth',
        block: 'nearest'
      });
    }
  };
  $(document).ready(function () {
    merchant.modules.quickView.init();
  });
})(jQuery);