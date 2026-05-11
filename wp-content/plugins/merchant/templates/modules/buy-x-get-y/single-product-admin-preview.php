<?php
/**
 * Admin preview template for buy x get y module — single product section.
 *
 * Purely presentational: all logic (price calculations, label text, inline styles)
 * is pre-computed by Merchant_Buy_X_Get_Y::render_admin_preview() and passed via $args.
 *
 * @var array $args {
 *     Pre-computed template data.
 *
 *     @type string $title_text        Offer title text.
 *     @type string $buy_label_text    Buy label text.
 *     @type string $get_label_text    Get label text.
 *     @type string $button_text       Add to cart button text.
 *     @type string $buy_price_html    Formatted buy price HTML.
 *     @type string $get_price_html    Formatted original get price HTML.
 *     @type string $reduced_price_html Formatted discounted price HTML.
 *     @type string $title_style       Inline CSS for the title.
 *     @type string $label_style       Inline CSS for labels.
 *     @type string $arrow_style       Inline CSS for the arrow.
 *     @type string $offer_y_style     Inline CSS for the Get Y box.
 * }
 *
 * @since 2.2.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="merchant-bogo">
	<div class="merchant-bogo-offers">
		<p class="merchant-bogo-title" style="<?php echo esc_attr( $args['title_style'] ); ?>">
			<?php echo esc_html( $args['title_text'] ); ?>
		</p>

		<div class="merchant-bogo-offer">
			<!-- Buy X side -->
			<div class="merchant-bogo-product-x">
				<div class="merchant-bogo-product-label merchant-bogo-product-buy-label" style="<?php echo esc_attr( $args['label_style'] ); ?>">
					<?php echo esc_html( $args['buy_label_text'] ); ?>
				</div>
				<div class="merchant-bogo-product">
					<div class="merchant-bogo-product-image-placeholder"></div>
					<div class="merchant-bogo-product-contents">
						<p class="woocommerce-loop-product__title">
							<?php esc_html_e( 'Product Name', 'merchant' ); ?>
						</p>
						<span class="price"><?php echo $args['buy_price_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Pre-escaped by merchant_preview_price(). ?></span>
					</div>
				</div>
				<div class="merchant-bogo-arrow" style="<?php echo esc_attr( $args['arrow_style'] ); ?>">→</div>
			</div>

			<!-- Get Y side -->
			<div class="merchant-bogo-product-y" style="<?php echo esc_attr( $args['offer_y_style'] ); ?>">
				<div class="merchant-bogo-product-label merchant-bogo-product-get-label" style="<?php echo esc_attr( $args['label_style'] ); ?>">
					<?php echo esc_html( $args['get_label_text'] ); ?>
				</div>
				<div class="merchant-bogo-product">
					<div class="merchant-bogo-product-image-placeholder"></div>
					<div class="merchant-bogo-product-contents">
						<p class="woocommerce-loop-product__title">
							<?php esc_html_e( 'Product Name', 'merchant' ); ?>
						</p>
						<div class="merchant-bogo-product-price">
							<del><?php echo $args['get_price_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Pre-escaped. ?></del>
							<ins><?php echo $args['reduced_price_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Pre-escaped. ?></ins>
						</div>
					</div>
				</div>
				<button type="button" class="button alt wp-element-button merchant-bogo-add-to-cart">
					<?php echo esc_html( $args['button_text'] ); ?>
				</button>
			</div>
		</div>
	</div>
</div>
