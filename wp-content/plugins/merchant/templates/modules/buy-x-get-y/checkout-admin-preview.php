<?php
/**
 * Admin preview template for buy x get y module — checkout page section.
 *
 * Purely presentational.
 *
 * @var array $args Pre-computed template data.
 *
 * @since 2.2.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="merchant-checkout-preview">
	<div class="order-received">
		<div class="page-title"><?php esc_html_e( 'Checkout', 'merchant' ); ?></div>
		<br>
		<div class="upsell-offer">
			<div class="offer-title"><?php esc_html_e( 'Last chance to get {offer_quantity} x', 'merchant' ); ?></div>
			<div class="product-details">
				<div class="product-image"></div>
				<div class="product-info">
					<div class="product-name"><?php esc_html_e( 'Your Product Name', 'merchant' ); ?></div>
					<p><?php esc_html_e( 'with {discount} off', 'merchant' ); ?></p>
					<button class="add-to-order"><?php esc_html_e( 'Add To My Order', 'merchant' ); ?></button>
				</div>
			</div>
		</div>
	</div>
</div>
