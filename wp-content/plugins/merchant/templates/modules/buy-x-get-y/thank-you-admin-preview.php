<?php
/**
 * Admin preview template for buy x get y module — thank you page section.
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

$payment_gateway = function_exists( 'merchant_get_first_active_payment_gateway_label' )
	? merchant_get_first_active_payment_gateway_label()
	: null;
?>
<div class="merchant-thank-you-preview">
	<div class="order-received">
		<div class="page-title"><?php esc_html_e( 'Order Received', 'merchant' ); ?></div>
		<p><?php esc_html_e( 'Thank you. Your order has been received.', 'merchant' ); ?></p>
		<div class="order-details">
			<div class="order-info">
				<div class="item-title"><?php esc_html_e( 'ORDER NUMBER:', 'merchant' ); ?></div>
				<p>550</p>
			</div>
			<div class="order-info">
				<div class="item-title"><?php esc_html_e( 'PAYMENT METHOD:', 'merchant' ); ?></div>
				<p><?php echo esc_html( $payment_gateway ?? 'Apple Pay' ); ?></p>
			</div>
		</div>
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
