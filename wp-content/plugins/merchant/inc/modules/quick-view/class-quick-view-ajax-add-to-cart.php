<?php

/**
 * Quick View AJAX Add to Cart Handler.
 * 
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Quick View AJAX Add to Cart Class.
 * 
 * Handles AJAX add to cart functionality for the Quick View module.
 */
class Merchant_Quick_View_Ajax_Add_To_Cart {

	/**
	 * Module ID.
	 */
	const MODULE_ID = 'quick-view';

	/**
	 * The single class instance.
	 *
	 * @var Merchant_Quick_View_Ajax_Add_To_Cart|null
	 */
	private static $instance = null;

	/**
	 * Constructor.
	 */
	private function __construct() {
		// Constructor is kept minimal - initialization happens via init() method.
	}

	/**
	 * Get the single instance of the class.
	 *
	 * @return Merchant_Quick_View_Ajax_Add_To_Cart
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize the class.
	 * Should be called explicitly after getting the instance.
	 * 
	 * @return void
	 */
	public function init() {
		// Only register AJAX handlers if module is active and AJAX add to cart is enabled.
		if ( ! $this->is_enabled() ) {
			return;
		}

		$this->register_hooks();
	}

	/**
	 * Register WordPress hooks.
	 * 
	 * @return void
	 */
	private function register_hooks() {
		// Register AJAX handlers.
		add_action( 'wp_ajax_merchant_quick_view_add_to_cart', array( $this, 'ajax_add_to_cart_handler' ) );
		add_action( 'wp_ajax_nopriv_merchant_quick_view_add_to_cart', array( $this, 'ajax_add_to_cart_handler' ) );
	}

	/**
	 * Check if AJAX add to cart is enabled.
	 * 
	 * @return bool True if module is active and AJAX add to cart is enabled, false otherwise.
	 */
	private function is_enabled() {
		// Check if Quick View module is active.
		if ( ! Merchant_Modules::is_module_active( self::MODULE_ID ) ) {
			return false;
		}

		// Check if WooCommerce is active.
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return false;
		}

