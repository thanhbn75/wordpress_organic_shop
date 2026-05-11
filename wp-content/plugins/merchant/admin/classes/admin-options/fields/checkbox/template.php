<?php
/**
 * Template: Checkbox field.
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
<div>
	<label>
		<input type="checkbox" name="merchant[<?php echo esc_attr( $settings['id'] ); ?>]" value="1" <?php checked( $value, 1, true ); ?> />
		<?php if ( ! empty( $settings['label'] ) ) : ?>
			<span><?php echo esc_html( $settings['label'] ); ?></span>
		<?php endif; ?>
	</label>
</div>
