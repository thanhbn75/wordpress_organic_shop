<?php
/**
 * Merchant Settings Renderer.
 *
 * Handles all rendering of module settings panels, including
 * field wrappers, titles, descriptions, and inner field content.
 * Extracted from {@see Merchant_Admin_Options}.
 *
 * @package Merchant
 * @since   1.9.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Settings_Renderer
 *
 * Renders module settings panels and their individual fields.
 * Delegates field-type rendering to the {@see Merchant_Field_Registry}.
 *
 * @since 1.9.3
 */
class Merchant_Settings_Renderer {

	/**
	 * Create and render a module settings panel.
	 *
	 * Processes the settings definition array, saves any submitted
	 * form data, and renders the full settings panel HTML including
	 * title, field wrappers, and individual fields.
	 *
	 * @since 1.0
	 *
	 * @param array<string, mixed> $settings {
	 *     Module settings configuration.
	 *
	 *     @type string $module   Module ID.
	 *     @type string $title    Panel title.
	 *     @type string $subtitle Panel subtitle.
	 *     @type array  $fields   Array of field definition arrays.
	 * }
	 *
	 * @return void
	 */
	public static function create( $settings ) {
		$module_id = ( isset( $_GET['module'] ) ) ? sanitize_text_field( wp_unslash( $_GET['module'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		/**
		 * Hook: merchant_module_settings
		 *
		 * @param array  $settings  Module settings.
		 * @param string $module_id Module ID.
		 *
		 * @since 1.0
		 */
		$settings = apply_filters( 'merchant_module_settings', $settings, $module_id );

		Merchant_Settings_Saver::save_options( $settings );

		$options = get_option( 'merchant', array() );
		?>
		<div class="merchant-module-page-settings">
			<div class="merchant-module-page-setting-box">
				<?php if ( ! empty( $settings['title'] ) ) : ?>
					<div class="merchant-module-page-setting-title">
						<?php echo esc_html( $settings['title'] ); ?>
						<?php if ( ! empty( $settings['subtitle'] ) ) : ?>
							<div class="merchant-module-page-setting-subtitle"><?php echo esc_html( $settings['subtitle'] ); ?></div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<div class="merchant-module-page-setting-fields">
					<?php self::render_fields( $settings, $options, $module_id ); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render all fields in a module settings panel.
	 *
	 * Iterates the field definitions, resolves each field's saved
	 * value, and delegates rendering to either {@see field()} or
	 * {@see disabled_field()} depending on Pro status.
	 *
	 * @since 1.9.3
	 *
	 * @param array<string, mixed>  $settings  Module settings with 'module' and 'fields' keys.
	 * @param array<string, mixed>  $options   All saved merchant options.
	 * @param string $module_id The current module ID.
	 *
	 * @return void
	 */
	private static function render_fields( $settings, $options, $module_id ) {
		if ( empty( $settings['fields'] ) ) {
			return;
		}

		$current_module = Merchant_Admin_Modules::get_module_info( $settings['module'] );
		$is_pro_module  = ! merchant_is_pro_active() && isset( $current_module['pro'] ) && $current_module['pro'] === true;

		foreach ( $settings['fields'] as $field ) {
			$value = $field['default'] ?? null;

			if ( isset( $field['id'] ) && isset( $options[ $settings['module'] ][ $field['id'] ] ) ) {
				$value = $options[ $settings['module'] ][ $field['id'] ];
			}

			$is_pro_field = ! merchant_is_pro_active() && isset( $field['pro'] ) && $field['pro'] === true;

			if ( $is_pro_module || $is_pro_field ) {
				self::disabled_field( $field, $value );
			} else {
				self::field( $field, $value, $module_id );
			}
		}
	}

	/**
	 * Render a single field.
	 *
	 * Outputs the full field markup including the wrapper div,
	 * title, description, and the field's inner HTML delegated
	 * to {@see Merchant_Field_Registry}.
	 *
	 * @since 1.0
	 *
	 * @param array<string, mixed> $settings  The field configuration array.
	 * @param mixed  $value     The current saved value for this field.
	 * @param string $module_id The module ID (used for nested field contexts).
	 *
	 * @return void
	 */
	public static function field( $settings, $value, $module_id = '' ) {
		if ( empty( $settings['type'] ) ) {
			return;
		}

		$type      = $settings['type'];
		$id        = $settings['id'] ?? '';
		$is_upsell = ! merchant_is_pro_active() && isset( $settings['pro'] ) && $settings['pro'] === true;
		$value     = self::resolve_field_value( $settings, $value, $module_id );

		self::render_field_wrapper_open( $settings, $value, $module_id, $type, $id );
		self::render_field_title( $settings, $id, $is_upsell );

		echo '<div class="merchant-module-page-setting-field-inner merchant-field-' . esc_attr( $id ) . '">';
		self::render_field_inner( $type, $settings, $value, $module_id );
		echo '</div>';

		self::render_field_description( $settings, $value, $module_id );
		echo '</div>';
	}

	/**
	 * Resolve the default value for a field based on its type.
	 *
	 * Handles type-specific fallback logic for checkbox_multiple,
	 * text, and url field types.
	 *
	 * @since 1.9.3
	 *
	 * @param array<string, mixed> $settings  The field configuration array.
	 * @param mixed  $value     The current raw value.
	 * @param string $module_id The module ID.
	 *
	 * @return mixed The resolved value.
	 */
	private static function resolve_field_value( $settings, $value, $module_id ) {
		$type    = $settings['type'];
		$id      = $settings['id'] ?? '';
		$default = $settings['default'] ?? null;

		if ( $value || 0 === $value || '0' === $value ) {
			return $value;
		}

		if ( $type === 'checkbox_multiple' ) {
			return (array) $default;
		}

		if ( in_array( $type, array( 'text', 'url' ), true ) && ! empty( $module_id ) ) {
			return Merchant_Option::get( $module_id, $id );
		}

		return $default;
	}

	/**
	 * Render the opening wrapper div for a field.
	 *
	 * Builds CSS classes and data attributes, then outputs the
	 * opening div tag.
	 *
	 * @since 1.9.3
	 *
	 * @param array<string, mixed> $settings  The field configuration array.
	 * @param mixed  $value     The field value.
	 * @param string $module_id The module ID.
	 * @param string $type      The field type.
	 * @param string $id        The field ID.
	 *
	 * @return void
	 */
	private static function render_field_wrapper_open( $settings, $value, $module_id, $type, $id ) {
		$class      = ! empty( $settings['class'] ) ? ' ' . $settings['class'] : '';
		$condition  = $settings['condition'] ?? array();
		$conditions = $settings['conditions'] ?? '';

		$wrapper_classes   = array( 'merchant-module-page-setting-field' );
		$wrapper_classes[] = 'merchant-module-page-setting-field-' . $type;

		if ( ! empty( $class ) ) {
			$wrapper_classes[] = $class;
		}

		/**
		 * Hook 'merchant_admin_module_field_wrapper_classes'
		 *
		 * @since 1.9.3
		 */
		$wrapper_classes = apply_filters( 'merchant_admin_module_field_wrapper_classes', $wrapper_classes, $settings, $value, $module_id );

		echo '<div class="' . esc_attr( implode( ' ', $wrapper_classes ) ) . '" data-id="'
			. esc_attr( $id ) . '" data-type="' . esc_attr( $type ) . '" data-condition="' . esc_attr( (string) wp_json_encode( $condition ) )
			. '" data-conditions="' . ( $conditions ? esc_attr( (string) wp_json_encode( $conditions ) ) : "" ) . '">';
	}

	/**
	 * Render the title bar for a field, including Pro upsell badge.
	 *
	 * @since 1.9.3
	 *
	 * @param array<string, mixed> $settings  The field configuration array.
	 * @param string $id        The field ID.
	 * @param bool   $is_upsell Whether to show the Pro upsell badge.
	 *
	 * @return void
	 */
	private static function render_field_title( $settings, $id, $is_upsell ) {
		if ( empty( $settings['title'] ) ) {
			return;
		}
		?>
		<div class="merchant-module-page-setting-field-title<?php echo esc_attr( $is_upsell ? ' merchant-module-page-setting-field-title__has-upsell' : '' ); ?>">
			<?php echo esc_html( $settings['title'] ); ?>

			<?php if ( $is_upsell ) : ?>
				<a href="https://athemes.com/merchant-upgrade?utm_source=inner_module_settings_field&utm_content=<?php echo esc_attr( $id ); ?>&utm_medium=merchant_dashboard&utm_campaign=Merchant" class="merchant-module-pro-upsell" target="_blank">
					<span class="merchant-pro-badge merchant-pro-tooltip" data-tooltip-message="<?php echo esc_attr__( 'This option is only available on Merchant Pro', 'merchant' ); ?>">
						<svg width="28" height="16" viewBox="0 0 28 16" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M7.41309 8.90723H5.58203V7.85254H7.41309C7.71257 7.85254 7.95508 7.80371 8.14062 7.70605C8.32943 7.60514 8.46777 7.46842 8.55566 7.2959C8.64355 7.12012 8.6875 6.91992 8.6875 6.69531C8.6875 6.47721 8.64355 6.27376 8.55566 6.08496C8.46777 5.89616 8.32943 5.74316 8.14062 5.62598C7.95508 5.50879 7.71257 5.4502 7.41309 5.4502H6.02148V11.5H4.67871V4.39062H7.41309C7.96647 4.39062 8.43848 4.48991 8.8291 4.68848C9.22298 4.88379 9.52246 5.1556 9.72754 5.50391C9.93587 5.84896 10.04 6.24284 10.04 6.68555C10.04 7.14453 9.93587 7.54004 9.72754 7.87207C9.52246 8.2041 9.22298 8.45964 8.8291 8.63867C8.43848 8.81771 7.96647 8.90723 7.41309 8.90723ZM11.0947 4.39062H13.6777C14.2181 4.39062 14.682 4.47201 15.0693 4.63477C15.4567 4.79753 15.7546 5.03841 15.9629 5.35742C16.1712 5.67643 16.2754 6.06868 16.2754 6.53418C16.2754 6.90202 16.2103 7.22103 16.0801 7.49121C15.9499 7.76139 15.766 7.98763 15.5283 8.16992C15.2939 8.35221 15.0173 8.49544 14.6982 8.59961L14.2783 8.81445H11.998L11.9883 7.75488H13.6924C13.9691 7.75488 14.1986 7.70605 14.3809 7.6084C14.5632 7.51074 14.6999 7.37565 14.791 7.20312C14.8854 7.0306 14.9326 6.83366 14.9326 6.6123C14.9326 6.37467 14.887 6.1696 14.7959 5.99707C14.7048 5.82129 14.5664 5.6862 14.3809 5.5918C14.1953 5.4974 13.9609 5.4502 13.6777 5.4502H12.4375V11.5H11.0947V4.39062ZM15.1084 11.5L13.4629 8.31641L14.8838 8.31152L16.5488 11.4316V11.5H15.1084ZM23.209 7.76465V8.13086C23.209 8.66797 23.1374 9.15137 22.9941 9.58105C22.8509 10.0075 22.6475 10.3704 22.3838 10.6699C22.1201 10.9694 21.806 11.1989 21.4414 11.3584C21.0768 11.5179 20.6715 11.5977 20.2256 11.5977C19.7861 11.5977 19.3825 11.5179 19.0146 11.3584C18.6501 11.1989 18.3343 10.9694 18.0674 10.6699C17.8005 10.3704 17.5938 10.0075 17.4473 9.58105C17.3008 9.15137 17.2275 8.66797 17.2275 8.13086V7.76465C17.2275 7.22428 17.3008 6.74089 17.4473 6.31445C17.5938 5.88802 17.7988 5.52507 18.0625 5.22559C18.3262 4.92285 18.6403 4.69173 19.0049 4.53223C19.3727 4.37272 19.7764 4.29297 20.2158 4.29297C20.6618 4.29297 21.0671 4.37272 21.4316 4.53223C21.7962 4.69173 22.1104 4.92285 22.374 5.22559C22.641 5.52507 22.846 5.88802 22.9893 6.31445C23.1357 6.74089 23.209 7.22428 23.209 7.76465ZM21.8516 8.13086V7.75488C21.8516 7.36751 21.8158 7.02734 21.7441 6.73438C21.6725 6.43815 21.5667 6.18913 21.4268 5.9873C21.2868 5.78548 21.1143 5.63411 20.9092 5.5332C20.7041 5.42904 20.473 5.37695 20.2158 5.37695C19.9554 5.37695 19.7243 5.42904 19.5225 5.5332C19.3239 5.63411 19.1546 5.78548 19.0146 5.9873C18.8747 6.18913 18.7673 6.43815 18.6924 6.73438C18.6208 7.02734 18.585 7.36751 18.585 7.75488V8.13086C18.585 8.51497 18.6208 8.85514 18.6924 9.15137C18.7673 9.44759 18.8747 9.69824 19.0146 9.90332C19.1579 10.1051 19.3304 10.2581 19.5322 10.3623C19.734 10.4665 19.9652 10.5186 20.2256 10.5186C20.486 10.5186 20.7171 10.4665 20.9189 10.3623C21.1208 10.2581 21.29 10.1051 21.4268 9.90332C21.5667 9.69824 21.6725 9.44759 21.7441 9.15137C21.8158 8.85514 21.8516 8.51497 21.8516 8.13086Z" fill="#3858E9"/>
							<rect x="0.5" y="1" width="27" height="14" rx="1.5" stroke="#3858E9"/>
						</svg>
					</span>
				</a>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render the inner field content via the field registry.
	 *
	 * Delegates rendering to the registered field class. Logs an
	 * error if the field type is unknown.
	 *
	 * @since 1.9.3
	 *
	 * @param string               $type      The field type.
	 * @param array<string, mixed> $settings  The field configuration array.
	 * @param mixed  $value     The field value.
	 * @param string $module_id The module ID.
	 *
	 * @return void
	 */
	private static function render_field_inner( $type, $settings, $value, $module_id ) {
		$registry = Merchant_Field_Registry::instance();

		if ( $registry->has( $type ) ) {
			try {
				$field_instance = $registry->create( $type, $settings, $value, $module_id );
				if ( $field_instance !== null ) {
					$field_instance->render();
				}
			} catch ( \Exception $e ) {
				wp_trigger_error( __METHOD__, 'Merchant field render error (' . $type . '): ' . $e->getMessage() ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			}
		} else {
			printf(
				'<div class="merchant-field-error"><strong>%s</strong> %s</div>',
				esc_html__( 'Merchant field error:', 'merchant' ),
				esc_html(
					sprintf(
						/* translators: %s: the unsupported field type slug */
						__( 'Unknown field type "%s". Register it via the merchant_field_types filter.', 'merchant' ),
						$type
					)
				)
			);
			_doing_it_wrong(
				__METHOD__,
				sprintf( 'Unknown field type "%s". Register it via the merchant_field_types filter.', esc_html( $type ) ),
				'2.2.5'
			);
		}
	}

	/**
	 * Render the description and hidden description for a field.
	 *
	 * @since 1.9.3
	 *
	 * @param array<string, mixed> $settings  The field configuration array.
	 * @param mixed  $value     The field value.
	 * @param string $module_id The module ID.
	 *
	 * @return void
	 */
	private static function render_field_description( $settings, $value, $module_id ) {
		$hidden_desc = $settings['hidden_desc'] ?? '';

		/**
		 * Hook 'merchant_admin_module_field_hidden_description'
		 *
		 * @since 1.9.3
		 */
		$hidden_desc = apply_filters( 'merchant_admin_module_field_hidden_description', $hidden_desc, $settings, $value, $module_id );

		$desc = $settings['desc'] ?? '';

		/**
		 * Hook 'merchant_admin_module_field_description'
		 *
		 * @since 1.9.3
		 */
		$desc = apply_filters( 'merchant_admin_module_field_description', $desc, $settings, $value, $module_id );

		if ( ! empty( $desc ) ) {
			$hidden_desc_html = '';
			if ( ! empty( $hidden_desc ) ) {
				$hidden_desc_html  = '<div class="merchant-module-page-setting-field-hidden-desc-trigger" data-show-text="' . esc_html__( 'Show more', 'merchant' ) . '" data-hidden-text="' . esc_html__( 'Show less', 'merchant' ) . '"><span>' . esc_html__( 'Show more', 'merchant' ) . '</span>';
				$hidden_desc_html .= '<img src="' . esc_url( MERCHANT_URI . '/assets/images/arrow-down.svg' ) . '" alt="Merchant" />';
				$hidden_desc_html .= '</div>';
			}

			$desc_class = 'merchant-module-page-setting-field-desc'
				. ( $hidden_desc ? ' merchant-module-page-setting-field-desc-has-hidden-desc' : '' );

			printf( '<div class="%s">%s%s</div>', esc_attr( $desc_class ), wp_kses_post( $desc ), wp_kses_post( $hidden_desc_html ) );
		}

		if ( ! empty( $hidden_desc ) ) {
			printf( '<div class="merchant-module-page-setting-field-hidden-desc">%s</div>', wp_kses_post( nl2br( $hidden_desc ) ) );
		}
	}

	/**
	 * Render a disabled (pro-gated) field.
	 *
	 * Renders the field via the normal registry path, then disables all
	 * interactive elements so the user can see the control but not interact.
	 *
	 * @since 1.0
	 *
	 * @param array<string, mixed> $settings  Field settings.
	 * @param mixed  $value     Field value.
	 * @param string $module_id Module ID.
	 *
	 * @return void
	 */
	public static function disabled_field( $settings, $value, $module_id = '' ) {
		ob_start();
		self::field( $settings, $value, $module_id );
		$field_html = (string) ob_get_clean();

		$field_html = str_replace(
			array( '<input ', '<select ', '<textarea ', '<button ', 'merchant-module-page-setting-field-inner' ),
			array( '<input disabled ', '<select disabled ', '<textarea disabled ', '<button disabled ', 'merchant-module-page-setting-field-inner disabled' ),
			$field_html
		);

		// The HTML is generated by self::field() — trusted admin output.
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- All values already escaped in field().
		echo $field_html;
	}
}
