<?php
/**
 * Merchant Admin AJAX Handlers.
 *
 * Handles all AJAX requests for the Merchant admin options panel.
 * Extracted from Merchant_Admin_Options to keep the orchestrator slim.
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Admin_Ajax
 *
 * Handles all WordPress AJAX requests for the Merchant admin options panel.
 * Extracted from {@see Merchant_Admin_Options} to keep the orchestrator focused
 * on rendering and saving. Each method is registered as a `wp_ajax_*` handler.
 *
 * Provides search endpoints for products, reviews, and Select2 content,
 * as well as module settings backup/restore functionality.
 *
 * @since 2.2.5
 */
class Merchant_Admin_Ajax {

	/**
	 * Create a WP_Query instance.
	 *
	 * @since 2.2.5
	 *
	 * @param array $args Query arguments.
	 *
	 * @return WP_Query
	 */
	protected static function make_wp_query( array $args ): WP_Query {
		return new WP_Query( $args );
	}

	/**
	 * Create a WP_User_Query instance.
	 *
	 * @since 2.2.5
	 *
	 * @param array $args Query arguments.
	 *
	 * @return WP_User_Query
	 */
	protected static function make_wp_user_query( array $args ): WP_User_Query {
		return new WP_User_Query( $args );
	}

	/**
	 * Create a WC_Product_Query instance.
	 *
	 * @since 2.2.5
	 *
	 * @param array $args Query arguments.
	 *
	 * @return WC_Product_Query
	 */
	protected static function make_wc_product_query( array $args ) {
		return new WC_Product_Query( $args );
	}

	/**
	 * Constructor.
	 *
	 * Registers all `wp_ajax_*` action hooks for Merchant admin AJAX handlers.
	 *
	 * @since 2.2.5
	 */
	public function __construct() {
		add_action( 'wp_ajax_merchant_create_page_control', array( $this, 'create_page_control_ajax_callback' ) );
		add_action( 'wp_ajax_merchant_admin_options_select_ajax', array( $this, 'select_content_ajax' ) );
		add_action( 'wp_ajax_merchant_admin_products_search', array( $this, 'products_search' ) );
		add_action( 'wp_ajax_merchant_get_review_images', array( $this, 'get_review_images' ) );
		add_action( 'wp_ajax_merchant_search_reviews', array( $this, 'search_reviews' ) );
		add_action( 'wp_ajax_merchant_load_more_reviews', array( $this, 'load_more_reviews' ) );
		add_action( 'wp_ajax_merchant_get_module_settings', array( $this, 'get_module_settings' ) );
		add_action( 'wp_ajax_merchant_restore_module_settings', array( $this, 'restore_module_settings' ) );
	}

	// ───────────────────────────────────────────────────────────────
	// Page creation
	// ───────────────────────────────────────────────────────────────

	/**
	 * Create a new WordPress page via AJAX.
	 *
	 * @since 2.2.5
	 *
	 * @return void Sends JSON response and exits.
	 */
	public function create_page_control_ajax_callback() {
		check_ajax_referer( 'customize-create-page-control-nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'You are not allowed to do this.' );
		}

		$postarr = self::build_page_post_array();
		$page_id = wp_insert_post( $postarr );

		if ( is_wp_error( $page_id ) ) {
			wp_send_json( array( 'status' => 'error' ) );
		}

		$option_name = isset( $_POST['option_name'] ) ? sanitize_text_field( $_POST['option_name'] ) : '';
		if ( $option_name ) {
			update_option( $option_name, $page_id );
		}

		wp_send_json( array( 'status' => 'success', 'page_id' => $page_id ) );
	}

