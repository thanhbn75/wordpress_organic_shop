<?php

/**
 * Buy Now - Exclusion Manager
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Exclusion manager for Buy Now module.
 *
 * Handles product exclusion logic based on module settings.
 */
class Merchant_Buy_Now_Exclusion {

	/**
	 * The module instance.
	 *
	 * @var Merchant_Buy_Now
	 */
	private $module;

	/**
	 * Cache for exclusion check results.
	 *
	 * @var array
	 */
	private $exclusion_cache = array();

	/**
	 * Constructor.
	 *
	 * @param Merchant_Buy_Now $module The module instance.
	 */
	public function __construct( Merchant_Buy_Now $module ) {
		$this->module = $module;
	}

	/**
	 * Determines whether a product should be excluded based on exclusion settings.
	 *
	 * @param WC_Product $product The product object.
	 *
	 * @return bool True if the product should be excluded, false otherwise.
	 */
	public function should_exclude( $product ) {
		$product_id = $product->get_id();

		// Check cache first
		if ( isset( $this->exclusion_cache[ $product_id ] ) ) {
			return $this->exclusion_cache[ $product_id ];
		}

		$settings = $this->module->get_module_settings();

		// Exclusion feature disabled → quick exit
		if ( empty( $settings['exclusion'] ) ) {
			return $this->cache_result( $product_id, false );
		}

		// Check individual exclusion rules
		if ( $this->is_excluded_by_product_id( $product_id ) ) {
			return $this->cache_result( $product_id, true );
		}

		if ( $this->is_excluded_by_category( $product_id ) ) {
			return $this->cache_result( $product_id, true );
		}

		if ( $this->is_excluded_by_tag( $product_id ) ) {
			return $this->cache_result( $product_id, true );
		}

		if ( $this->is_excluded_by_brand( $product_id ) ) {
			return $this->cache_result( $product_id, true );
		}

		/**
		 * Filters whether the product should be excluded from the buy now button.
		 *
		 * @param bool $should_exclude Whether the product should be excluded.
		 * @param int  $product_id     The ID of the product being checked.
		 *
		 * @since 2.2.3
		 */
		$result = apply_filters( 'merchant_buy_now_should_exclude', false, $product_id );

		return $this->cache_result( $product_id, $result );
	}

	/**
	 * Check if product is explicitly excluded by ID
	 *
	 * @param int $product_id
	 *
	 * @return bool
	 */
	private function is_excluded_by_product_id( $product_id ) {
		$excluded_products = $this->get_excluded_products();

		return ! empty( $excluded_products ) && in_array( $product_id, $excluded_products, true );
	}

	/**
	 * Check if product belongs to any excluded category
	 *
	 * @param int $product_id
	 *
	 * @return bool
	 */
	private function is_excluded_by_category( $product_id ) {
		$excluded_categories = $this->get_excluded_categories();

		return ! empty( $excluded_categories ) && has_term( $excluded_categories, 'product_cat', $product_id );
	}

	/**
	 * Check if product has any excluded tag
	 *
	 * @param int $product_id
	 *
	 * @return bool
	 */
	private function is_excluded_by_tag( $product_id ) {
		$excluded_tags = $this->get_excluded_tags();

		return ! empty( $excluded_tags ) && has_term( $excluded_tags, 'product_tag', $product_id );
	}

	/**
	 * Check if product belongs to any excluded brand
	 *
	 * @param int   $product_id
	 *
	 * @return bool
	 */
	private function is_excluded_by_brand( $product_id ) {
		$excluded_brands = $this->get_excluded_brands();

		return ! empty( $excluded_brands ) && has_term( $excluded_brands, 'product_brand', $product_id );
	}

	/**
	 * Cache the result and return it
	 *
	 * @param int  $product_id
	 * @param bool $value
	 *
	 * @return bool
	 */
	private function cache_result( $product_id, $value ) {
		$this->exclusion_cache[ $product_id ] = (bool) $value;

		return $this->exclusion_cache[ $product_id ];
	}

	/**
	 * Retrieves the list of excluded product IDs from the module settings.
	 *
	 * @return array An array of excluded product IDs.
	 */
	public function get_excluded_products() {
		$settings          = $this->module->get_module_settings();
		$excluded_products = ! empty( $settings['excluded_products'] ) ? merchant_parse_product_ids( $settings['excluded_products'] ) : array();

		/**
		 * Filters the list of excluded product IDs for the buy now module.
		 *
		 * @param array $excluded_products List of excluded product IDs.
		 *
		 * @since 2.2.3
		 */
		return apply_filters( 'merchant_buy_now_excluded_products', $excluded_products );
	}

	/**
	 * Retrieves the list of excluded category slugs from the module settings.
	 *
	 * @return array An array of excluded category slugs.
	 */
	public function get_excluded_categories() {
		$settings            = $this->module->get_module_settings();
		$excluded_categories = ! empty( $settings['excluded_categories'] ) ? $settings['excluded_categories'] : array();

		/**
		 * Filters the list of excluded category slugs for the buy now module.
		 *
		 * @param array $excluded_categories List of excluded category slugs.
		 *
		 * @since 2.2.3
		 */
		return apply_filters( 'merchant_buy_now_excluded_categories', $excluded_categories );
	}

	/**
	 * Retrieves the list of excluded tag slugs from the module settings.
	 *
	 * @return array An array of excluded tag slugs.
	 */
	public function get_excluded_tags() {
		$settings      = $this->module->get_module_settings();
		$excluded_tags = ! empty( $settings['excluded_tags'] ) ? $settings['excluded_tags'] : array();

		/**
		 * Filters the list of excluded tag slugs for the buy now module.
		 *
		 * @param array $excluded_tags List of excluded tag slugs.
		 *
		 * @since 2.2.3
		 */
		return apply_filters( 'merchant_buy_now_excluded_tags', $excluded_tags );
	}

	/**
	 * Retrieves the list of excluded brand slugs from the module settings.
	 *
	 * @return array An array of excluded brand slugs.
	 */
	public function get_excluded_brands() {
		$settings        = $this->module->get_module_settings();
		$excluded_brands = ! empty( $settings['excluded_brands'] ) ? $settings['excluded_brands'] : array();

		/**
		 * Filters the list of excluded brand slugs for the buy now module.
		 *
		 * @param array $excluded_brands List of excluded brand slugs.
		 *
		 * @since 2.2.3
		 */
		return apply_filters( 'merchant_buy_now_excluded_brands', $excluded_brands );
	}

	/**
	 * Clears the exclusion cache.
	 *
	 * Useful for testing or when settings are updated.
	 *
	 * @return void
	 */
	public function clear_cache() {
		$this->exclusion_cache = array();
	}

	/**
	 * Gets the current cache for debugging purposes.
	 *
	 * @return array The exclusion cache.
	 */
	public function get_cache() {
		return $this->exclusion_cache;
	}
}
