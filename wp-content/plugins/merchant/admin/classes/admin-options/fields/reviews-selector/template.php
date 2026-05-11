<?php
/**
 * Template: Reviews Selector field.
 *
 * @var array<string, mixed>  $settings  Field configuration.
 * @var mixed  $value     Current saved value.
 * @var string $module_id Module ID.
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_array( $value ) ) {
	$value = array();
}
?>
<div class="merchant-reviews-selector" data-id="<?php echo esc_attr( $settings['id'] ); ?>">
	<div class="selected-reviews">
		<div class="product-reviews"><?php // keep this (no space) between the div and php tag to avoid white space issues with css
			foreach ( $value as $review ) {
				echo wp_kses( Merchant_Admin_Ajax::get_review( $review ), merchant_kses_allowed_tags( array( 'div' ) ) );
			} ?></div>
	</div>
	<button type="button" class="merchant-btn-control popup-trigger"><?php esc_html_e( 'Select Reviews', 'merchant' ); ?></button>
	<div class="overlay"></div>
	<div class="selector-popup">
		<div class="popup-content">
			<div class="popup-header">
				<label>
					<span>
						<?php esc_html_e( 'Search & filter', 'merchant' ); ?>
					</span>
					<input type="text" name="products_search" class="products-search" placeholder="<?php echo esc_attr__( 'Search for a product', 'merchant' ); ?>">
				</label>

				<span class="close" title="<?php esc_attr_e( 'Close', 'merchant' ); ?>">&times;</span>
			</div>
			<div class="popup-body merchant-reviews-selector-body">
				<div class="products-search-results">
					<?php
					echo wp_kses( Merchant_Admin_Ajax::products_selector_search_results(), merchant_kses_allowed_tags( array( 'div' ) ) ); ?>
				</div>
			</div>
			<div class="loader"></div>
		</div>
	</div>
	<input type="hidden" class="review-saved-ids" name="merchant[<?php echo esc_attr( $settings['id'] ); ?>]" value="<?php
	echo esc_attr( implode( ',', $value ) ) ?>">
</div>
<?php
