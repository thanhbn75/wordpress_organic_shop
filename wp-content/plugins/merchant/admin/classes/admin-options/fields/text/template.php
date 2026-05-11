<?php
/**
 * Template: Text field.
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
<input type="text" name="merchant[<?php
echo esc_attr( $settings['id'] ); ?>]" value="<?php
echo esc_attr( $value ); ?>" placeholder="<?php
echo esc_attr( $settings['placeholder'] ?? '' ); ?>"/>
