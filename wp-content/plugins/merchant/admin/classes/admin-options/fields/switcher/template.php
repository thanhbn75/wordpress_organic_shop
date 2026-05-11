<?php
/**
 * Template: Switcher field.
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
<div class="merchant-toggle-switch">
	<input type="checkbox" id="<?php echo esc_attr( $settings['id'] ); ?>" name="merchant[<?php echo esc_attr( $settings['id'] ); ?>]" value="1" <?php checked( $value, 1, true ); ?>
		class="toggle-switch-checkbox"/>
	<label class="toggle-switch-label" for="<?php echo esc_attr( $settings['id'] ); ?>">
		<span class="toggle-switch-inner"></span>
		<span class="toggle-switch-switch"></span>
	</label>
	<?php if ( ! empty( $settings['label'] ) ) : ?>
		<span><?php echo esc_html( $settings['label'] ); ?></span>
	<?php endif; ?>
</div>
