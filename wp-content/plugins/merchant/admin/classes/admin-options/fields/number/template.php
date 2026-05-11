<?php
/**
 * Template: Number field.
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
<input type="number" name="merchant[<?php
echo esc_attr( $settings['id'] ); ?>]" value="<?php
echo esc_attr( $value ); ?>"<?php
echo isset( $settings['step'] ) ? ' step="' . esc_attr( $settings['step'] ) . '"' : '';
echo isset( $settings['max'] ) ? ' max="' . esc_attr( $settings['max'] ) . '"' : '';
echo isset( $settings['min'] ) ? ' min="' . esc_attr( $settings['min'] ) . '"' : '' ?>
placeholder="<?php echo esc_attr( $settings['placeholder'] ?? '' ); ?>"/>
