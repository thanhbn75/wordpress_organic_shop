<?php
/**
 * Merchant Field Registry.
 *
 * Singleton registry that maps field type strings to their corresponding
 * field classes. Uses an explicit array for field registration.
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Merchant_Field_Registry
 *
 * @since 2.2.5
 */
class Merchant_Field_Registry {

	/**
	 * The single class instance.
	 *
	 * @since 2.2.5
	 * @var Merchant_Field_Registry|null
	 */
	private static $instance = null;

	/**
	 * Registered field types.
	 * Maps type string => class name.
	 *
	 * @since 2.2.5
	 * @var array<string, string>
	 */
	private $types = array();

	/**
	 * Get the singleton instance.
	 *
	 * @since 2.2.5
	 *
	 * @return Merchant_Field_Registry
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Reset the singleton instance.
	 *
	 * Clears the cached instance so a fresh one is created
	 * on the next {@see instance()} call. Intended for unit tests.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public static function reset(): void {
		self::$instance = null;
	}

	/**
	 * Constructor.
	 *
	 * Loads field classes from an explicit registry array and builds the type map.
	 *
	 * @since 2.2.5
	 */
	private function __construct() {
		$base_path = MERCHANT_DIR . 'admin/classes/admin-options/fields';

		/**
		 * Core field type definitions.
		 *
		 * Each entry maps a type key to its class name and file path.
		 * Format: type_key => [ 'class' => ClassName, 'file' => absolute_path ]
		 *
		 * @since 2.2.5
		 */
		$field_types = array(
			'text'                  => array(
				'class' => 'Merchant_Field_Text',
				'file'  => $base_path . '/text/class-merchant-field-text.php',
			),
			'number'                => array(
				'class' => 'Merchant_Field_Number',
				'file'  => $base_path . '/number/class-merchant-field-number.php',
			),
			'url'                   => array(
				'class' => 'Merchant_Field_Url',
				'file'  => $base_path . '/url/class-merchant-field-url.php',
			),
			'textarea'              => array(
				'class' => 'Merchant_Field_Textarea',
				'file'  => $base_path . '/textarea/class-merchant-field-textarea.php',
			),
			'textarea_multiline'    => array(
				'class' => 'Merchant_Field_Textarea_Multiline',
				'file'  => $base_path . '/textarea-multiline/class-merchant-field-textarea-multiline.php',
			),
			'textarea_code'         => array(
				'class' => 'Merchant_Field_Textarea_Code',
				'file'  => $base_path . '/textarea-code/class-merchant-field-textarea-code.php',
			),
			'text_readonly'         => array(
				'class' => 'Merchant_Field_Text_Readonly',
				'file'  => $base_path . '/text-readonly/class-merchant-field-text-readonly.php',
			),
			'color'                 => array(
				'class' => 'Merchant_Field_Color',
				'file'  => $base_path . '/color/class-merchant-field-color.php',
			),
			'range'                 => array(
				'class' => 'Merchant_Field_Range',
				'file'  => $base_path . '/range/class-merchant-field-range.php',
			),
			'date_time'             => array(
				'class' => 'Merchant_Field_Date_Time',
				'file'  => $base_path . '/date-time/class-merchant-field-date-time.php',
			),
			'select'                => array(
				'class' => 'Merchant_Field_Select',
				'file'  => $base_path . '/select/class-merchant-field-select.php',
			),
			'select_ajax'           => array(
				'class' => 'Merchant_Field_Select_Ajax',
				'file'  => $base_path . '/select-ajax/class-merchant-field-select-ajax.php',
			),
			'select_size_chart'     => array(
				'class' => 'Merchant_Field_Select_Size_Chart',
				'file'  => $base_path . '/select-size-chart/class-merchant-field-select-size-chart.php',
			),
			'radio'                 => array(
				'class' => 'Merchant_Field_Radio',
				'file'  => $base_path . '/radio/class-merchant-field-radio.php',
			),
			'radio_alt'             => array(
				'class' => 'Merchant_Field_Radio_Alt',
				'file'  => $base_path . '/radio-alt/class-merchant-field-radio-alt.php',
			),
			'buttons'               => array(
				'class' => 'Merchant_Field_Buttons',
				'file'  => $base_path . '/buttons/class-merchant-field-buttons.php',
			),
			'buttons_alt'           => array(
				'class' => 'Merchant_Field_Buttons_Alt',
				'file'  => $base_path . '/buttons-alt/class-merchant-field-buttons-alt.php',
			),
			'buttons_content'       => array(
				'class' => 'Merchant_Field_Buttons_Content',
				'file'  => $base_path . '/buttons-content/class-merchant-field-buttons-content.php',
			),
			'image_picker'          => array(
				'class' => 'Merchant_Field_Image_Picker',
				'file'  => $base_path . '/image-picker/class-merchant-field-image-picker.php',
			),
			'choices'               => array(
				'class' => 'Merchant_Field_Choices',
				'file'  => $base_path . '/choices/class-merchant-field-choices.php',
			),
			'switcher'              => array(
				'class' => 'Merchant_Field_Switcher',
				'file'  => $base_path . '/switcher/class-merchant-field-switcher.php',
			),
			'checkbox'              => array(
				'class' => 'Merchant_Field_Checkbox',
				'file'  => $base_path . '/checkbox/class-merchant-field-checkbox.php',
			),
			'checkbox_multiple'     => array(
				'class' => 'Merchant_Field_Checkbox_Multiple',
				'file'  => $base_path . '/checkbox-multiple/class-merchant-field-checkbox-multiple.php',
			),
			'dimensions'            => array(
				'class' => 'Merchant_Field_Dimensions',
				'file'  => $base_path . '/dimensions/class-merchant-field-dimensions.php',
			),
			'responsive_dimensions' => array(
				'class' => 'Merchant_Field_Responsive_Dimensions',
				'file'  => $base_path . '/responsive-dimensions/class-merchant-field-responsive-dimensions.php',
			),
			'sortable'              => array(
				'class' => 'Merchant_Field_Sortable',
				'file'  => $base_path . '/sortable/class-merchant-field-sortable.php',
			),
			'sortable_repeater'     => array(
				'class' => 'Merchant_Field_Sortable_Repeater',
				'file'  => $base_path . '/sortable-repeater/class-merchant-field-sortable-repeater.php',
			),
			'upload'                => array(
				'class' => 'Merchant_Field_Upload',
				'file'  => $base_path . '/upload/class-merchant-field-upload.php',
			),
			'gallery'               => array(
				'class' => 'Merchant_Field_Gallery',
				'file'  => $base_path . '/gallery/class-merchant-field-gallery.php',
			),
			'hook_select'           => array(
				'class' => 'Merchant_Field_Hook_Select',
				'file'  => $base_path . '/hook-select/class-merchant-field-hook-select.php',
			),
			'create_page'           => array(
				'class' => 'Merchant_Field_Create_Page',
				'file'  => $base_path . '/create-page/class-merchant-field-create-page.php',
			),
			'products_selector'     => array(
				'class' => 'Merchant_Field_Products_Selector',
				'file'  => $base_path . '/products-selector/class-merchant-field-products-selector.php',
			),
			'reviews_selector'      => array(
				'class' => 'Merchant_Field_Reviews_Selector',
				'file'  => $base_path . '/reviews-selector/class-merchant-field-reviews-selector.php',
			),
			'wc_coupons'            => array(
				'class' => 'Merchant_Field_Wc_Coupons',
				'file'  => $base_path . '/wc-coupons/class-merchant-field-wc-coupons.php',
			),
			'flexible_content'      => array(
				'class' => 'Merchant_Field_Flexible_Content',
				'file'  => $base_path . '/flexible-content/class-merchant-field-flexible-content.php',
			),
			'fields_group'          => array(
				'class' => 'Merchant_Field_Fields_Group',
				'file'  => $base_path . '/fields-group/class-merchant-field-fields-group.php',
			),
			'content'               => array(
				'class' => 'Merchant_Field_Content',
				'file'  => $base_path . '/content/class-merchant-field-content.php',
			),
			'divider'               => array(
				'class' => 'Merchant_Field_Divider',
				'file'  => $base_path . '/divider/class-merchant-field-divider.php',
			),
			'info'                  => array(
				'class' => 'Merchant_Field_Info',
				'file'  => $base_path . '/info/class-merchant-field-info.php',
			),
			'info_block'            => array(
				'class' => 'Merchant_Field_Info_Block',
				'file'  => $base_path . '/info-block/class-merchant-field-info-block.php',
			),
			'warning'               => array(
				'class' => 'Merchant_Field_Warning',
				'file'  => $base_path . '/warning/class-merchant-field-warning.php',
			),
			'custom_callback'       => array(
				'class' => 'Merchant_Field_Custom_Callback',
				'file'  => $base_path . '/custom-callback/class-merchant-field-custom-callback.php',
			),
		);

		/**
		 * Filter: register, override, or remove field types.
		 *
		 * Allows 3rd-party plugins and Merchant Pro to register custom field types
		 * or override existing ones.
		 *
		 * Expected format: array( 'type_key' => array( 'class' => 'ClassName', 'file' => '/path/to/file.php' ) )
		 *
		 * @since 2.2.5
		 *
		 * @param array<string, array<string, string>> $field_types Map of type key => config array.
		 */
		$field_types = apply_filters( 'merchant_field_types', $field_types );

		// Load and register each field type.
		foreach ( $field_types as $type_key => $config ) {
			if ( ! empty( $config['file'] ) && file_exists( $config['file'] ) ) {
				require_once $config['file'];
			}

			if ( ! empty( $config['class'] ) && class_exists( $config['class'] ) ) {
				$this->register( $type_key, $config['class'] );
			}
		}
	}

