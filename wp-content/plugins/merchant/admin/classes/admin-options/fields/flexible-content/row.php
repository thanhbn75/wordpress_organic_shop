<?php
/**
 * Template: Flexible Content — single row.
 *
 * Used by both the live rows and the hidden JS-clone templates.
 *
 * @var array<string, mixed>  $settings      Field configuration.
 * @var string $layout        Layout type key.
 * @var array<string, mixed>  $layout_config Layout configuration (title, fields, etc.).
 * @var int    $option_key    Row index.
 * @var array<string, mixed>  $option        Row values.
 * @var bool   $has_sorting   Whether sorting is enabled.
 * @var bool   $has_accordion Whether accordion is enabled.
 * @var bool   $is_clone      Whether this is a clone template (uses data-name instead of name).
 * @var bool   $deferred      Whether this row should use deferred rendering (lazy load).
 * @var string $module_id     Module ID.
 * @var Merchant_Field_Flexible_Content $field The field instance.
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$deferred    = ! empty( $deferred );
$title_field = $layout_config['title-field'] ?? '';
$row_title   = $is_clone ? ( $layout_config['title'] ?? '' ) : ( $option[ $title_field ] ?? '' );
$count       = $is_clone ? 1 : absint( $option_key + 1 );

$name_attr   = $is_clone ? 'data-name' : 'name';
?>
<div class="layout" data-type="<?php echo esc_attr( $layout ) ?>"<?php
	if ( ! $is_clone && ! empty( $option['flexible_id'] ) ) {
		echo ' data-layout-id="' . esc_attr( $option['flexible_id'] ) . '"';
	}
	if ( $deferred ) {
		echo ' data-deferred="1"';
		$deferred_data = $field->build_deferred_data( $layout_config['fields'] ?? array(), $option );
		$json = wp_json_encode( $deferred_data );
		echo ' data-fields-json="' . esc_attr( $json ? $json : '{}' ) . '"';
	}
?>>
	<div class="layout__inner">
		<?php if ( $has_sorting ) : ?>
			<span class="customize-control-flexible-content-move">
				<svg xmlns="http://www.w3.org/2000/svg" width="10" height="14" viewBox="0 0 10 14" fill="none">
				<path d="M1.75 0.5C1.19772 0.5 0.75 0.947715 0.75 1.5V2.5C0.75 3.05228 1.19772 3.5 1.75 3.5H2.75C3.30228 3.5 3.75 3.05228 3.75 2.5V1.5C3.75 0.947715 3.30228 0.5 2.75 0.5H1.75Z" fill="#4A4A4A"/>
				<path d="M1.75 5.5C1.19772 5.5 0.75 5.94772 0.75 6.5V7.5C0.75 8.05228 1.19772 8.5 1.75 8.5H2.75C3.30228 8.5 3.75 8.05228 3.75 7.5V6.5C3.75 5.94772 3.30228 5.5 2.75 5.5H1.75Z" fill="#4A4A4A"/>
				<path d="M0.75 11.5C0.75 10.9477 1.19772 10.5 1.75 10.5H2.75C3.30228 10.5 3.75 10.9477 3.75 11.5V12.5C3.75 13.0523 3.30228 13.5 2.75 13.5H1.75C1.19772 13.5 0.75 13.0523 0.75 12.5V11.5Z" fill="#4A4A4A"/>
				<path d="M7.25 0.5C6.69772 0.5 6.25 0.947715 6.25 1.5V2.5C6.25 3.05228 6.69772 3.5 7.25 3.5H8.25C8.80228 3.5 9.25 3.05228 9.25 2.5V1.5C9.25 0.947715 8.80228 0.5 8.25 0.5H7.25Z" fill="#4A4A4A"/>
				<path d="M6.25 6.5C6.25 5.94772 6.69772 5.5 7.25 5.5H8.25C8.80228 5.5 9.25 5.94772 9.25 6.5V7.5C9.25 8.05228 8.80228 8.5 8.25 8.5H7.25C6.69772 8.5 6.25 8.05228 6.25 7.5V6.5Z" fill="#4A4A4A"/>
				<path d="M7.25 10.5C6.69772 10.5 6.25 10.9477 6.25 11.5V12.5C6.25 13.0523 6.69772 13.5 7.25 13.5H8.25C8.80228 13.5 9.25 13.0523 9.25 12.5V11.5C9.25 10.9477 8.80228 10.5 8.25 10.5H7.25Z" fill="#4A4A4A"/>
				</svg>
			</span>
		<?php endif; ?>
		<div class="layout-header">
			<div class="layout-count"><?php echo absint( $count ) ?></div>
			<div class="layout-title"<?php
			if ( ! empty( $title_field ) ) {
				echo ' data-title-field="' . esc_attr( $title_field ) . '"';
			} ?>>
				<?php echo esc_html( $row_title ); ?>
			</div>
			<div class="layout-toggle">
				<?php if ( $has_accordion ) : ?>
					<span class="customize-control-flexible-content-accordion">
						<svg xmlns="http://www.w3.org/2000/svg" width="10" height="7" viewBox="0 0 10 7" fill="none">
							<path fill-rule="evenodd" clip-rule="evenodd" d="M0.71967 0.732854C1.01256 0.43996 1.48744 0.43996 1.78033 0.732854L5.25 4.20252L8.71967 0.732854C9.01256 0.43996 9.48744 0.43996 9.78033 0.732854C10.0732 1.02575 10.0732 1.50062 9.78033 1.79351L5.78033 5.79351C5.48744 6.08641 5.01256 6.08641 4.71967 5.79351L0.71967 1.79351C0.426777 1.50062 0.426777 1.02575 0.71967 0.732854Z" fill="#4A4A4A"/>
						</svg>
					</span>
				<?php endif; ?>
			</div>
		</div>
		<div class="layout-body">
			<?php if ( ! $deferred ) : ?>
				<?php
				foreach ( $layout_config['fields'] as $sub_field ) :
					$classes = array( 'layout-field' );
					if ( isset( $sub_field['classes'] ) ) {
						$classes = array_merge( $classes, $sub_field['classes'] );
					} ?>
					<div class="<?php echo esc_attr( implode( ' ', $classes ) ) ?>">
						<?php
						if ( $is_clone ) {
							// Clone template: use defaults and data-name attributes.
							if ( 'fields_group' === $sub_field['type'] ) {
								Merchant_Field_Fields_Group::render_group( $sub_field, $option, $module_id, true, array(
									'id'         => $settings['id'],
									'option_key' => 0,
									'value'      => $option,
								) );
							} else {
								Merchant_Field_Flexible_Content::render_sub_field(
									$sub_field,
									$sub_field['default'] ?? '',
									array(
										"name=\"merchant[{$sub_field['id']}]",
										'merchant-module-page-setting-field-upload',
										'merchant-module-page-setting-field-select_ajax',
									),
									array(
										"data-name=\"merchant[{$settings['id']}][0][{$sub_field['id']}]",
										'merchant-module-page-setting-field-upload template',
										'merchant-module-page-setting-field-select_ajax template',
									),
									$module_id
								);
							}
						} else {
							// Live row: use actual values and name attributes.
							$sub_value = null;
							if ( isset( $option[ $sub_field['id'] ] ) ) {
								$sub_value = $option[ $sub_field['id'] ];
							} elseif ( isset( $sub_field['type'] ) && $sub_field['type'] === 'switcher' ) {
								$sub_value = 0;
							} elseif ( isset( $sub_field['default'] ) ) {
								$sub_value = $sub_field['default'];
							}

							if ( 'fields_group' === $sub_field['type'] ) {
								Merchant_Field_Fields_Group::render_group( $sub_field, $sub_value, $module_id, true, array(
									'id'         => $settings['id'],
									'option_key' => $option_key,
									'value'      => $option,
								) );
							} else {
								Merchant_Field_Flexible_Content::render_sub_field(
									$sub_field,
									$sub_value,
									"name=\"merchant[{$sub_field['id']}]",
									"name=\"merchant[{$settings['id']}][{$option_key}][{$sub_field['id']}]",
									$module_id
								);
							}
						}
						?>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
			<input type="hidden" <?php echo esc_attr( $name_attr ); ?>="merchant[<?php echo esc_attr( $settings['id'] ) ?>][<?php echo absint( $option_key ) ?>][layout]"
				value="<?php echo esc_attr( $layout ) ?>">
			<input type="hidden" class="flexible-id" <?php echo esc_attr( $name_attr ); ?>="merchant[<?php echo esc_attr( $settings['id'] ) ?>][<?php echo absint( $option_key ) ?>][flexible_id]" value="<?php
			echo isset( $option['flexible_id'] ) ? esc_attr( $option['flexible_id'] ) : '' ?>">
		</div>
		<?php
		$field->get_template_part( 'actions', array(
			'layout_type'   => $layout,
			'has_duplicate' => ! empty( $settings['duplicate'] ),
			'has_accordion' => ! empty( $settings['accordion'] ),
		) );
		?>
	</div>
</div>
