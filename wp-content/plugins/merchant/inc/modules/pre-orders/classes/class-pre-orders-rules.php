<?php
/**
 * Pre-Orders Rules Repository.
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Merchant_Pre_Orders_Rules_Repository
 *
 * Provides a centralized interface for resolving and managing pre-order eligibility.
 *
 * This repository is responsible for the complex orchestration of rule matching,
 * ensuring that product-specific overrides are properly prioritized over global
 * campaign settings. It handles all aspects of data retrieval, normalization,
 * and structural validation for pre-order configurations.
 */
class Merchant_Pre_Orders_Rules_Repository {

	/**
	 * The module ID.
	 *
	 * @var string
	 */
	const MODULE_ID = 'pre-orders';

	/**
	 * The date time format.
	 *
	 * @var string
	 */
	const DATE_TIME_FORMAT = 'm-d-Y h:i A';

	/**
	 * Resolves the final applicable pre-order rule for a given product.
	 *
	 * The resolution logic starts by checking if pre-orders are explicitly enabled/disabled
	 * at the product level. If it's set to 'product' mode, it attempts to build a rule
	 * from product specific meta. If no valid product rule is found, it falls back to
	 * scanning the global campaign rules for a match.
	 *
	 * @param int $product_id The product ID to check.
	 *
	 * @return array The compiled rule settings or an empty array if pre-orders do not apply.
	 */
	public static function get_rule_for_product( int $product_id ) {
		// 1. Check Product-Specific Settings
		// We check meta directly to determine mode.
		$pre_order_mode = get_post_meta( $product_id, '_merchant_pre_order_mode', true );

		if ( 'disabled' === $pre_order_mode ) {
			return array();
		}

		if ( 'product' === $pre_order_mode ) {
			$product_rule = self::get_product_scope_rule( $product_id );

			/**
			 * Filter to get the available product rule.
			 *
			 * @param array $product_rule The product rule.
			 * @param int $product_id The product ID.
			 *
			 * @return array The available product rule.
			 *
			 * @since 2.2.4
			 */
			return apply_filters( 'merchant_pre_order_available_rule', $product_rule, $product_id );
		}

		// 2. Global Rules Fallback
		return self::find_matching_global_rule( $product_id );
	}

	/**
	 * Iterates through available global pre-order campaigns to find the first criteria match.
	 *
	 * Each global rule is validated against:
	 * 1. Campaign active status.
	 * 2. Time availability (start and end times).
	 * 3. User targeting conditions (roles/users).
	 * 4. Product exclusions (categories/tags/brands/specific IDs).
	 * 5. Scope matching (all products vs specific categories/tags/brands).
	 *
	 * @param int $product_id The product ID to match against global rules.
	 *
	 * @return array The first matching rule data or an empty array.
	 */
	private static function find_matching_global_rule( int $product_id ) {
		$rules          = self::get_global_rules();
		$current_time   = merchant_get_current_timestamp();
		$available_rule = array();

		foreach ( $rules as $rule ) {
			if ( isset( $rule['campaign_status'] ) && 'inactive' === $rule['campaign_status'] ) {
				continue;
			}

			if ( ! self::is_valid_rule( $rule ) ) {
				continue;
			}

			$rule = self::prepare_rule( $rule );

			// Time Validation
			if ( ! empty( $rule['pre_order_start'] ) && $rule['pre_order_start'] > $current_time ) {
				continue;
			}
			if ( ! empty( $rule['pre_order_end'] ) && $rule['pre_order_end'] < $current_time ) {
				continue;
			}

			// User Condition Check
			if ( ! merchant_is_user_condition_passed( $rule ) ) {
				continue;
			}

			// Exclusion Check
			if ( self::is_product_excluded( $product_id, $rule ) ) {
				continue;
			}

			// Scope Matching
			if ( self::rule_matches_product( $rule, $product_id ) ) {
				$available_rule = $rule;
				break; // First match wins
			}
		}

		/**
		 * Filter to get the available product rule.
		 *
		 * @param array $product_rule The product rule.
		 * @param int $product_id The product ID.
		 *
		 * @return array The available product rule.
		 *
		 * @since 2.2.4
		 */
		return apply_filters( 'merchant_pre_order_available_rule', $available_rule, $product_id );
	}

