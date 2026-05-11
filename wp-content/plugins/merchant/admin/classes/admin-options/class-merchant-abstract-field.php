<?php
/**
 * Merchant Abstract Field.
 *
 * Provides shared functionality for all field types including
 * wrapper rendering, value retrieval, and default sanitize/preprocess.
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Merchant_Abstract_Field
 *
 * @since 2.2.5
 */
abstract class Merchant_Abstract_Field implements Merchant_Field_Interface {

	/**
	 * Field configuration array.
	 *
	 * @since 2.2.5
	 * @var array<string, mixed>
	 */
	protected $field;

	/**
	 * The current saved value.
	 *
	 * @since 2.2.5
	 * @var mixed
	 */
	protected $value;

	/**
	 * The module ID.
	 *
	 * @since 2.2.5
	 * @var string
	 */
	protected $module_id;

	/**
	 * Field ID.
	 *
	 * @since 2.2.5
	 * @var string
	 */
	protected $id;

	/**
	 * Field type.
	 *
	 * @since 2.2.5
	 * @var string
	 */
	protected $type;



	/**
	 * Constructor.
	 *
	 * @since 2.2.5
	 *
	 * @param array<string, mixed> $field     The field configuration array.
	 * @param mixed                $value     The current saved value.
	 * @param string               $module_id The module ID.
	 */
	public function __construct( $field, $value = null, $module_id = '' ) {
		$this->field     = $field;
		$this->value     = $value;
		$this->module_id = $module_id;
		$this->id        = isset( $field['id'] ) ? $field['id'] : '';
		$this->type      = isset( $field['type'] ) ? $field['type'] : '';
	}

	/**
	 * Sanitize the submitted field value.
	 *
	 * Checks for a custom sanitize callback first ($field['sanitize']),
	 * then falls back to the type-specific implementation.
	 * Subclasses should override sanitize_value() for type-specific logic.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	public function sanitize( $value ) {
		// Custom sanitize callback takes priority.
		if ( ! empty( $this->field['sanitize'] ) && is_callable( $this->field['sanitize'] ) ) {
			return call_user_func( $this->field['sanitize'], $value );
		}

		return $this->sanitize_value( $value );
	}

	/**
	 * Type-specific sanitization.
	 *
	 * Subclasses should override this method for their type-specific sanitization logic.
	 * Default implementation uses sanitize_text_field().
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	protected function sanitize_value( $value ) {
		return sanitize_text_field( $value );
	}

	/**
	 * Preprocess the field value before saving.
	 *
	 * Default implementation is a passthrough.
	 * Subclasses can override for complex preprocessing (e.g., flexible_content, sortable_repeater).
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The sanitized value.
	 *
	 * @return mixed The preprocessed value.
	 */
	public function preprocess( $value ) {
		return $value;
	}

	/**
	 * Get the field configuration array.
	 *
	 * @since 2.2.5
	 *
	 * @return array<string, mixed>
	 */
	public function get_field() {
		return $this->field;
	}

	/**
	 * Get the field value.
	 *
	 * @since 2.2.5
	 *
	 * @return mixed
	 */
	public function get_value() {
		return $this->value;
	}

	/**
	 * Get the field ID.
	 *
	 * @since 2.2.5
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get the field type.
	 *
	 * @since 2.2.5
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Return the base directory that contains this field's template files.
	 *
	 * Override this method (not a property) in a subclass to point to your own
	 * plugin directory. Using a method allows dynamic expressions such as
	 * plugin_dir_path( __FILE__ ), which are illegal in property initializers.
	 *
	 * Example override:
	 *   protected function get_template_dir(): string {
	 *       return plugin_dir_path( __FILE__ ) . 'fields/';
	 *   }
	 *
	 * @since 2.2.5
	 *
	 * @return string Absolute path with trailing slash.
	 */
	protected function get_template_dir(): string {
		return MERCHANT_DIR . 'admin/classes/admin-options/fields/';
	}

	/**
	 * Return the subfolder name for this field's templates.
	 *
	 * Defaults to the field type with underscores replaced by hyphens.
	 * Override this method in a subclass if your folder name doesn't
	 * match the convention (e.g., the type is 'my_custom' but the folder
	 * is 'custom-v2').
	 *
	 * @since 2.2.5
	 *
	 * @return string Folder name (no slashes).
	 */
	protected function get_template_folder(): string {
		return str_replace( '_', '-', $this->type );
	}

