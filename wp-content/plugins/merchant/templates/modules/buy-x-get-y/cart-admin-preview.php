<?php
/**
 * Admin preview template for buy x get y module — cart page section.
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
<div class="merchant-cart-preview">
	<div class="my-cart">
		<div class="cart-title"><?php esc_html_e( 'My Cart', 'merchant' ); ?></div>
		<table class="cart-table">
			<tr>
				<th class="product-col"><?php esc_html_e( 'PRODUCT', 'merchant' ); ?></th>
				<th class="price-col"><?php esc_html_e( 'PRICE', 'merchant' ); ?></th>
				<th class="quantity-col"><?php esc_html_e( 'QUANTITY', 'merchant' ); ?></th>
				<th class="total-col"><?php esc_html_e( 'TOTAL', 'merchant' ); ?></th>
			</tr>
			<tr class="cart-item">
				<td class="product-column">
					<div class="product">
						<div class="product-image"></div>
						<div class="product-info">
							<div class="product-name"><?php esc_html_e( 'Your Product Name', 'merchant' ); ?></div>
							<p class="upsell-offer"><?php esc_html_e( 'You are eligible to get {offer_quantity}', 'merchant' ); ?></p>
							<div class="upsell-product">
								<div class="upsell-image"></div>
								<div class="upsell-info">
									<div class="upsell-name"><?php esc_html_e( 'Product Name', 'merchant' ); ?></div>
									<p><?php esc_html_e( 'with {discount} off', 'merchant' ); ?></p>
									<button class="add-to-cart"><?php esc_html_e( 'Add To Cart', 'merchant' ); ?></button>
								</div>
							</div>
						</div>
					</div>
				</td>
				<td class="price-col">
					<span class="original-price"><?php echo merchant_preview_price( 16 ); // phpcs:ignore ?></span>
					<span class="discounted-price"><?php echo merchant_preview_price( 12 ); // phpcs:ignore ?></span>
				</td>
				<td class="quantity-col">
					<div class="quantity-control">
						<button class="decrease">-</button>
						<input type="text" value="1" min="1">
						<button class="increase">+</button>
					</div>
				</td>
				<td class="total-col"><?php echo merchant_preview_price( 300 ); // phpcs:ignore ?></td>
			</tr>
		</table>
	</div>
</div>
