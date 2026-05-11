<?php
/**
 * Template: Fields Group — group wrapper.
 *
 * @var array<string, mixed>  $settings          Field settings.
 * @var mixed  $value             Field value.
 * @var string $module_id         Module ID.
 * @var bool   $inside_flexible   Whether group is inside flexible_content.
 * @var array<string, mixed>  $args              Extra args when inside flexible_content.
 * @var bool   $control_field_status Whether status dropdown is enabled.
 * @var bool   $accordion         Whether accordion is enabled.
 * @var bool   $state             Whether accordion is open.
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="merchant-group-field<?php
echo $accordion ? ' has-accordion' : '';
echo $control_field_status ? ' has-flag' : '';
echo $state ? ' open' : '';
echo ' merchant-group-field-' . esc_attr( $settings['id'] ) ?>" data-id="<?php
echo esc_attr( $settings['id'] ) ?>">
	<div class="title-area<?php
	echo $accordion ? ' accordion-style' : '' ?>">
		<?php
		if ( ! empty( $settings['title'] ) ) {
			printf( '<div class="merchant-module-page-setting-field-title">%s<span class="field-status hidden"></span></div>', esc_html( $settings['title'] ) );
		}
		if ( ! empty( $settings['sub-desc'] ) ) {
			printf( '<div class="merchant-module-page-setting-field-desc">%s</div>', wp_kses_post( $settings['sub-desc'] ) );
		}
		if ( $accordion ) {
			?>
			<span class="accordion-icon dashicons dashicons-arrow-down-alt2"></span>
			<?php
		}
		?>
	</div>
	<div class="merchant-group-fields-container">
		<?php
		if ( $control_field_status ) {
			include __DIR__ . '/status.php';
		}

		foreach ( $settings['fields'] as $field ) {
			if ( $inside_flexible ) {
				Merchant_Field_Fields_Group::render_sub_field(
					$field,
					$args['value'][ $settings['id'] ][ $field['id'] ] ?? ( $field['default'] ?? '' ),
					"name=\"merchant[{$field['id']}]",
					"name=\"merchant[{$args['id']}][{$args['option_key']}][{$settings['id']}][{$field['id']}]\"  data-name=\"merchant[{$args['id']}][0][{$settings['id']}][{$field['id']}]",
					$module_id
				);
			} else {
				Merchant_Field_Fields_Group::render_sub_field(
					$field,
					isset( $value[ $field['id'] ] ) ? $value[ $field['id'] ] : ( $field['default'] ?? '' ),
					"name=\"merchant[{$field['id']}]",
					"name=\"merchant[{$settings['id']}][{$field['id']}]",
					$module_id
				);
			}
		} ?>
	</div>
</div>