	/**
	 * Build the post array for page creation from POST data.
	 *
	 * @since 2.2.5
	 *
	 * @return array Post array suitable for wp_insert_post.
	 */
	private static function build_page_post_array(): array {
		// Nonce verified in create_page() before calling this method.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$page_title      = isset( $_POST['page_title'] ) ? sanitize_text_field( $_POST['page_title'] ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$page_meta_key   = isset( $_POST['page_meta_key'] ) ? sanitize_text_field( $_POST['page_meta_key'] ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$page_meta_value = isset( $_POST['page_meta_value'] ) ? sanitize_text_field( $_POST['page_meta_value'] ) : '';

		$meta_input = array();
		if ( $page_meta_key && $page_meta_value ) {
			$meta_input = array( $page_meta_key => $page_meta_value );
		}

		return array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $page_title,
			'post_content' => '',
			'meta_input'   => $meta_input,
		);
	}

	// ───────────────────────────────────────────────────────────────
	// Select2 content search
	// ───────────────────────────────────────────────────────────────

	/**
	 * AJAX handler for Select2 content search.
	 *
	 * @since 2.2.5
	 *
	 * @return void Sends JSON response and exits.
	 */
	public function select_content_ajax() {
		$term   = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';
		$nonce  = isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '';
		$source = isset( $_GET['source'] ) ? sanitize_text_field( wp_unslash( $_GET['source'] ) ) : '';

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'You are not allowed to do this.' );
		}

		if ( empty( $term ) || empty( $source ) || empty( $nonce ) || ! wp_verify_nonce( $nonce, 'merchant_admin_options' ) ) {
			wp_send_json_error();
		}

		$options = ( 'user' === $source )
			? self::search_users( $term )
			: self::search_posts_or_products( $term, $source );

		wp_send_json_success( $options );
	}

	/**
	 * Search posts or products for Select2.
	 *
	 * @since 2.2.5
	 *
	 * @param string $term   Search term.
	 * @param string $source Post type ('post' or 'product').
	 *
	 * @return array Select2-compatible results.
	 */
	private static function search_posts_or_products( string $term, string $source ): array {
		$query = static::make_wp_query( array(
			's'              => $term,
			'post_type'      => $source,
			'post_status'    => 'publish',
			'posts_per_page' => 25,
			'order'          => 'DESC',
		) );

		$options = array();
		foreach ( $query->posts as $post ) {
			$options[] = array( 'id' => $post->ID, 'text' => $post->post_title );
		}

		return $options;
	}

	/**
	 * Search users for Select2.
	 *
	 * @since 2.2.5
	 *
	 * @param string $term Search term.
	 *
	 * @return array Select2-compatible results.
	 */
	private static function search_users( string $term ): array {
		$query = static::make_wp_user_query( array(
			'search'         => '*' . $term . '*',
			'search_columns' => array( 'user_login', 'user_nicename', 'user_email', 'user_url' ),
			'number'         => 25,
		) );

		$options = array();
		foreach ( $query->results as $user ) {
			$options[] = array( 'id' => $user->ID, 'text' => $user->display_name );
		}

		return $options;
	}

	// ───────────────────────────────────────────────────────────────
	// Reviews
	// ───────────────────────────────────────────────────────────────

	/**
	 * AJAX handler for searching product reviews.
	 *
	 * @since 1.10.4
	 *
	 * @return void Sends JSON response and exits.
	 */
	public function search_reviews() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'merchant_admin_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Nonce verification failed', 'merchant' ) ) );
		}

		$term = '';
		if ( isset( $_POST['search'] ) && ! empty( $_POST['search'] ) ) {
			$term = trim( sanitize_text_field( $_POST['search'] ) );
		}
		wp_send_json_success( self::products_selector_search_results( $term ) );
	}

	/**
	 * AJAX handler for loading additional product reviews.
	 *
	 * @since 1.10.4
	 *
	 * @return void Sends JSON response and exits.
	 */
	public function load_more_reviews() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'merchant_admin_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Nonce verification failed', 'merchant' ) ) );
		}

		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$offset = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;
		if( 0 === $product_id ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid product ID', 'merchant' ) ) );
		}

		wp_send_json_success( self::get_rendered_product_reviews( wc_get_product( $product_id ), $offset ) );
	}

	// ───────────────────────────────────────────────────────────────
	// Module settings backup / restore
	// ───────────────────────────────────────────────────────────────

	/**
	 * AJAX handler for exporting module settings.
	 *
	 * @since 2.2.5
	 *
	 * @return void Sends JSON response and exits.
	 */
	public function get_module_settings() {
		if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_GET['nonce'] ), 'merchant_admin_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Nonce verification failed', 'merchant' ) ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to access this page', 'merchant' ) ) );
		}

		if ( ! isset( $_GET['module_id'] ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Module ID is required', 'merchant' ) ) );
		}

		$module_id = sanitize_text_field( $_GET['module_id'] );

		$modules = merchant_get_modules_data();

		if ( ! isset( $modules[ $module_id ] ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Module not found', 'merchant' ) ) );
		}

		$module_object = Merchant_Modules::get_module( $module_id );

		if ( ! $module_object ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Module not available', 'merchant' ) ) );
		}

		wp_send_json_success( array(
			'timestamp' => time(),
			'module_id' => $module_id,
			'settings'  => $module_object->get_module_settings(),
		) );
	}

	/**
	 * AJAX handler for restoring module settings from a backup.
	 *
	 * @since 2.2.5
	 *
	 * @return void Sends JSON response and exits.
	 */
	public function restore_module_settings() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'merchant_admin_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Nonce verification failed', 'merchant' ) ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to access this page', 'merchant' ) ) );
		}

		$module_id          = self::get_validated_module_id( $_POST, 'module_id' );
		$sanitized_settings = self::parse_and_validate_settings( $module_id );

		$module_object = Merchant_Modules::get_module( $module_id );

		if ( ! $module_object ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Module not available', 'merchant' ) ) );
		}

		$module_object->update_module_settings( $sanitized_settings['settings'] );

		$admin_url = add_query_arg( array(
			'page'   => 'merchant',
			'module' => $module_id,
		), esc_url( admin_url( 'admin.php' ) ) );

		wp_send_json_success( array(
			'message'      => esc_html__( 'Settings restored successfully', 'merchant' ),
			'redirect_url' => $admin_url,
		) );
	}

	/**
	 * Validate and return a module ID from request data.
	 *
	 * @since 2.2.5
	 *
	 * @param array  $data Request data ($_GET or $_POST).
	 * @param string $key  Key to look up.
	 *
	 * @return string Sanitized module ID.
	 */
	private static function get_validated_module_id( array $data, string $key ): string {
		if ( ! isset( $data[ $key ] ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Module ID is required', 'merchant' ) ) );
		}

		$module_id = sanitize_text_field( $data[ $key ] );
		$modules   = merchant_get_modules_data();

		if ( ! isset( $modules[ $module_id ] ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Module not found', 'merchant' ) ) );
		}

		return $module_id;
	}

	/**
	 * Parse, validate, and sanitize module settings from POST JSON.
	 *
	 * @since 2.2.5
	 *
	 * @param string $module_id Expected module ID for cross-check.
	 *
	 * @return array Sanitized settings array.
	 */
	private static function parse_and_validate_settings( string $module_id ): array {
		// Nonce verified in restore_module_settings() before calling this method.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST['module_settings'] ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Default settings not found', 'merchant' ) ) );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
		$module_settings = json_decode( wp_unslash( $_POST['module_settings'] ), true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid settings data', 'merchant' ), $_POST['module_settings'] ) );
		}

		$sanitized = map_deep( $module_settings, 'sanitize_text_field' );

		if ( ! isset( $sanitized['module_id'] ) || $sanitized['module_id'] !== $module_id ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid module ID', 'merchant' ) ) );
		}

		if ( ! isset( $sanitized['settings'] ) || ! is_array( $sanitized['settings'] ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid or missing settings data', 'merchant' ) ) );
		}

		return $sanitized;
	}

	// ───────────────────────────────────────────────────────────────
	// Review images
	// ───────────────────────────────────────────────────────────────

	/**
	 * AJAX handler for retrieving review images.
	 *
	 * @since 1.10.4
	 *
	 * @return void Sends JSON response and exits.
	 */
	public function get_review_images() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'merchant_admin_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Nonce verification failed', 'merchant' ) ) );
		}
		$review_id = 0;
		if ( ! isset( $_POST['review_id'] ) || ! is_numeric( $_POST['review_id'] ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid review ID', 'merchant' ) ) );
		} else {
			$review_id = absint( $_POST['review_id'] );
		}
		$photos_ids = get_comment_meta( $review_id, 'review_images', true );
		if ( empty( $photos_ids ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'No images found', 'merchant' ) ) );
		}
		$images   = array_map( static function ( $image_id ) {
			return wp_get_attachment_image_url( $image_id, 'full' );
		}, $photos_ids );
		$response = '<div class="review-photos-popup images-count-' . count( $images ) . '" data-images-count="' . count( $images ) . '">';
		$response .= '<div class="overlay"></div>';
		foreach ( $images as $image ) {
			$response .= '<div class="review-photo"><a href="' . esc_url( $image ) . '" target="_blank"><img src="' . esc_url( $image ) . '" alt=""></a></div>';
		}
		$response .= '</div>';

		wp_send_json_success( $response );
	}

	// ───────────────────────────────────────────────────────────────
	// Product search
	// ───────────────────────────────────────────────────────────────

	/**
	 * AJAX handler for WooCommerce product search.
	 *
	 * Orchestrates: validate → parse types → build query → render results.
	 *
	 * @since 2.2.5
	 *
	 * @return void Outputs HTML and exits.
	 */
	public function products_search() {
		check_ajax_referer( 'merchant_admin_options', 'nonce' );

		if ( ! isset( $_POST['keyword'] ) || empty( $_POST['keyword'] ) ) {
			wp_die();
			return; // @codeCoverageIgnore
		}

		$keyword    = sanitize_text_field( $_POST['keyword'] );
		$types      = self::get_product_search_types();
		$hierarchy  = self::is_hierarchy_search( $types );
		$added_ids  = isset( $_POST['ids'] ) ? explode( ',', sanitize_text_field( $_POST['ids'] ) ) : array();
		$query_args = self::build_product_search_query_args( $keyword, $types, $added_ids );
		$query      = static::make_wp_query( $query_args );

		if ( $query->have_posts() ) {
			echo self::render_product_search_results( $query, $types, $hierarchy ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			wp_reset_postdata();
		} else {
			// translators: %s is the search keyword
			echo wp_kses( '<ul><span>' . sprintf( esc_html__( 'No results found for "%s"', 'merchant' ), $keyword ) . '</span></ul>', array(
				'ul'   => array(),
				'span' => array(),
			) );
		}

		wp_die();
	}

	/**
	 * Parse product types from POST data.
	 *
	 * @since 2.2.5
	 *
	 * @return string[] Product type slugs.
	 */
	private static function get_product_search_types(): array {
		// Nonce verified in products_search() before calling this method.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['product_types'] ) && ! empty( $_POST['product_types'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			return explode( ',', sanitize_text_field( $_POST['product_types'] ) );
		}

		return array( 'simple', 'variable' );
	}

	/**
	 * Determine whether the search should show parent/child hierarchy.
	 *
	 * @since 2.2.5
	 *
	 * @param string[] $types Product types.
	 *
	 * @return bool
	 */
	private static function is_hierarchy_search( array $types ): bool {
		return in_array( 'all', $types, true )
			|| ( in_array( 'variation', $types, true ) && in_array( 'variable', $types, true ) );
	}

	/**
	 * Build WP_Query arguments for product search.
	 *
	 * @since 2.2.5
	 *
	 * @param string   $keyword   Search keyword.
	 * @param string[] $types     Product type slugs.
	 * @param string[] $added_ids Already-added product IDs to exclude.
	 *
	 * @return array WP_Query arguments.
	 */
	private static function build_product_search_query_args( string $keyword, array $types, array $added_ids ): array {
		if ( is_numeric( $keyword ) ) {
			$args = array(
				'p'         => absint( $keyword ),
				'post_type' => 'product',
			);
		} else {
			$args = array(
				'post_type'      => 'product',
				'post_status'    => array( 'publish', 'private' ),
				's'              => $keyword,
				'posts_per_page' => 10,
				'post__not_in'   => array_map( 'absint', $added_ids ),
			);

			if ( ! empty( $types ) && ! in_array( 'all', $types, true ) ) {
				$product_types = $types;
				if ( in_array( 'variation', $types, true ) ) {
					$product_types[] = 'variable';
				}
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				$args['tax_query'] = array(
					array(
						'taxonomy' => 'product_type',
						'field'    => 'slug',
						'terms'    => $product_types,
					),
				);
			}
		}

		return self::append_category_filter( $args );
	}

	/**
	 * Append a product_cat tax_query clause if categories are present in POST.
	 *
	 * @since 2.2.5
	 *
	 * @param array $args Existing WP_Query arguments.
	 *
	 * @return array Modified arguments.
	 */
	private static function append_category_filter( array $args ): array {
		// Nonce verified in products_search() before calling this method.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$categories = array_map( 'sanitize_text_field', $_POST['categories'] ?? array() );
		if ( is_array( $categories ) && ! empty( $categories ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => $categories,
				'operator' => 'IN',
			);
		}

		return $args;
	}

	/**
	 * Render product search results as HTML list items.
	 *
	 * @since 2.2.5
	 *
	 * @param WP_Query $query     The executed query.
	 * @param string[] $types     Product type slugs.
	 * @param bool     $hierarchy Whether to show hierarchy.
	 *
	 * @return string HTML output.
	 */
	private static function render_product_search_results( WP_Query $query, array $types, bool $hierarchy ): string {
		$html = '<ul>';

		while ( $query->have_posts() ) {
			$query->the_post();
			$_product = wc_get_product( get_the_ID() );

			if ( ! $_product ) {
				continue;
			}

			if ( ! $_product->is_type( 'variable' ) || in_array( 'variable', $types, true ) || in_array( 'all', $types, true ) ) {
				$html .= static::product_data_li( $_product, true, $hierarchy );
			}

			$html .= self::render_product_variations( $_product, $types, $hierarchy );
		}

		$html .= '</ul>';

		return $html;
	}

	/**
	 * Render child variations for a variable product.
	 *
	 * @since 2.2.5
	 *
	 * @param WC_Product $product   Parent product.
	 * @param string[]   $types     Product type slugs.
	 * @param bool       $hierarchy Whether to show hierarchy styling.
	 *
	 * @return string HTML list items for variations.
	 */
	private static function render_product_variations( $product, array $types, bool $hierarchy ): string {
		if ( ! $product->is_type( 'variable' ) ) {
			return '';
		}

		if ( ! empty( $types ) && ! in_array( 'all', $types, true ) && ! in_array( 'variation', $types, true ) ) {
			return '';
		}

		$children = $product->get_children();
		if ( ! is_array( $children ) || empty( $children ) ) {
			return '';
		}

		$html = '';
		foreach ( $children as $child_id ) {
			$child_product = wc_get_product( $child_id );
			/** @var WC_Product_Variation $child_product */
			if ( ! static::are_variation_attributes_set( $child_product ) ) {
				continue;
			}
			$html .= static::product_data_li( $child_product, true, $hierarchy );
		}

		return $html;
	}

	// ───────────────────────────────────────────────────────────────
	// Products selector (reviews)
	// ───────────────────────────────────────────────────────────────

	/**
	 * Search products with reviews and render results.
	 *
	 * @since 1.10.4
	 *
	 * @param string $term    Optional search term.
	 * @param int[]  $exclude Optional product IDs to exclude.
	 *
	 * @return string Rendered HTML.
	 */
	public static function products_selector_search_results( $term = '', $exclude = array() ) {
		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'any',
			'posts_per_page' => 20,
			'post__not_in'   => $exclude,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_key'       => '_wc_review_count',
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
		);

		if ( $term ) {
			$args['s'] = $term;
		}

		$query    = static::make_wc_product_query( $args );
		$products = $query->get_products();

		$response = '';
		foreach ( $products as $product ) {
			$response .= self::render_product_review_item( $product );
		}

		return $response;
	}

	/**
	 * Render a single product item in the reviews selector.
	 *
	 * @since 2.2.5
	 *
	 * @param WC_Product $product The product.
	 *
	 * @return string Rendered HTML.
	 */
	protected static function render_product_review_item( $product ): string {
		$reviews_count  = $product->get_review_count();
		$reviews_text   = _n( 'Review', 'Reviews', $reviews_count, 'merchant' );
		$has_reviews_cl = $reviews_count > 0 ? ' product-item-has-reviews' : '';
		$product_id     = $product->get_id();

		$html  = '<div class="product-item' . $has_reviews_cl . '" data-id="' . $product_id . '">';
		$html .= self::render_product_review_header( $product, $reviews_count, $reviews_text );

		if ( $reviews_count > 0 ) {
			$reviews           = self::get_rendered_product_reviews( $product );
			$rendered_reviews  = $reviews['reviews'] . $reviews['load_more'];
			$html             .= '<div class="product-reviews" data-id="' . esc_attr( $product_id ) . '">' . $rendered_reviews . '</div>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Render the header section of a product review item.
	 *
	 * @since 2.2.5
	 *
	 * @param WC_Product $product       The product.
	 * @param int        $reviews_count Number of reviews.
	 * @param string     $reviews_text  Localized review/reviews label.
	 *
	 * @return string Rendered HTML.
	 */
	protected static function render_product_review_header( $product, int $reviews_count, string $reviews_text ): string {
		$product_id = $product->get_id();
		$edit_link  = get_edit_post_link( $product_id );

		$html  = '<div class="header">';
		$html .= '<div class="product-image"><a href="' . $edit_link . '" target="_blank">'
				. get_the_post_thumbnail( $product_id, 'thumbnail' ) . '</a></div>';
		$html .= '<div class="product-title"><a href="' . $edit_link . '" target="_blank">' . $product->get_name() . '</a></div>';
		$html .= '<div class="spacer"></div>';
		$html .= '<div class="rating-stars">' . wc_get_rating_html( $product->get_average_rating(), $reviews_count ) . '</div>';

		if ( $reviews_count > 0 ) {
			$html .= '<div class="selected-reviews-count"><span class="counter">0</span>
<div class="tooltip">' . esc_attr__( 'Number of selected reviews', 'merchant' ) . '</div></div>';
		}

		$html .= '<div class="product-status product-status-' . esc_attr( $product->get_status() ) . '">' . esc_html( $product->get_status() ) . '</div>';
		$html .= '<div class="product-reviews-count">' . $reviews_count . ' ' . $reviews_text . '</div>';
		$html .= '<div class="product-expander"><svg width="12" height="13" viewBox="0 0 12 13" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.5 5.07422L6 8.57422L2.5 5.07422" stroke="#a6a4a4" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></div>';
		$html .= '</div>'; // .header

		return $html;
	}

	/**
	 * Get rendered HTML for a product's reviews with pagination.
	 *
	 * @since 1.10.4
	 *
	 * @param WC_Product $product The WooCommerce product object.
	 * @param int        $offset  Number of reviews to skip.
	 *
	 * @return array{reviews: string, load_more: string}
	 */
	public static function get_rendered_product_reviews( $product, $offset = 0 ) {
		$reviews_count = $product->get_review_count();
		if ( $reviews_count === 0 ) {
			return array( 'reviews' => '', 'load_more' => '' );
		}

		/** @since 1.10.4 */
		$reviews_needed = apply_filters( 'merchant_reviews_per_page', 10 );
		$reviews        = get_comments( array(
			'post_id' => $product->get_id(),
			'status'  => array( 'approve', 'spam', 'hold' ),
			'type'    => 'review',
			'number'  => $reviews_needed,
			'offset'  => $offset,
			'fields'  => 'ids',
		) );

		$response = '';
		if ( ! empty( $reviews ) ) {
			$response .= '<div class="reviews-wrapper">';
			foreach ( $reviews as $review ) {
				$response .= self::get_review( $review );
			}
			$response .= '</div>';
		}

		$load_more = self::render_load_more_button( $reviews_count, $reviews_needed, $offset );

		return array( 'reviews' => $response, 'load_more' => $load_more );
	}

	/**
	 * Render a "load more" button if additional reviews exist.
	 *
	 * @since 2.2.5
	 *
	 * @param int $total    Total review count.
	 * @param int $per_page Reviews per page.
	 * @param int $offset   Current offset.
	 *
	 * @return string HTML or empty string.
	 */
	private static function render_load_more_button( int $total, int $per_page, int $offset ): string {
		if ( $total <= $per_page || $offset + $per_page >= $total ) {
			return '';
		}

		return '<div class="product-reviews-load-more">'
			. '<button type="button">' . esc_html__( 'Load more', 'merchant' ) . '</button>'
			. '</div>';
	}

	// ───────────────────────────────────────────────────────────────
	// Single review rendering
	// ───────────────────────────────────────────────────────────────

	/**
	 * Render a single review comment as HTML.
	 *
	 * @since 1.10.4
	 *
	 * @param int $review_id The WooCommerce review (comment) ID.
	 *
	 * @return string Rendered review HTML.
	 */
	public static function get_review( $review_id ) {
		$review = get_comment( $review_id );

		if ( ! $review ) {
			/** @since 1.10.4 */
			return apply_filters( 'merchant_single_review_item_rendered', '', null );
		}

		$product = wc_get_product( $review->comment_post_ID );

		if ( ! $product ) {
			/** @since 1.10.4 */
			return apply_filters( 'merchant_single_review_item_rendered', '', $review );
		}

		$response  = self::render_review_header( $review, $product );
		$response .= self::render_review_body( $review, $product );
		$response .= self::render_review_actions( $review );
		$response .= '</div>';

		/** @since 1.10.4 */
		return apply_filters( 'merchant_single_review_item_rendered', $response, $review );
	}

	/**
	 * Render review header: checkbox + author + product image/name + rating.
	 *
	 * @since 2.2.5
	 *
	 * @param object     $review  Comment object.
	 * @param WC_Product $product Product object.
	 *
	 * @return string HTML.
	 */
	private static function render_review_header( $review, $product ): string {
		$edit_link = get_edit_post_link( $product->get_id() );
		$rating    = get_comment_meta( $review->comment_ID, 'rating', true );
		$status    = wp_get_comment_status( $review->comment_ID );

		$html  = '<div class="product-review" data-id="' . esc_attr( $review->comment_ID ) . '" data-product-id="' . esc_attr( $product->get_id() ) . '">';
		$html .= '<div class="product-review-add-checkbox"><input type="checkbox" class="review-checkbox" title="' . esc_html__( 'Add this review', 'merchant' ) . '"></div>';
		$html .= '<div class="product-review-author">' . esc_html( $review->comment_author ) . '</div>';
		$html .= '<div class="product-review-product-image"><a href="' . $edit_link . '" target="_blank">' . $product->get_image( 'thumbnail' ) . '</a></div>';
		$html .= '<div class="product-review-product-name"><a href="' . $edit_link . '" target="_blank">' . esc_html( $product->get_name() ) . '</a></div>';
		$html .= '<div class="product-review-rating">' . wc_get_rating_html( $rating ) . '</div>';
		$html .= '<div class="product-review-status"><div class="status status-' . esc_attr( $status ) . '">' . esc_html( $status ) . '</div></div>';

		return $html;
	}

	/**
	 * Render review body: content + photos + date + edit link.
	 *
	 * @since 2.2.5
	 *
	 * @param object     $review  Comment object.
	 * @param WC_Product $product Product object.
	 *
	 * @return string HTML.
	 */
	private static function render_review_body( $review, $product ): string {
		$html  = '<div class="product-review-content">' . esc_html( wp_trim_words( $review->comment_content, 8, '...' ) ) . '</div>';
		$html .= '<div class="spacer"></div>';
		$html .= '<div class="product-review-photos">'
			. '<div class="tooltip">' . esc_attr__( 'View customer\'s uploaded review photos.', 'merchant' ) . '</div>'
			. self::get_review_main_photo( $review->comment_ID )
			. '</div>';
		$html .= '<div class="product-review-date">' . esc_html( get_comment_date( 'j/n/Y', $review->comment_ID ) ) . '</div>';
		$html .= '<div class="product-review-edit"><a href="' . esc_url( get_edit_comment_link( $review->comment_ID ) ) . '" target="_blank">' . esc_html__( 'Edit', 'merchant' ) . '</a></div>';

		return $html;
	}

	/**
	 * Render review action buttons: delete + move.
	 *
	 * @since 2.2.5
	 *
	 * @param object $review Comment object.
	 *
	 * @return string HTML.
	 */
	private static function render_review_actions( $review ): string {
		$html  = '<button class="product-review-delete">
                            <svg width="80px" height="80px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M6 7V18C6 19.1046 6.89543 20 8 20H16C17.1046 20 18 19.1046 18 18V7M6 7H5M6 7H8M18 7H19M18 7H16M10 11V16M14 11V16M8 7V5C8 3.89543 8.89543 3 10 3H14C15.1046 3 16 3.89543 16 5V7M8 7H16" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
                        </button>';
		$html .= '<button class="product-review-move">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M12 3V21M12 3L9 6M12 3L15 6M12 21L15 18M12 21L9 18M3 12H21M3 12L6 15M3 12L6 9M21 12L18 9M21 12L18 15" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
                        </button>';

		return $html;
	}

	/**
	 * Get the primary photo thumbnail for a review.
	 *
	 * @since 1.10.4
	 *
	 * @param int $comment_id The review (comment) ID.
	 *
	 * @return string Rendered HTML or empty string.
	 */
	public static function get_review_main_photo( $comment_id ) {
		$photos_ids = get_comment_meta( $comment_id, 'review_images', true );
		if ( empty( $photos_ids ) ) {
			return '';
		}
		$response    = '';
		$first_image = wp_get_attachment_image_url( $photos_ids[0], array( 60, 60 ) );
		$counter     = '';
		if ( count( $photos_ids ) > 1 ) {
			$counter = '<span class="count">' . count( $photos_ids ) . '</span>';
		}
		$response .= '<div class="review-photo"><img src="' . esc_url( $first_image ) . '" alt="">' . $counter . '</div>';

		return $response;
	}

	// ───────────────────────────────────────────────────────────────
	// Product data list item
	// ───────────────────────────────────────────────────────────────

	/**
	 * Render a product as an `<li>` element for the product selector.
	 *
	 * @since 2.2.5
	 *
	 * @param WC_Product $product   The WooCommerce product object.
	 * @param bool|array $search    Whether this is a search result (true = "Add" button).
	 * @param bool       $hierarchy Whether to apply hierarchy indentation.
	 *
	 * @return string Rendered HTML.
	 */
	public static function product_data_li( $product, $search = false, $hierarchy = false ): string {
		$attrs = self::build_product_li_attributes( $product, $hierarchy );

		$html  = '<li ' . $attrs . '>';
		$html .= self::render_product_image_html( $product );
		$html .= self::render_product_data_html( $product );
		$html .= self::render_product_type_and_button( $product, $search );
		$html .= '</li>';

		return $html;
	}

	/**
	 * Build HTML attributes for a product list item.
	 *
	 * @since 2.2.5
	 *
	 * @param WC_Product $product   Product object.
	 * @param bool       $hierarchy Whether to show hierarchy.
	 *
	 * @return string Attribute string.
	 */
	private static function build_product_li_attributes( $product, bool $hierarchy ): string {
		$product_id   = $product->get_id();
		$product_sku  = $product->get_sku();
		$product_name = $product->get_name();
		$price        = $product->get_price();
		$key          = $product_id . '_' . $product_sku;

		$item_class = 'product-item';
		if ( $hierarchy && $product->is_type( 'variation' ) ) {
			$item_class .= ' hierarchy-style';
		}

		return 'class="' . esc_attr( $item_class ) . '"'
			. ' data-key="' . esc_attr( $key ) . '"'
			. ' data-name="' . esc_attr( $product_name ) . '"'
			. ' data-sku="' . esc_attr( $product_sku ) . '"'
			. ' data-id="' . esc_attr( $product_id ) . '"'
			. ' data-price="' . esc_attr( $price ) . '"';
	}

	/**
	 * Render the product image with wp_kses.
	 *
	 * @since 2.2.5
	 *
	 * @param WC_Product $product Product object.
	 *
	 * @return string Sanitized image HTML.
	 */
	private static function render_product_image_html( $product ): string {
		/** @since 1.9.1 */
		$product_image = apply_filters(
			'merchant_product_item_product_image',
			'<span class="img">' . $product->get_image( array( 30, 30 ) ) . '</span>',
			$product
		);

		return wp_kses( $product_image, array(
			'span' => array( 'class' => true ),
			'img'  => array(
				'src' => true, 'alt' => true, 'decoding' => true, 'srcset' => true,
				'loading' => true, 'sizes' => true, 'class' => true, 'width' => true, 'height' => true,
			),
		) );
	}

	/**
	 * Render product name, price, and sold-individually notice.
	 *
	 * @since 2.2.5
	 *
	 * @param WC_Product $product Product object.
	 *
	 * @return string HTML.
	 */
	private static function render_product_data_html( $product ): string {
		$price_html = wp_kses( $product->get_price_html(), array(
			'span' => array( 'class' => true ),
			'del'  => array( 'aria-hidden' => true ),
			'ins'  => array(),
			'bdi'  => array(),
		) );

		$sold_individually = $product->is_sold_individually()
			? '<span class="info">' . esc_html__( 'sold individually', 'merchant' ) . '</span> '
			: '';

		return '<span class="data">'
			. '<span class="name">' . esc_html( $product->get_name() ) . '</span>'
			. '<span class="info">' . $price_html . '</span> '
			. $sold_individually
			. '</span>';
	}

	/**
	 * Render product type badge and add/remove button.
	 *
	 * @since 2.2.5
	 *
	 * @param WC_Product $product Product object.
	 * @param bool       $search  True for "Add" button, false for "Remove".
	 *
	 * @return string HTML.
	 */
	private static function render_product_type_and_button( $product, bool $search ): string {
		$product_id = $product->get_id();
		$edit_link  = get_edit_post_link( $product_id );
		if ( $product->is_type( 'variation' ) ) {
			$edit_link = get_edit_post_link( $product->get_parent_id() );
		}

		/** @since 1.9.0 */
		$product_info = apply_filters( 'merchant_pro_product_bundle_item_product_info', $product->get_type() . '<br/>#' . $product_id, $product );

		$btn_label = $search
			? esc_html__( 'Add', 'merchant' )
			: esc_html__( 'Remove', 'merchant' );
		$btn_char  = $search ? '+' : '×';

		$remove_btn = '<span class="remove hint--left" aria-label="' . $btn_label . '">' . $btn_char . '</span>';

		return '<span class="type"><a href="' . esc_url( $edit_link ) . '" target="_blank">' . wp_kses_post( $product_info ) . '</a></span> '
			. wp_kses( $remove_btn, array( 'span' => array( 'class' => true, 'aria-label' => true ) ) );
	}

	// ───────────────────────────────────────────────────────────────
	// Variation helpers
	// ───────────────────────────────────────────────────────────────

	/**
	 * Check if all variation attributes are set for a product variation.
	 *
	 * @since 2.2.5
	 *
	 * @param WC_Product_Variation $variation The variation product object.
	 *
	 * @return bool True if all attributes are set, false otherwise.
	 */
	public static function are_variation_attributes_set( $variation ) {
		if ( $variation && $variation->is_type( 'variation' ) ) {
			$variation_attributes = $variation->get_variation_attributes();

			foreach ( $variation_attributes as $attribute => $value ) {
				if ( empty( $value ) ) {
					return false;
				}
			}

			return true;
		}

		return false;
	}
}

// Initialize.
new Merchant_Admin_Ajax();
