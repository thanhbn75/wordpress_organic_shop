<?php
/**
 * Template: Textarea Multiline field.
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

$value = ( $value ) ? $value : '';
?>
<textarea name="merchant[<?php echo esc_attr( $settings['id'] ); ?>]"><?php echo wp_kses_post( $value ); ?></textarea>
