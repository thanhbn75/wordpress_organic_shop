<?php
/**
 * Template: Info Block field.
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
?>
<div class="merchant-info-block">
	<i class="dashicons dashicons-info"></i>
	<p><?php echo ! empty( $settings['description'] ) ? esc_html( $settings['description'] ) : ''; ?><?php
		if ( ! empty( $settings['button_text'] ) && ! empty( $settings['button_link'] ) ) {
			printf( '<a href="%s" target="_blank">%s</a>', esc_url( $settings['button_link'] ), esc_html( $settings['button_text'] ) );
		} ?></p>
</div>
