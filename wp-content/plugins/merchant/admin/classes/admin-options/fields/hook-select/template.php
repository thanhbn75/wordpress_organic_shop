<?php
/**
 * Template: Hook Select field.
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

$hook_name     = isset( $value['hook_name'] ) ? $value['hook_name'] : '';
$hook_priority = isset( $value['hook_priority'] ) ? $value['hook_priority'] : '';
?>
<div class="merchant-module-page-setting-field-hook_select-wrapper">
	<div class="merchant-module-page-setting-field-select">
		<select name="merchant[<?php echo esc_attr( $settings['id'] ); ?>][hook_name]">
			<?php if ( ! empty( $settings['options'] ) ) :
				foreach ( $settings['options'] as $key => $option ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $hook_name, $key, true ); ?>><?php echo esc_html( $option ); ?></option>
				<?php endforeach;
			endif; ?>
		</select>
	</div>
	<?php if ( isset( $settings['order'] ) && true === $settings['order'] ) : ?>
		<div class="merchant-module-page-setting-field-number">
			<input type="number" name="merchant[<?php echo esc_attr( $settings['id'] ); ?>][hook_priority]" value="<?php echo esc_attr( $hook_priority ); ?>"/>
		</div>
	<?php endif; ?>
</div>
<?php
