<?php
/**
 * Template: Sortable Repeater field.
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
<div class="merchant-sortable-repeater-control<?php echo isset( $settings['sorting'] ) && false === $settings['sorting'] ? ' disable-sorting' : ''; ?>">
	<div class="merchant-sortable-repeater sortable regular-field">
		<div class="repeater">
			<input type="text" value="" class="repeater-input"/><span class="dashicons dashicons-menu"></span><a class="customize-control-sortable-repeater-delete"
				href="#"><span class="dashicons dashicons-no-alt"></span></a>
		</div>
	</div>
	<button class="button customize-control-sortable-repeater-add" type="button"><?php echo esc_html( $settings['button_label'] ); ?></button>
	<input class="merchant-sortable-repeater-input" type="hidden" name="merchant[<?php echo esc_attr( $settings['id'] ); ?>]" value="<?php echo esc_attr( (string) wp_json_encode( $value ) ); ?>"/>
</div>
<?php
