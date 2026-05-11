<?php
/**
 * Template: Checkbox Multiple field.
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
		<label>
			<input
				type="checkbox" name="merchant[<?php echo esc_attr( $settings['id'] ); ?>][]"
				value="<?php echo esc_attr( $key ); ?>"
				<?php checked( in_array( $key, $value, true ), true ); ?>
			/>
			<span><?php echo esc_html( $option ); ?></span>
		</label>
	<?php
	endforeach;
endif;
