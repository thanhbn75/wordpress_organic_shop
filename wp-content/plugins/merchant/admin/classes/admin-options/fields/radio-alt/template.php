<?php
/**
 * Template: Radio Alt field.
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

if ( ! empty( $settings['options'] ) ) :
	foreach ( $settings['options'] as $key => $option ) : ?>
		<div>
			<label>
				<input type="radio" name="merchant[<?php echo esc_attr( $settings['id'] ); ?>]" value="<?php echo esc_attr( $key ); ?>" <?php checked( $value, $key, true ); ?>/>
				<span><?php echo esc_html( $option['title'] ); ?></span>
			</label>
			<p><?php echo esc_html( $option['desc'] ); ?></p>
		</div>
	<?php
	endforeach;
endif;