	/**
	 * Determines if a global rule's scope includes the specified product.
	 *
	 * This method evaluates the rule's primary trigger (all products, specific products,
	 * or taxonomy-based rules like categories, tags, or brands).
	 *
	 * @param array $rule The rule configuration to evaluate.
	 * @param int $product_id The product ID to check against the rule scope.
	 *
	 * @return bool True if the product falls within the rule's scope, false otherwise.
	 */
	private static function rule_matches_product( array $rule, int $product_id ) {
		$trigger = $rule['trigger_on'] ?? 'product';

		if ( 'all' === $trigger ) {
			return true;
		}

		if ( 'product' === $trigger ) {
			return in_array( $product_id, $rule['product_ids'] ?? array(), true );
		}

		// Taxonomy Triggers
		$taxonomy_map = array(
			'category' => 'product_cat',
			'tags'     => 'product_tag',
			'brands'   => 'product_brand',
		);

		if ( isset( $taxonomy_map[ $trigger ] ) ) {
			$taxonomy     = $taxonomy_map[ $trigger ];
			$needed_slugs = array();
			if ( 'category' === $trigger ) {
				$needed_slugs = $rule['category_slugs'] ?? array();
			} elseif ( 'tags' === $trigger ) {
				$needed_slugs = $rule['tag_slugs'] ?? array();
			} elseif ( 'brands' === $trigger ) {
				$needed_slugs = $rule['brand_slugs'] ?? array();
			}

			$terms = get_the_terms( $product_id, $taxonomy );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					if ( in_array( $term->slug, $needed_slugs, true ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Constructs a pre-order rule specifically from a single product's metadata.
	 *
	 * This aggregates style, date, discount, and user targeting settings directly
	 * from the product edit screen, ensuring it matches the same structure as
	 * global rules for unified processing later.
	 *
	 * @param int $product_id The ID of the product being processed.
	 *
	 * @return array Compiled product-level pre-order rule or empty if invalid.
	 */
	private static function get_product_scope_rule( int $product_id ) {
		$rule = array_merge(
			self::get_product_style_settings( $product_id ),
			self::get_product_date_settings( $product_id )
		);

		// Analytics fallback
		$rule['flexible_id'] = 'product-' . $product_id;

		$rule = self::convert_rule_dates_to_timestamps( $rule );

		if ( ! self::is_pre_order_time_valid( $rule ) ) {
			return array();
		}

		$placement         = get_post_meta( $product_id, '_merchant_pre_order_placement', true );
		$rule['placement'] = $placement ? $placement : 'before';

		// Create shipping_timestamp logic
		$rule['shipping_timestamp'] = '';
		if ( ! empty( $rule['shipping_date'] ) ) {
			$rule['shipping_timestamp'] = is_numeric( $rule['shipping_date'] )
				? (int) $rule['shipping_date']
				: merchant_date_string_to_timestamp( $rule['shipping_date'] );
		}

		// Merges
		$rule = array_merge( $rule, self::get_product_discount_settings( $product_id ) );
		$rule = array_merge( $rule, self::get_product_user_conditions( $product_id ) );

		if ( ! merchant_is_user_condition_passed( $rule ) ) {
			return array();
		}

		// Validation: Ensure we have a shipping date before considering this a valid pre-order
		if ( empty( $rule['shipping_date'] ) ) {
			return array();
		}

		return $rule;
	}

	/**
	 * Retrieves the visual configuration for the pre-order interface from product meta.
	 *
	 * Captures button labels, cart notes, and color themes (foreground, backdrop, frames).
	 *
	 * @param int $product_id The product ID.
	 *
	 * @return array Visual setting keys and their corresponding values.
	 */
	private static function get_product_style_settings( int $product_id ) {
		$button_text            = get_post_meta( $product_id, '_merchant_pre_order_button_text', true );
		$cart_label_text        = get_post_meta( $product_id, '_merchant_pre_order_cart_label_text', true );
		$additional_text        = get_post_meta( $product_id, '_merchant_pre_order_additional_text', true );
		$text_color             = get_post_meta( $product_id, '_merchant_pre_order_text_color', true );
		$text_hover_color       = get_post_meta( $product_id, '_merchant_pre_order_text_hover_color', true );
		$border_color           = get_post_meta( $product_id, '_merchant_pre_order_border_color', true );
		$border_hover_color     = get_post_meta( $product_id, '_merchant_pre_order_border_hover_color', true );
		$border_width           = get_post_meta( $product_id, '_merchant_pre_order_border_width', true );
		$border_radius          = get_post_meta( $product_id, '_merchant_pre_order_border_radius', true );
		$background_color       = get_post_meta( $product_id, '_merchant_pre_order_background_color', true );
		$background_hover_color = get_post_meta( $product_id, '_merchant_pre_order_background_hover_color', true );

		return array(
			'button_text'            => ! empty( $button_text ) ? $button_text : __( 'Pre-Order Now', 'merchant' ),
			'cart_label_text'        => ! empty( $cart_label_text ) ? $cart_label_text : __( 'Ships on', 'merchant' ),
			'additional_text'        => $additional_text,
			'text-color'             => ! empty( $text_color ) ? $text_color : '#FFF',
			'text-hover-color'       => ! empty( $text_hover_color ) ? $text_hover_color : '#FFF',
			'border-color'           => ! empty( $border_color ) ? $border_color : '#212121',
			'border-hover-color'     => ! empty( $border_hover_color ) ? $border_hover_color : '#414141',
			'border-width'           => isset( $border_width ) && $border_width !== '' ? $border_width : 0,
			'border-radius'          => isset( $border_radius ) && $border_radius !== '' ? $border_radius : 0,
			'background-color'       => ! empty( $background_color ) ? $background_color : '#212121',
			'background-hover-color' => ! empty( $background_hover_color ) ? $background_hover_color : '#414141',
		);
	}

	/**
	 * Retrieves the temporal constraints for a pre-order from product meta.
	 *
	 * @param int $product_id The product ID.
	 *
	 * @return array Array containing shipping_date, pre_order_start, and pre_order_end.
	 */
	private static function get_product_date_settings( int $product_id ) {
		return array(
			'shipping_date'   => get_post_meta( $product_id, '_merchant_pre_order_shipping_date', true ),
			'pre_order_start' => get_post_meta( $product_id, '_merchant_pre_order_start', true ),
			'pre_order_end'   => get_post_meta( $product_id, '_merchant_pre_order_end', true ),
		);
	}

	/**
	 * Retrieves the financial incentive settings for pre-ordering a product.
	 *
	 * Checks if a discount is enabled and captures the type (percentage/fixed) and amount.
	 *
	 * @param int $product_id The product ID.
	 *
	 * @return array Discount toggle status and details.
	 */
	private static function get_product_discount_settings( int $product_id ) {
		$discount_toggle = get_post_meta( $product_id, '_merchant_pre_order_discount_toggle', true );
		$is_enabled      = ( '1' === $discount_toggle || 1 === $discount_toggle || true === $discount_toggle ); // Robust check

		if ( ! $is_enabled ) {
			return array( 'discount_toggle' => false );
		}

		$discount_type   = get_post_meta( $product_id, '_merchant_pre_order_discount_type', true );
		$discount_amount = get_post_meta( $product_id, '_merchant_pre_order_discount_amount', true );

		return array(
			'discount_toggle' => true,
			'discount_type'   => $discount_type ? $discount_type : 'percentage',
			'discount_amount' => $discount_amount ? $discount_amount : '0',
		);
	}

	/**
	 * Retrieves the user inheritance and visibility rules for a product.
	 *
	 * This includes both the positive targeting (which roles/users can see the pre-order)
	 * and the negative targeting (exclusion rules for specific roles/users).
	 *
	 * @param int $product_id The product ID.
	 *
	 * @return array {
	 * @type string $user_condition The targeting strategy ('all', 'roles', 'users').
	 * @type array $user_condition_roles List of authorized roles (if applicable).
	 * @type array $user_condition_users List of authorized user IDs (if applicable).
	 * @type array $exclude_roles List of denied roles (if applicable).
	 * @type array $exclude_users List of denied user IDs (if applicable).
	 * }
	 */
	private static function get_product_user_conditions( int $product_id ) {
		$targeting_strategy = get_post_meta( $product_id, '_merchant_pre_order_user_condition', true );
		$active_strategy    = ! empty( $targeting_strategy ) ? $targeting_strategy : 'all';

		$conditions = array( 'user_condition' => $active_strategy );

		if ( 'roles' === $active_strategy ) {
			$authorized_roles                   = get_post_meta( $product_id, '_merchant_pre_order_user_condition_roles', true );
			$conditions['user_condition_roles'] = ! empty( $authorized_roles ) ? $authorized_roles : array();
		} elseif ( 'users' === $active_strategy ) {
			$authorized_users                   = get_post_meta( $product_id, '_merchant_pre_order_user_condition_users', true );
			$conditions['user_condition_users'] = ! empty( $authorized_users ) ? $authorized_users : array();
		}

		$exclusion_toggle = get_post_meta( $product_id, '_merchant_pre_order_user_exclusion_enabled', true );
		if ( '1' === $exclusion_toggle || 1 === $exclusion_toggle ) {
			$restricted_roles                     = get_post_meta( $product_id, '_merchant_pre_order_exclude_roles', true );
			$restricted_users                     = get_post_meta( $product_id, '_merchant_pre_order_exclude_users', true );
			$conditions['user_exclusion_enabled'] = true;
			$conditions['exclude_roles']          = ! empty( $restricted_roles ) ? $restricted_roles : array();
			$conditions['exclude_users']          = ! empty( $restricted_users ) ? $restricted_users : array();
		}

		return $conditions;
	}

	/**
	 * Normalizes human-readable date strings into Unix timestamps for comparison logic.
	 *
	 * Processes shipping, start, and end dates within a rule array.
	 *
	 * @param array $rule The rule array containing raw date strings.
	 *
	 * @return array The rule array with normalized integer timestamps.
	 */
	private static function convert_rule_dates_to_timestamps( array $rule ) {
		$fields = array( 'shipping_date', 'pre_order_start', 'pre_order_end' );
		foreach ( $fields as $field ) {
			if ( ! empty( $rule[ $field ] ) && ! is_numeric( $rule[ $field ] ) ) {
				$rule[ $field ] = merchant_date_string_to_timestamp( $rule[ $field ] );
			}
		}

		return $rule;
	}

	/**
	 * Ensures the current site time falls within the rule's active window.
	 *
	 * @param array $rule The rule to validate, containing pre_order_start and pre_order_end timestamps.
	 *
	 * @return bool True if currently active, false if not yet started or already expired.
	 */
	private static function is_pre_order_time_valid( array $rule ) {
		$current_time = merchant_get_current_timestamp();
		if ( ! empty( $rule['pre_order_start'] ) && $rule['pre_order_start'] > $current_time ) {
			return false;
		}
		if ( ! empty( $rule['pre_order_end'] ) && $rule['pre_order_end'] < $current_time ) {
			return false;
		}

		return true;
	}

	/**
	 * Fetches the master list of global pre-order rules from the options framework.
	 *
	 * @return array Collection of global campaign rules.
	 */
	public static function get_global_rules() {
		return Merchant_Admin_Options::get( self::MODULE_ID, 'rules', array() );
	}

	/**
	 * Performs a structural integrity check on a rule to ensure all required fields are present.
	 *
	 * Validates triggers, discount formatting, and essential interface fields like button text.
	 *
	 * @param array $rule The rule data to validate.
	 *
	 * @return bool True if valid for processing, false otherwise.
	 */
	private static function is_valid_rule( array $rule ) {
		if ( ! isset( $rule['trigger_on'] ) ) {
			return false;
		}
		if ( 'product' === $rule['trigger_on'] && empty( $rule['product_ids'] ) ) {
			return false;
		}
		if ( 'category' === $rule['trigger_on'] && empty( $rule['category_slugs'] ) ) {
			return false;
		}
		if ( 'tags' === $rule['trigger_on'] && empty( $rule['tag_slugs'] ) ) {
			return false;
		}
		if ( 'brands' === $rule['trigger_on'] && empty( $rule['brand_slugs'] ) ) {
			return false;
		}

		// Discount validation
		if ( ! empty( $rule['discount_toggle'] ) ) {
			if ( ! isset( $rule['discount_type'], $rule['discount_amount'] ) ) {
				return false;
			}
		}

		// Shipping / Interface
		if ( empty( $rule['shipping_date'] ) ) {
			return false;
		}
		if ( empty( $rule['button_text'] ) ) {
			return false;
		}
		if ( empty( $rule['placement'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Sanitizes and normalizes global rule data before it's used in matching logic.
	 *
	 * Handles parsing of comma-separated IDs and converting date strings to common timestamps.
	 *
	 * @param array $rule The raw rule data from settings.
	 *
	 * @return array The prepared rule data.
	 */
	private static function prepare_rule( array $rule ) {
		if ( 'product' === $rule['trigger_on'] && is_string( $rule['product_ids'] ) ) {
			$rule['product_ids'] = merchant_parse_product_ids( $rule['product_ids'] );
		}

		// Dates
		if ( ! empty( $rule['pre_order_start'] ) ) {
			$rule['pre_order_start'] = merchant_convert_date_to_timestamp( $rule['pre_order_start'], self::DATE_TIME_FORMAT );
		}
		if ( ! empty( $rule['pre_order_end'] ) ) {
			$rule['pre_order_end'] = merchant_convert_date_to_timestamp( $rule['pre_order_end'], self::DATE_TIME_FORMAT );
		}

		$rule['shipping_timestamp'] = merchant_convert_date_to_timestamp( $rule['shipping_date'], self::DATE_TIME_FORMAT );

		return $rule;
	}

	/**
	 * Checks if a specific product (or its parent if it's a variation) is explicitly excluded from a rule.
	 *
	 * Evaluates exclusions based on:
	 * 1. Specific Product IDs.
	 * 2. Product Categories.
	 * 3. Product Tags.
	 * 4. Product Brands.
	 *
	 * @param int $product_id The product ID to check.
	 * @param array $rule The rule containing exclusion settings.
	 *
	 * @return bool True if the product is excluded, false if it can proceed.
	 */
	public static function is_product_excluded( int $product_id, array $rule ) {
		$trigger = $rule['trigger_on'] ?? 'product';

		// If variation, checks parent
		$product     = wc_get_product( $product_id );
		$_product_id = $product && $product->is_type( 'variation' ) ? $product->get_parent_id() : $product_id;

		// 1. Exclude Products
		if ( ! empty( $rule['exclude_products_toggle'] ) ) {
			$excluded_ids = merchant_parse_product_ids( $rule['excluded_products'] ?? array() );
			if ( in_array( (int) $product_id, $excluded_ids, true ) || in_array( (int) $_product_id, $excluded_ids, true ) ) {
				return true;
			}
		}

		// 2. Exclude Categories
		if ( ! empty( $rule['exclude_categories_toggle'] ) && 'category' !== $trigger ) {
			$slugs = $rule['excluded_categories'] ?? array();
			if ( ! empty( $slugs ) && has_term( $slugs, 'product_cat', $_product_id ) ) {
				return true;
			}
		}

		// 3. Exclude Tags
		if ( ! empty( $rule['exclude_tags_toggle'] ) && 'tags' !== $trigger ) {
			$slugs = $rule['excluded_tags'] ?? array();
			if ( ! empty( $slugs ) && has_term( $slugs, 'product_tag', $_product_id ) ) {
				return true;
			}
		}

		// 4. Exclude Brands
		if ( ! empty( $rule['exclude_brands_toggle'] ) && 'brands' !== $trigger ) {
			$slugs = $rule['excluded_brands'] ?? array();
			if ( ! empty( $slugs ) && has_term( $slugs, 'product_brand', $_product_id ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Extracts and formats the specialized sale data from a pre-order rule.
	 *
	 * This is typically used to inject discount information into the checkout or cart logic.
	 *
	 * @param array $rule The full rule data.
	 *
	 * @return array|false Formatted sale details or false if no discount is applied.
	 */
	public static function get_rule_sale( array $rule ) {
		$sale = false;
		if ( isset( $rule['discount_toggle'] ) && $rule['discount_toggle'] ) {
			$sale = array(
				'discount_type'   => $rule['discount_type'],
				'discount_amount' => $rule['discount_amount'],
			);
		}

		/**
		 * Filter the pre order sale.
		 *
		 * @param array $sale The pre order sale.
		 * @param array $rule The pre order rule.
		 *
		 * @since 1.9.9
		 */
		return apply_filters( 'merchant_pre_order_rule_sale', $sale, $rule );
	}
}
