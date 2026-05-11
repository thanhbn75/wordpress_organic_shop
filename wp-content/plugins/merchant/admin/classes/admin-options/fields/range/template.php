<?php
/**
 * Template: Range field.
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
	'min'  => '',
	'max'  => '',
	'step' => '',
	'unit' => '',
) );
?>
<div class="merchant-range">
	<input type="range" class="merchant-range-input" name="" min="<?php echo esc_attr( $settings['min'] ); ?>" max="<?php echo esc_attr( $settings['max'] ); ?>"
		step="<?php echo esc_attr( $settings['step'] ); ?>" value="<?php echo esc_attr( $value ); ?>"/>
	<input type="number" class="merchant-range-number-input" name="merchant[<?php echo esc_attr( $settings['id'] ); ?>]" min="<?php echo esc_attr( $settings['min'] ); ?>"
		max="<?php echo esc_attr( $settings['max'] ); ?>" step="<?php echo esc_attr( $settings['step'] ); ?>" value="<?php echo esc_attr( $value ); ?>"/>
	<?php if ( ! empty( $settings['unit'] ) ) : ?>
		<span class="merchant-range-unit"><?php echo esc_html( $settings['unit'] ); ?></span>
	<?php endif; ?>
</div>