		// Check if AJAX add to cart is enabled in settings.
		$settings = $this->get_module_settings();
		if ( empty( $settings['ajax_add_to_cart'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get module settings.
	 * 
	 * @return array Module settings.
	 */
	private function get_module_settings() {
		$settings = get_option( 'merchant', array() );
		$defaults = array(
			'ajax_add_to_cart' => false,
		);

		if ( empty( $settings[ self::MODULE_ID ] ) ) {
			$settings[ self::MODULE_ID ] = $defaults;
		}

		$settings = wp_parse_args( $settings[ self::MODULE_ID ], $defaults );

		/**
		 * Hook: merchant_module_settings
		 *
		 * @param array  $settings  Module settings.
		 * @param string $module_id Module ID.
		 *
		 * @since 1.9.16
		 */
		$settings = apply_filters( 'merchant_module_settings', $settings, self::MODULE_ID );

		return $settings;
	}

	/**
	 * AJAX add to cart handler.
	 * 
	 * @return void
	 */
	public function ajax_add_to_cart_handler() {
		check_ajax_referer( 'merchant-nonce', 'nonce' );

		// Validate request settings.
		$validation_error = $this->validate_ajax_add_to_cart_settings();
		if ( $validation_error ) {
			wp_send_json_error( array( 'message' => $validation_error ) );
		}

		// Extract and sanitize all request data.
		$request_data = $this->extract_add_to_cart_request_data();

		// Validate extracted data.
		$validation_error = $this->validate_add_to_cart_request_data( $request_data );
		if ( $validation_error ) {
			wp_send_json_error( array( 'message' => $validation_error ) );
		}

		// Get product object.
		$product = $this->get_product_from_id( $request_data['product_id'] );
		$validation_error = $this->validate_product( $product );
		if ( $validation_error ) {
			wp_send_json_error( array( 'message' => $validation_error ) );
		}

		// Validate product-specific requirements and prepare data (e.g., find variation_id). (variable and grouped products)
		$validation_error = $this->validate_and_prepare_product_add_to_cart( $product, $request_data );
		if ( $validation_error ) {
			wp_send_json_error( array( 'message' => $validation_error ) );
		}

		// Add product to cart based on type.
		$added = $this->add_product_to_cart( $product, $request_data );

		// Check if add to cart was successful.
		if ( ! $added ) {
			// For grouped products, provide specific error message.
			if ( $product->is_type( 'grouped' ) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Please choose the quantity of items you wish to add to your cart.', 'merchant' ) ) );
			}

			$notices = wc_get_notices( 'error' );
			$error_message = ! empty( $notices ) ? wp_strip_all_tags( $notices[0]['notice'] ) : esc_html__( 'Product could not be added to cart.', 'merchant' );
			wp_send_json_error( array( 'message' => $error_message, 'notices' => $notices ) );
		}

		// Send success response with cart fragments.
		$this->send_ajax_add_to_cart_success_response( $request_data['product_id'], $request_data['quantity'] );
	}

	/**
	 * Validate AJAX add to cart settings and environment.
	 * 
	 * @return string|false Error message if validation fails, false otherwise.
	 */
	private function validate_ajax_add_to_cart_settings() {
		// Check if Quick View module is active.
		if ( ! Merchant_Modules::is_module_active( self::MODULE_ID ) ) {
			return esc_html__( 'Quick View module is not active.', 'merchant' );
		}

		// Check if AJAX add to cart is enabled.
		$settings = $this->get_module_settings();
		if ( empty( $settings['ajax_add_to_cart'] ) ) {
			return esc_html__( 'AJAX add to cart is disabled.', 'merchant' );
		}

		// Check if WooCommerce is active.
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return esc_html__( 'WooCommerce is not available.', 'merchant' );
		}

		return false;
	}

	/**
	 * Extract and sanitize all add to cart request data from $_POST.
	 * Only extracts and sanitizes, does not validate.
	 * 
	 * We are ignoring nonce verification because we are only extracting data from $_POST.
	 * The nonce is verified in the ajax_add_to_cart_handler method.
	 * 
	 * @return array Sanitized request data with keys: product_id, quantity, variation_id, variation_attributes, grouped_items.
	 */
	private function extract_add_to_cart_request_data() {
		$data = array();

		// Get product ID (WooCommerce uses 'add-to-cart' as the field name).
		$data['product_id'] = 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['product_id'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$data['product_id'] = absint( $_POST['product_id'] );
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
		} elseif ( isset( $_POST['add-to-cart'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$data['product_id'] = absint( $_POST['add-to-cart'] );
		}

		// Get quantity.
		$data['quantity'] = 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['quantity'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$data['quantity'] = absint( $_POST['quantity'] );
		}

		// Get variation ID (for variable products).
		$data['variation_id'] = 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['variation_id'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$data['variation_id'] = absint( $_POST['variation_id'] );
		}

		// Get variation attributes (for variable products).
		$data['variation_attributes'] = array();
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		foreach ( $_POST as $key => $value ) {
			if ( strpos( $key, 'attribute_' ) === 0 ) {
				$data['variation_attributes'][ sanitize_text_field( $key ) ] = sanitize_text_field( wp_unslash( $value ) );
			}
		}

		// Get grouped items (for grouped products).
		$data['grouped_items'] = array();
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['quantity'] ) && is_array( $_POST['quantity'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$grouped_items = wp_unslash( $_POST['quantity'] );
			foreach ( $grouped_items as $item_id => $item_quantity ) {
				$data['grouped_items'][ absint( $item_id ) ] = absint( $item_quantity ); // This plays the sanitization role.
			}
		}

		return $data;
	}

	/**
	 * Validate extracted request data.
	 * 
	 * @param array $request_data Request data to validate.
	 * @return string|false Error message if validation fails, false otherwise.
	 */
	private function validate_add_to_cart_request_data( $request_data ) {
		if ( empty( $request_data['product_id'] ) ) {
			return esc_html__( 'Product ID is required.', 'merchant' );
		}

		if ( empty( $request_data['grouped_items'] ) && ( empty( $request_data['quantity'] ) || $request_data['quantity'] < 1 ) ) {
			return esc_html__( 'Quantity is required.', 'merchant' );
		}

		return false;
	}

	/**
	 * Get product from ID.
	 * 
	 * @param int $product_id Product ID.
	 * @return WC_Product|false Product object or false on failure.
	 */
	private function get_product_from_id( $product_id ) {
		return wc_get_product( $product_id );
	}

	/**
	 * Validate product object.
	 * 
	 * @param WC_Product|false|WP_Error $product Product object.
	 * @return string|false Error message if validation fails, false otherwise.
	 */
	private function validate_product( $product ) {
		if ( ! $product || is_wp_error( $product ) ) {
			return esc_html__( 'Invalid product.', 'merchant' );
		}

		return false;
	}

	/**
	 * Validate product-specific add to cart requirements and prepare data.
	 * This method may modify $request_data (e.g., to add found variation_id).
	 * 
	 * @param WC_Product $product Product object.
	 * @param array      $request_data Request data (passed by reference, may be modified).
	 * @return string|false Error message if validation fails, false otherwise.
	 */
	private function validate_and_prepare_product_add_to_cart( $product, &$request_data ) {
		if ( $product->is_type( 'variable' ) ) {
			return $this->validate_and_prepare_variable_product( $product, $request_data );
		} elseif ( $product->is_type( 'grouped' ) ) {
			return $this->validate_grouped_product_requirements( $request_data );
		}

		return false;
	}

	/**
	 * Add product to cart based on product type.
	 * 
	 * @param WC_Product $product Product object.
	 * @param array      $request_data Request data containing product_id, quantity, variation_id, variation_attributes, grouped_items.
	 * @return bool|string Cart item key on success, false on failure.
	 */
	private function add_product_to_cart( $product, $request_data ) {
		if ( $product->is_type( 'variable' ) ) {
			return $this->add_variable_product_to_cart( $product, $request_data );
		} elseif ( $product->is_type( 'grouped' ) ) {
			return $this->add_grouped_product_to_cart( $request_data );
		} else {
			return $this->add_simple_product_to_cart( $request_data['product_id'], $request_data['quantity'] );
		}
	}

	/**
	 * Validate variable product requirements and prepare data.
	 * Finds variation_id if not provided and stores it in $request_data.
	 * 
	 * @param WC_Product $product Product object.
	 * @param array      $request_data Request data (passed by reference, may be modified).
	 * @return string|false Error message if validation fails, false otherwise.
	 */
	private function validate_and_prepare_variable_product( $product, &$request_data ) {
		$variation_id = $request_data['variation_id'];
		$variation_attributes = $request_data['variation_attributes'];

		// If variation_id is not provided, try to find it from attributes.
		if ( ! $variation_id && ! empty( $variation_attributes ) ) {
			$data_store = WC_Data_Store::load( 'product' );
			$variation_id = $data_store->find_matching_product_variation( $product, $variation_attributes );
			// Store found variation_id in request_data for use in add method.
			$request_data['variation_id'] = $variation_id;
		}

		if ( ! $variation_id ) {
			return esc_html__( 'Please select product options.', 'merchant' );
		}

		$variation = wc_get_product( $variation_id );
		if ( ! $variation || ! $variation->is_purchasable() ) {
			return esc_html__( 'This variation is not available.', 'merchant' );
		}

		// Validate using WooCommerce's validation filter.
		$product_id = $request_data['product_id'];
		$quantity = $request_data['quantity'];
		// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment -- This is a woocommerce hook, no need to add a comment.
		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variation_attributes );
		if ( ! $passed_validation ) {
			$notices = wc_get_notices( 'error' );
			return ! empty( $notices ) ? wp_strip_all_tags( $notices[0]['notice'] ) : esc_html__( 'Validation failed.', 'merchant' );
		}

		return false;
	}

	/**
	 * Validate grouped product requirements.
	 * 
	 * @param array $request_data Request data.
	 * @return string|false Error message if validation fails, false otherwise.
	 */
	private function validate_grouped_product_requirements( $request_data ) {
		$items = $request_data['grouped_items'];
		
		if ( empty( $items ) || ! is_array( $items ) ) {
			return esc_html__( 'Please choose the quantity of items you wish to add to your cart.', 'merchant' );
		}

		return false;
	}

	/**
	 * Add variable product to cart.
	 * Assumes validation has already passed and variation_id is set in request_data.
	 * 
	 * @param WC_Product $product Product object.
	 * @param array      $request_data Request data containing product_id, quantity, variation_id, variation_attributes.
	 * @return bool|string Cart item key on success, false on failure.
	 */
	private function add_variable_product_to_cart( $product, $request_data ) {
		$product_id = $request_data['product_id'];
		$quantity = $request_data['quantity'];
		$variation_id = $request_data['variation_id'];
		$variation_attributes = $request_data['variation_attributes'];

		return WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation_attributes );
	}

	/**
	 * Add grouped product to cart.
	 * Assumes validation has already passed.
	 * 
	 * @param array $request_data Request data containing grouped_items.
	 * @return bool True if at least one item was added, false otherwise.
	 */
	private function add_grouped_product_to_cart( $request_data ) {
		$items = $request_data['grouped_items'];
		$was_added_to_cart = false;

		foreach ( $items as $item_id => $item_quantity ) {
			$item_quantity = wc_stock_amount( $item_quantity );
			if ( $item_quantity <= 0 ) {
				continue;
			}

			// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment -- This is a woocommerce hook, no need to add a comment.
			$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $item_id, $item_quantity );
			if ( $passed_validation && false !== WC()->cart->add_to_cart( $item_id, $item_quantity ) ) {
				$was_added_to_cart = true;
			}
		}

		return $was_added_to_cart;
	}

	/**
	 * Add simple product to cart.
	 * Assumes validation has already passed.
	 * 
	 * @param int $product_id Product ID.
	 * @param int $quantity Quantity.
	 * @return bool|string Cart item key on success, false on failure.
	 */
	private function add_simple_product_to_cart( $product_id, $quantity ) {
		// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment -- This is a woocommerce hook, no need to add a comment.
		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
		if ( ! $passed_validation ) {
			return false;
		}

		return WC()->cart->add_to_cart( $product_id, $quantity );
	}

	/**
	 * Send AJAX add to cart success response with cart fragments.
	 * 
	 * @param int $product_id Product ID.
	 * @param int $quantity Quantity.
	 * @return void
	 */
	private function send_ajax_add_to_cart_success_response( $product_id, $quantity ) {
		// Calculate cart totals.
		WC()->cart->calculate_totals();
		
		// Get mini cart fragments.
		ob_start();
		woocommerce_mini_cart();
		$mini_cart = ob_get_clean();

		// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment -- This is a woocommerce hook, no need to add a comment.
		$fragments = apply_filters( 'woocommerce_add_to_cart_fragments', array(
			'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
		) );

		// Get cart hash.
		$cart_hash = WC()->cart->get_cart_hash();

		// Get success message.
		$message = wc_add_to_cart_message( array( $product_id => $quantity ), true, true );

		wp_send_json_success( array(
			'fragments' => $fragments,
			'cart_hash' => $cart_hash,
			'message'   => $message,
		) );
	}
}
