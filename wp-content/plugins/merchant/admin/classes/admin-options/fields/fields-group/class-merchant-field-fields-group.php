<?php
/**
 * Merchant Field: Fields Group
 *
 * A container field that groups multiple sub-fields together, with optional
 * accordion display, status flag, and support for nesting inside flexible_content.
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Fields_Group
 *
 * Renders a collapsible group of nested sub-fields.
 *
 * @since 2.2.5
 */
class Merchant_Field_Fields_Group extends Merchant_Abstract_Field {

	/**
	 * Render the fields group (standard usage, not inside flexible_content).
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$control_field_status = ! empty( $this->field['display_status'] ) && $this->field['display_status'] === true;
		$accordion            = ! empty( $this->field['accordion'] ) && $this->field['accordion'] === true;
		$state                = ! empty( $this->field['state'] ) && $this->field['state'] === 'open';

		$this->get_template_part( 'template', array(
			'accordion'            => $accordion,
			'control_field_status' => $control_field_status,
			'state'                => $state,
			'inside_flexible'      => false,
			'args'                 => array(),
		) );
	}

	/**
	 * Render the fields group.
	 *
	 * Public static so flexible_content can call it with extra parameters
	 * for nested usage inside layouts.
	 *
	 * @since 2.2.5
	 *
	 * @param array<string, mixed>  $settings         Field settings.
	 * @param mixed  $value            Field value.
	 * @param string $module_id        Module ID.
	 * @param bool   $inside_flexible  Whether this group is inside a flexible_content layout.
	 * @param array<string, mixed>  $args             Extra arguments when inside flexible_content.
	 *
	 * @return void
	 */
	public static function render_group( $settings, $value, $module_id = '', $inside_flexible = false, $args = array() ) {
		$control_field_status = ! empty( $settings['display_status'] ) && $settings['display_status'] === true;
		$accordion            = ! empty( $settings['accordion'] ) && $settings['accordion'] === true;
		$state                = ! empty( $settings['state'] ) && $settings['state'] === 'open';

		include __DIR__ . '/template.php';
	}

	/**
	 * Type-specific sanitization for the fields group.
	 *
	 * Fields group values are sanitized per-sub-field during save_options,
	 * so this method returns the value as-is.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	protected function sanitize_value( $value ) {
		if ( ! is_array( $value ) || empty( $this->field['fields'] ) ) {
			return $value;
		}

		$registry  = Merchant_Field_Registry::instance();
		$sanitized = array();

		foreach ( $this->field['fields'] as $sub_field ) {
			if ( ! isset( $sub_field['id'] ) ) {
				continue;
			}

			$sub_id    = $sub_field['id'];
			$sub_value = $value[ $sub_id ] ?? ( $sub_field['default'] ?? null );
			$sub_type  = $sub_field['type'] ?? 'text';

			if ( $registry->has( $sub_type ) ) {
				$instance = $registry->create( $sub_type, $sub_field, $sub_value );
				if ( $instance !== null ) {
					$sub_value = $instance->preprocess( $sub_value );
					$sub_value = $instance->sanitize( $sub_value );
				}
			} else {
				$sub_value = sanitize_text_field( $sub_value );
			}

			$sanitized[ $sub_id ] = $sub_value;
		}

		// Preserve the status sub-field if present (injected by render_group).
		$status_id = $this->field['id'] . '_status';
		if ( isset( $value[ $status_id ] ) ) {
			$sanitized[ $status_id ] = sanitize_text_field( $value[ $status_id ] );
		}

		return $sanitized;
	}
}
