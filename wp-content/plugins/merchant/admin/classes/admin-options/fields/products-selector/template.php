<?php
/**
 * Template: Products Selector field.
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

if ( ! class_exists( 'WooCommerce' ) ) {
	echo '<p class="merchant-notice">' . esc_html__( 'WooCommerce is not installed or activated.', 'merchant' ) . '</p>';
	return;
}

$ids = $value ? explode( ',', $value ) : array();

if ( isset( $settings['multiple'] ) ) {
	$multiple = $settings['multiple'] === true ? 'multiple' : 'false';
} else {
	$multiple = 'multiple';
}

if ( ! isset( $settings['allowed_types'] ) ) {
	$settings['allowed_types'] = array( 'all' );
}
?>
<div class="merchant-products-search-container" data-multiple="<?php echo esc_attr( $multiple ); ?>">
	<div class="merchant-search-area">
		<input type="text" name="merchant-search-field" data-allowed-types="<?php
		echo esc_attr( implode( ',', $settings['allowed_types'] ) ) ?>" placeholder="<?php
		esc_attr_e( 'Search products', 'merchant' ); ?>" class="merchant-search-field">
		<span class="merchant-searching"><?php
			esc_html_e( 'Searching...', 'merchant' ); ?></span>
		<img src="<?php echo esc_url( MERCHANT_URI . 'assets/images/admin/products-search-icon.svg' ); ?>" class="merchant-search-icon"
			alt="<?php esc_attr_e( 'Search icon', 'merchant' ); ?>">
		<div class="merchant-selections-products-preview"></div>
	</div>
	<div class="merchant-selected-products-preview">
		<ul>
			<?php
			if ( ! empty( $ids ) ) {
				foreach ( $ids as $product_id ) {
					$product = wc_get_product( $product_id );
					if ( $product ) {
						echo Merchant_Admin_Ajax::product_data_li( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
				}
			}
			?>
		</ul>
	</div>
	<input type="hidden" name="merchant[<?php echo esc_attr( $settings['id'] ); ?>]" class="merchant-selected-products" value="<?php echo esc_attr( $value ) ?>">
</div>
<?php