	/**
	 * Load a field template from the field's own folder.
	 *
	 * Templates are located beside the class file:
	 *   admin/classes/admin-options/fields/{type-slug}/{name}.php
	 *
	 * Available variables inside the template:
	 *   $settings  — Field configuration array.
	 *   $value     — Current saved value.
	 *   $module_id — Module ID string.
	 *   $field     — The field instance.
	 *   + any keys passed via $extra_args.
	 *
	 * @since 2.2.5
	 *
	 * @param string               $name       Template name (without .php). Default: 'template'.
	 * @param array<string, mixed> $extra_args Additional variables to pass to the template.
	 *
	 * @return void
	 */
	public function get_template_part( $name = 'template', $extra_args = array() ) {
		$dir           = trailingslashit( $this->get_template_dir() );
		$folder        = trim( $this->get_template_folder(), '/' );
		$template_path = $dir . $folder . '/' . $name . '.php';

		if ( empty( $folder ) ) {
			_doing_it_wrong(
				__METHOD__,
				esc_html( sprintf( 'get_template_folder() returned an empty string for field type "%s".', $this->type ) ),
				'2.2.5'
			);

			return;
		}

		if ( ! file_exists( $template_path ) ) {
			printf(
				'<div class="merchant-field-error"><strong>%s</strong> %s</div>',
				esc_html__( 'Merchant field error:', 'merchant' ),
				esc_html(
					sprintf(
						/* translators: 1: template name, 2: field type */
						__( 'Template "%1$s" not found for field type "%2$s".', 'merchant' ),
						$name . '.php',
						$this->type
					)
				)
			);

			return;
		}

		// Variables available in the template scope.
		$settings  = $this->field;
		$value     = $this->value;
		$module_id = $this->module_id;
		$field     = $this;

		// Extract extra args into the template scope (e.g. layout_type, has_accordion).
		if ( ! empty( $extra_args ) ) {
			extract( $extra_args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Intentional, matches merchant_get_template_part pattern.
		}

		include $template_path;
	}

	/**
	 * Render a sub-field with name attribute replacement.
	 *
	 * Captures the output of Merchant_Admin_Options::field(), then replaces
	 * the name attribute string for proper nesting (e.g., inside flexible_content or fields_group).
	 *
	 * @since 2.2.5
	 *
	 * @param array<string, mixed>         $settings  The sub-field configuration array.
	 * @param mixed                        $value     The sub-field value.
	 * @param string|array<int, string>    $search    The string(s) to search for in the rendered HTML.
	 * @param string|array<int, string>    $replace   The replacement string(s).
	 * @param string                       $module_id The module ID.
	 *
	 * @return void
	 */
	public static function render_sub_field( $settings, $value, $search, $replace, $module_id = '' ) {
		ob_start();
		Merchant_Settings_Renderer::field( $settings, $value, $module_id );
		$field_html = (string) ob_get_clean();

		// Replace attributes in the field.
		$field = str_replace( $search, $replace, $field_html );

		// Process specific field types.
		if ( isset( $settings['type'] ) && $settings['type'] === 'hook_select' ) {
			$field = self::update_field_attributes( $field, array(
				'select' => '[hook_name]',
				'input'  => '[hook_priority]',
			) );
		}

		echo wp_kses( $field, merchant_kses_allowed_tags( array( 'all' ) ) );
	}

	/**
	 * Update attributes for specific tags in the field HTML.
	 *
	 * @since 2.2.5
	 *
	 * @param string                $field   The field's HTML.
	 * @param array<string, string> $updates An associative array where keys are tag names
	 *                                       and values are strings to append to the `name` attribute.
	 *
	 * @return string Updated field HTML.
	 */
	protected static function update_field_attributes( $field, $updates ) {
		if ( ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
			return $field;
		}

		foreach ( $updates as $tag => $append ) {
			$processor = new WP_HTML_Tag_Processor( $field );

			while ( $processor->next_tag( array( 'tag_name' => $tag ) ) ) {
				$current_name = (string) $processor->get_attribute( 'name' );

				if ( $current_name && substr( $current_name, -strlen( $append ) ) !== $append ) {
					$processor->set_attribute( 'name', $current_name . $append );
				}
			}

			$field = $processor->get_updated_html();
		}

		return $field;
	}
}
