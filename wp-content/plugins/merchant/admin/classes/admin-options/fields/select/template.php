<?php
/**
 * Template: Select field.
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

if ( ! empty( $settings['options'] ) ) : ?>
	<select name="merchant[<?php
	echo esc_attr( $settings['id'] ); ?>]">
		<?php
		foreach ( $settings['options'] as $key => $option ) : ?>
			<option value="<?php
			echo esc_attr( $key ); ?>" <?php
			selected( $value, $key, true ); ?>><?php
				echo esc_html( $option ); ?></option>
		<?php
		endforeach; ?>
	</select>
<?php
endif;