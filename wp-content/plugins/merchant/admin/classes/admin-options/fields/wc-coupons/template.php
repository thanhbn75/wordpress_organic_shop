<?php
/**
 * Template: Wc Coupons field.
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

$coupons = get_posts( array(
	'posts_per_page' => - 1,
	'orderby'        => 'title',
	'order'          => 'asc',
	'post_type'      => 'shop_coupon',
	'post_status'    => 'publish',
) );

if ( $coupons ) {
	?>
	<div class="merchant-wc-coupon-selector merchant-<?php echo esc_attr( $settings['id'] ) ?>">
		<?php
		echo '<select name="merchant[' . esc_attr( $settings['id'] ) . ']">';
		echo '<option value="">' . esc_html__( 'Select a coupon', 'merchant' ) . '</option>';

		foreach ( $coupons as $coupon ) {
			$coupon_code = strtolower( $coupon->post_title );
			echo '<option value="' . esc_attr( $coupon_code ) . '"' . selected( esc_attr( $coupon_code ), $value, false ) . '>';
			echo esc_html( $coupon_code );
			echo '</option>';
		}

		echo '</select>';
		echo '<a href="' . esc_url( admin_url( 'edit.php?post_type=shop_coupon' ) ) . '" target="_blank" style="margin-left: 10px;">' . esc_html__( 'Manage coupons', 'merchant' ) . '</a>';
		?>
	</div>
	<?php
} else {
	echo '<div style="color: red;">';
	echo wp_kses_post(
		sprintf(
		/* Translators: 1. Coupon admin url 2. Link target attribute value */
			__( 'No coupons found! <a href="%1$s" target="%2$s">Create a new coupon</a>', 'merchant' ),
			admin_url( 'post-new.php?post_type=shop_coupon' ),
			'_blank'
		)
	);
	echo '</div>';
	echo '<input type="hidden" name="merchant[' . esc_attr( $settings['id'] ) . ']" value="" />';
}
