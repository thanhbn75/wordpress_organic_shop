<?php
/**
 * Template: Color field.
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

$settings = wp_parse_args( $settings, array(
	'default' => '#212121',
) );
?>
<div class="merchant-color">
	<div class="merchant-color-picker" data-default-color="<?php echo esc_attr( $settings['default'] ); ?>" style="background-color: <?php echo esc_attr( $value ); ?>;"></div>
	<input type="text" class="merchant-color-input" name="merchant[<?php echo esc_attr( $settings['id'] ); ?>]" value="<?php echo esc_attr( $value ); ?>"/>
</div>