	/**
	 * Check if a field type is registered.
	 *
	 * @since 2.2.5
	 *
	 * @param string $type The field type string.
	 *
	 * @return bool
	 */
	public function has( $type ) {
		return isset( $this->types[ $type ] );
	}

	/**
	 * Create a field instance for the given type.
	 *
	 * @since 2.2.5
	 *
	 * @param string               $type      The field type string.
	 * @param array<string, mixed> $field     The field configuration array.
	 * @param mixed                $value     The current saved value.
	 * @param string               $module_id The module ID.
	 *
	 * @return Merchant_Field_Interface|null The field instance, or null if type is unknown and text fallback unavailable.
	 */
	public function create( $type, $field, $value = null, $module_id = '' ) {
		if ( ! $this->has( $type ) ) {
			_doing_it_wrong(
				__METHOD__,
				sprintf(
					/* translators: %s: field type */
					'Unknown field type "%s". Falling back to text field.',
					esc_html( $type )
				),
				'2.2.5'
			);

			// Fallback to text field if available, otherwise return a basic instance.
			if ( $this->has( 'text' ) ) {
				$class = $this->types['text'];

				/** @var Merchant_Field_Interface $instance */
				$instance = new $class( $field, $value, $module_id );

				return $instance;
			}

			// If even text is not registered, this is an early bootstrap call.
			return null;
		}

		$class = $this->types[ $type ];

		/** @var Merchant_Field_Interface $instance */
		$instance = new $class( $field, $value, $module_id );

		return $instance;
	}

	/**
	 * Get all registered types.
	 *
	 * @since 2.2.5
	 *
	 * @return array<string, string> Map of type => class name.
	 */
	public function get_types() {
		return $this->types;
	}

	/**
	 * Register a field type manually.
	 *
	 * Used internally and by the merchant_field_types filter.
	 *
	 * @since 2.2.5
	 *
	 * @param string $type       The field type string.
	 * @param string $class_name The fully qualified class name.
	 *
	 * @return void
	 */
	public function register( $type, $class_name ) {
		$this->types[ $type ] = $class_name;
	}
}
