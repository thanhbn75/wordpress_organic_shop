<?php
/**
 * Merchant Select2 Choices.
 *
 * Provides formatted Select2 choice data for categories, tags,
 * brands, user roles, and customers. Extracted from
 * {@see Merchant_Admin_Options} to isolate data-provider concerns.
 *
 * @package Merchant
 * @since   1.9.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Select2_Choices
 *
 * Static data providers that return `{id, text}` arrays
 * formatted for Select2 dropdowns in module settings.
 *
 * @since 1.9.3
 */
class Merchant_Select2_Choices {

	/**
	 * Get product category choices formatted for Select2.
	 *
	 * Retrieves all WooCommerce product categories and formats them
	 * as a flat array of `{id, text}` objects suitable for Select2,
	 * preserving hierarchical indentation via non-breaking spaces.
	 *
	 * @since 1.9.3
	 *
	 * @return array<int, array{id: string, text: string}> Formatted category choices.
	 */
	public static function get_category_select2_choices() {
		$choices    = array();
		$categories = merchant_get_product_categories();

		if ( is_array( $categories ) && ! empty( $categories ) ) {
			$choices = self::build_category_select2_choices( $categories );
		}

		return $choices;
	}

	/**
	 * Recursively build Select2 choices from hierarchical categories.
	 *
	 * @since 1.9.3
	 *
	 * @param array<int, array{slug?: string, name?: string, children?: array<int, mixed>}> $categories Category data.
	 * @param int $level Current nesting depth (used for indentation).
	 *
	 * @return array<int, array{id: string, text: string}> Formatted choices.
	 */
	private static function build_category_select2_choices( $categories, $level = 0 ) {
		$choices = array();

		foreach ( $categories as $cat ) {
			$indent = str_repeat( '&nbsp;', $level * 4 );

			$choices[] = array(
				'id'   => esc_attr( $cat['slug'] ?? '' ),
				'text' => esc_html( $indent . ( $cat['name'] ?? '' ) ),
			);

			if ( ! empty( $cat['children'] ) ) {
				$choices = array_merge( $choices, self::build_category_select2_choices( $cat['children'], $level + 1 ) );
			}
		}

		return $choices;
	}

	/**
	 * Get product tag choices formatted for Select2.
	 *
	 * Retrieves all WooCommerce product tags and formats them
	 * as a flat array of `{id, text}` objects for Select2.
	 *
	 * @since 1.9.3
	 *
	 * @return array<int, array{id: string, text: string}> Formatted tag choices.
	 */
	public static function get_tag_select2_choices() {
		$choices = array();
		$tags    = merchant_get_product_tags();

		if ( is_array( $tags ) && ! empty( $tags ) ) {
			foreach ( $tags as $slug => $name ) {
				$choices[] = array(
					'id'   => esc_attr( $slug ),
					'text' => esc_html( $name ),
				);
			}
		}

		return $choices;
	}

	/**
	 * Get product brand choices formatted for Select2.
	 *
	 * Retrieves all terms from the `product_brand` taxonomy
	 * (WooCommerce Brands) and formats them for Select2.
	 * Returns an empty array if the taxonomy does not exist.
	 *
	 * @since 1.9.3
	 *
	 * @return array<int, array{id: string, text: string}> Formatted brand choices.
	 */
	public static function get_brand_select2_choices() {
		$choices = array();

		if ( ! taxonomy_exists( 'product_brand' ) ) {
			return $choices;
		}

		$brands = get_terms( array(
			'taxonomy'   => 'product_brand',
			'hide_empty' => false,
		) );

		if ( is_array( $brands ) && ! is_wp_error( $brands ) && ! empty( $brands ) ) { // @phpstan-ignore function.impossibleType
			foreach ( $brands as $brand ) {
				$choices[] = array(
					'id'   => esc_attr( $brand->slug ),
					'text' => esc_html( $brand->name ),
				);
			}
		}

		return $choices;
	}

	/**
	 * Get user role choices formatted for Select2.
	 *
	 * Retrieves all editable WordPress user roles and formats
	 * them as `{id, text}` objects for Select2 dropdowns.
	 *
	 * @since 1.9.3
	 *
	 * @return array<int, array{id: string, text: string}> Formatted role choices.
	 */
	public static function get_user_roles_select2_choices() {
		$choices    = array();
		$user_roles = get_editable_roles();

		if ( ! empty( $user_roles ) ) {
			foreach ( $user_roles as $role_id => $role_data ) {
				$choices[] = array(
					'id'   => $role_id,
					'text' => $role_data['name'],
				);
			}
		}

		return $choices;
	}

	/**
	 * Get customer user choices formatted for Select2.
	 *
	 * Retrieves all users with the `customer` role and returns them
	 * as `{id, text}` objects. Results are cached in a transient
	 * with no expiration; the cache is cleared via
	 * {@see Merchant_Select2_Choices::clear_customer_choices_cache()}.
	 *
	 * @since 1.9.3
	 *
	 * @return array<int, array{id: int, text: string}> Formatted customer choices.
	 */
	public static function get_customers_select2_choices() {
		$cache_key = 'customers_select2_choices';
		$choices   = get_transient( $cache_key );

		if ( ! empty( $choices ) && is_array( $choices ) ) {
			return $choices;
		}

		$customer_users = get_users(
			array(
				'role'   => 'customer',
				'fields' => array( 'ID', 'display_name' ),
			)
		);

		$choices = array();
		if ( ! empty( $customer_users ) ) {
			foreach ( $customer_users as $user ) {
				$choices[] = array(
					'id'   => $user->ID,
					'text' => $user->display_name,
				);
			}
		}

		set_transient( $cache_key, $choices );

		return $choices;
	}

	/**
	 * Clear the customers Select2 choices transient cache.
	 *
	 * Hooked to `clean_user_cache` to invalidate the customer
	 * list whenever a customer user is created, updated, or deleted.
	 *
	 * @since 1.9.3
	 *
	 * @param int     $user_id The user ID being cleaned.
	 * @param WP_User $user    The user object.
	 *
	 * @return void
	 */
	public function clear_customer_choices_cache( $user_id, $user ) {
		$user_roles = ! empty( $user->roles ) ? $user->roles : array();
		if ( in_array( 'customer', $user_roles, true ) ) {
			delete_transient( 'customers_select2_choices' );
		}
	}
}
