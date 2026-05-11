<?php
/**
 * Merchant - Pre Orders
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Pre Orders Class.
 *
 * This class handles the initialization and setup of the Pre Orders module, including
 * admin settings, asset enqueueing, and frontend styling.
 *
 * @property string $module_id The module ID.
 * @property bool $wc_only The WooCommerce only flag.
 * @property string $module_section The module section.
 * @property array $module_default_settings The module default settings.
 * @property array $module_data The module data.
 * @property string $module_options_path The module options path.
 */
class Merchant_Pre_Orders extends Merchant_Add_Module {

	/**
	 * Module ID.
	 *
	 * @var string The module ID.
	 */
	const MODULE_ID = 'pre-orders';

	/**
	 * Is module preview.
	 *
	 * @var bool The is module preview flag.
	 */
	public static $is_module_preview = false;

	/**
	 * Main functionality dependency.
	 *
	 * @var Merchant_Pre_Orders_Main_Functionality The main functionality instance.
	 */
	public $main_func;

	/**
	 * Set the module as having analytics.
	 *
	 * @var bool
	 */
	protected $has_analytics = true;

	/**
	 * Constructor.
	 *
	 * @param Merchant_Pre_Orders_Main_Functionality $main_func The main functionality instance.
	 */
	public function __construct( Merchant_Pre_Orders_Main_Functionality $main_func ) {
		// Module id.
		$this->module_id = self::MODULE_ID;

		// WooCommerce only.
		$this->wc_only = true;

		// Parent construct.
		parent::__construct();

		$this->main_func = $main_func;

		// Module section.
		$this->module_section = 'boost-revenue';

		// Module default settings.
		$this->module_default_settings = array(
			'button_text'     => __( 'Pre Order Now!', 'merchant' ),
			'additional_text' => __( 'Ships on {date}.', 'merchant' ),
		);

		// Module data.
		$this->module_data = Merchant_Admin_Modules::$modules_data[ self::MODULE_ID ];

		// Module options path.
		$this->module_options_path = MERCHANT_DIR . 'inc/modules/' . self::MODULE_ID . '/admin/options.php';

		// Is module preview page.
		if ( is_admin() && parent::is_module_settings_page() ) {
			self::$is_module_preview = true;

			// Enqueue admin styles.
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_css' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_js' ) );

			// Localize Script.
			add_filter( 'merchant_admin_localize_script', array( $this, 'localize_script' ) );

			// Admin preview box.
			add_filter( 'merchant_module_preview', array( $this, 'render_admin_preview' ), 10, 2 );

			// Custom CSS.
			// The custom CSS should be added here as well due to ensure preview box works properly.
			add_filter( 'merchant_custom_css', array( $this, 'admin_custom_css' ) );
		}

		add_action( 'merchant_admin_before_include_modules_options', array( $this, 'help_banner' ) );

		if ( Merchant_Modules::is_module_active( self::MODULE_ID ) && is_admin() ) {
			// Init translations.
			$this->init_translations();

			// Include product metabox for per-product settings.
			require_once MERCHANT_DIR . 'inc/modules/' . self::MODULE_ID . '/admin/class-pre-orders-metabox.php';
		}


		if ( ! Merchant_Modules::is_module_active( self::MODULE_ID ) ) {
			return;
		}

		// TODO: Refactor the 'Merchant_Pre_Orders_Main_Functionality' class to load admin things separated from frontend things.
		$main_func->init();

		// Return early if it's on admin but not in the respective module settings page.
		if ( is_admin() && ! wp_doing_ajax() && ! parent::is_module_settings_page() ) {
			return;
		}

		// Enqueue styles.
		add_action( 'merchant_enqueue_before_main_css_js', array( $this, 'enqueue_css' ) );

		// Enqueue scripts.
		add_action( 'merchant_enqueue_after_main_css_js', array( $this, 'enqueue_scripts' ) );

		// Localize script.
		add_filter( 'merchant_localize_script', array( $this, 'localize_script' ) );

		// Custom CSS.
		add_filter( 'merchant_custom_css', array( $this, 'frontend_custom_css' ) );

		$this->supply_missing_id( 'rules' );
	}

	/**
	 * Init translations for module settings.
	 *
	 * Registers strings for the Merchant translator tool.
	 *
	 * @return void
	 */
	public function init_translations() {
		$settings = $this->get_module_settings();
		if ( ! empty( $settings['rules'] ) ) {
			foreach ( $settings['rules'] as $rule ) {
				if ( ! empty( $rule['button_text'] ) ) {
					Merchant_Translator::register_string( $rule['button_text'], 'Pre order button text' );
				}
				if ( ! empty( $rule['additional_text'] ) ) {
					Merchant_Translator::register_string( $rule['additional_text'], 'Pre order additional information' );
				}
				if ( ! empty( $rule['cart_label_text'] ) ) {
					Merchant_Translator::register_string( $rule['cart_label_text'], 'Label text on cart' );
				}
			}
		}
	}

	/**
	 * Enqueue admin-specific CSS.
	 *
	 * @return void
	 */
	public function admin_enqueue_css() {
		if ( parent::is_module_settings_page() ) {
			wp_enqueue_style( 'merchant-' . self::MODULE_ID, MERCHANT_URI . 'assets/css/modules/' . self::MODULE_ID . '/pre-orders.min.css', array(), MERCHANT_VERSION );
			wp_enqueue_style( 'merchant-admin-' . self::MODULE_ID, MERCHANT_URI . 'assets/css/modules/' . self::MODULE_ID . '/admin/preview.min.css', array(), MERCHANT_VERSION );
		}
	}

	/**
	 * Enqueue admin-specific JavaScript.
	 *
	 * @return void
	 */
	public function admin_enqueue_js() {
		if ( parent::is_module_settings_page() ) {
			wp_enqueue_script(
				'merchant-admin-' . self::MODULE_ID,
				MERCHANT_URI . 'assets/js/modules/' . self::MODULE_ID . '/admin/preview.min.js',
				array( 'jquery' ),
				MERCHANT_VERSION,
				true
			);
		}
	}

	/**
	 * Enqueue frontend-specific CSS.
	 *
	 * @return void
	 */
	public function enqueue_css() {
		// Specific module styles.
		wp_enqueue_style( 'merchant-' . self::MODULE_ID, MERCHANT_URI . 'assets/css/modules/' . self::MODULE_ID . '/pre-orders.min.css', array(), MERCHANT_VERSION );

		// add inline style
		if ( is_singular( 'product' ) ) {
			wp_add_inline_style( 'merchant-' . self::MODULE_ID, $this->add_inline_style() );
		}
	}

	/**
	 * Add inline CSS styles based on the active rule.
	 *
	 * @return string The generated inline CSS.
	 */
	public function add_inline_style() {
		ob_start();
		$rule = $this->current_rule();
		if ( ! empty( $rule ) ) {
			?>
            .woocommerce .merchant-pre-ordered-product{
            --mrc-po-text-color: <?php echo esc_attr( $rule['text-color'] ?? '#FFF' ); ?>;
            --mrc-po-text-hover-color: <?php echo esc_attr( $rule['text-hover-color'] ?? '#FFF' ); ?>;
            --mrc-po-border-color: <?php echo esc_attr( $rule['border-color'] ?? '#212121' ); ?>;
            --mrc-po-border-hover-color: <?php echo esc_attr( $rule['border-hover-color'] ?? '#414141' ); ?>;
            --mrc-po-border-width: <?php echo esc_attr( ( $rule['border-width'] ?? 0 ) . 'px' ); ?>;
            --mrc-po-border-radius: <?php echo esc_attr( ( $rule['border-radius'] ?? 0 ) . 'px' ); ?>;
            --mrc-po-background-color: <?php echo esc_attr( $rule['background-color'] ?? '#212121' ); ?>;
            --mrc-po-background-hover-color: <?php echo esc_attr( $rule['background-hover-color'] ?? '#414141' ); ?>;
            }
			<?php
		}

		return ob_get_clean();
	}

	/**
	 * Enqueue frontend-specific scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		// Register and enqueue the main module script.
		wp_enqueue_script( 'merchant-' . self::MODULE_ID, MERCHANT_URI . 'assets/js/modules/' . self::MODULE_ID . '/pre-orders.min.js', array(), MERCHANT_VERSION, true );
	}

	/**
	 * Localize the main module script with settings and translations.
	 *
	 * @param array $setting The localized settings array.
	 *
	 * @return array The updated settings array.
	 */
	public function localize_script( $setting ) {
		$setting['pre_orders'] = true;
		$rule                  = $this->current_rule();
		if ( ! empty( $rule ) && ! empty( $rule['button_text'] ) ) {
			$setting['pre_orders_add_button_title'] = Merchant_Translator::translate( $rule['button_text'] );
		} else {
			$setting['pre_orders_add_button_title'] = esc_html__( 'Pre Order Now!', 'merchant' );
		}

		$setting['shipping_date_missing_text'] = esc_html__( 'Please set a shipping date first', 'merchant' );

		return $setting;
	}

	/**
	 * Render the admin preview for the module.
	 *
	 * @param Merchant_Admin_Preview $preview The preview object.
	 * @param string                 $module  The module ID.
	 *
	 * @return Merchant_Admin_Preview The updated preview object.
	 */
	public function render_admin_preview( $preview, $module ) {
		if ( self::MODULE_ID === $module ) {
			$settings = $this->get_module_settings();

			// Additional text.
			$additional_text = $settings['additional_text'];
			$time_format     = date_i18n( get_option( 'date_format' ), strtotime( gmdate( 'Y-m-d', strtotime( '+2 days' ) ) ) );
			$text            = $this->main_func->replace_date_text( $additional_text, $time_format );

			ob_start();
			self::admin_preview_content( $settings, $text );
			$content = ob_get_clean();

			// HTML.
			$preview->set_html( $content );

			// Button Text.
			$preview->set_text( 'button_text', '.add_to_cart_button' );

			// Additional Text.
			$preview->set_text( 'additional_text', '.merchant-pre-orders-date', array(
				array(
					'{date}',
				),
				array(
					$time_format,
				),
			) );
		}

		return $preview;
	}

	/**
	 * Admin preview HTML content.
	 *
	 * @param array  $settings The module settings.
	 * @param string $text     The formatted additional text.
	 *
	 * @return void
	 */
	public function admin_preview_content( $settings, $text ) {
		?>
        <div class="mrc-preview-single-product-elements">
            <div class="mrc-preview-left-column">
                <div class="mrc-preview-product-image-wrapper">
                    <div class="mrc-preview-product-image"></div>
                    <div class="mrc-preview-product-image-thumbs">
                        <div class="mrc-preview-product-image-thumb"></div>
                        <div class="mrc-preview-product-image-thumb"></div>
                        <div class="mrc-preview-product-image-thumb"></div>
                    </div>
                </div>
            </div>
            <div class="mrc-preview-right-column">
                <div class="mrc-preview-text-placeholder"></div>
                <div class="mrc-preview-text-placeholder mrc-mw-70"></div>
                <div class="mrc-preview-text-placeholder mrc-mw-40 mrc-hide-on-smaller-screens"></div>
                <div class="mrc-preview-text-placeholder mrc-mw-30 mrc-hide-on-smaller-screens"></div>
                <div class="merchant-pre-ordered-product">
                    <div class="merchant-pre-orders-date"><?php echo esc_html( $text ); ?></div>
                    <a href="#" class="add_to_cart_button"><?php echo esc_html( $settings['button_text'] ); ?></a>
                </div>
            </div>
        </div>
		<?php
	}

	/**
	 * Get Custom CSS for the module.
	 *
	 * @return string The generated CSS.
	 */
	public function get_module_custom_css() {
		$css = '';

		if ( is_admin() || is_singular( 'product' ) ) {
			// Text Color.
			$css .= Merchant_Custom_CSS::get_variable_css( 'pre-orders', 'text-color', '#FFF', '.merchant-pre-ordered-product', '--mrc-po-text-color' );

			// Text Color (hover).
			$css .= Merchant_Custom_CSS::get_variable_css( 'pre-orders', 'text-hover-color', '#FFF', '.merchant-pre-ordered-product', '--mrc-po-text-hover-color' );

			// Border Color.
			$css .= Merchant_Custom_CSS::get_variable_css( 'pre-orders', 'border-color', '#212121', '.merchant-pre-ordered-product', '--mrc-po-border-color' );

			// Border Color (hover).
			$css .= Merchant_Custom_CSS::get_variable_css( 'pre-orders', 'border-hover-color', '#414141', '.merchant-pre-ordered-product', '--mrc-po-border-hover-color' );

			// Border Width.
			$css .= Merchant_Custom_CSS::get_variable_css( 'pre-orders', 'border-width', 0, '.merchant-pre-ordered-product', '--mrc-po-border-width', 'px' );

			// Border Radius.
			$css .= Merchant_Custom_CSS::get_variable_css( 'pre-orders', 'border-radius', 0, '.merchant-pre-ordered-product', '--mrc-po-border-radius', 'px' );

			// Background Color.
			$css .= Merchant_Custom_CSS::get_variable_css( 'pre-orders', 'background-color', '#212121', '.merchant-pre-ordered-product', '--mrc-po-background-color' );

			// Background Color (hover).
			$css .= Merchant_Custom_CSS::get_variable_css( 'pre-orders', 'background-hover-color', '#414141', '.merchant-pre-ordered-product', '--mrc-po-background-hover-color' );
		}

		return $css;
	}

	/**
	 * Filter for admin custom CSS.
	 *
	 * @param string $css The custom CSS.
	 *
	 * @return string The updated custom CSS.
	 */
	public function admin_custom_css( $css ) {
		$css .= $this->get_module_custom_css();

		return $css;
	}

	/**
	 * Filter for frontend custom CSS.
	 *
	 * @param string $css The custom CSS.
	 *
	 * @return string The updated custom CSS.
	 */
	public function frontend_custom_css( $css ) {
		$css .= $this->get_module_custom_css();

		return $css;
	}

	/**
	 * Get the currently applicable product rule.
	 *
	 * @return array The rule array if found, empty array otherwise.
	 */
	private function current_rule() {
		if ( is_singular( 'product' ) ) {
			$product_id = get_queried_object_id();
			$product    = wc_get_product( $product_id );

			if ( ! $product ) {
				return array();
			}

			$rule = Merchant_Pre_Orders_Main_Functionality::available_product_rule( $product->get_id() );
			if ( empty( $rule ) && $product instanceof \WC_Product_Variable ) {
				$available_variations = $product->get_available_variations();
				foreach ( $available_variations as $variation ) {
					$rule = Merchant_Pre_Orders_Main_Functionality::available_product_rule( $variation['variation_id'] );
					if ( ! empty( $rule ) ) {
						break;
					}
				}
			}

			return $rule;
		}

		return array();
	}

	/**
	 * Display a help banner in the module settings page.
	 *
	 * @param string $module_id The module ID.
	 *
	 * @return void
	 */
	public function help_banner( $module_id ) {
		if ( $module_id === self::MODULE_ID ) {
			?>
            <div class="merchant-module-page-setting-fields">
                <div class="merchant-module-page-setting-field merchant-module-page-setting-field-content">
                    <div class="merchant-module-page-setting-field-inner">
                        <div class="merchant-tag-pre-orders">
                            <i class="dashicons dashicons-info"></i>
                            <p>
								<?php
								echo esc_html__(
									'Pre-orders captured by Merchant are tagged with "Merchant PreOrder" and can be found in your WooCommerce Order Section. You can control pre-order settings on a per-product basis from the individual product page.',
									'merchant'
								);
								printf(
									'<a href="%1s" target="_blank">%2s</a>',
									esc_url( admin_url( 'edit.php?post_type=shop_order' ) ),
									esc_html__( 'View Pre-Orders', 'merchant' )
								);
								?></p>
                        </div>
                    </div>
                </div>
            </div>
			<?php
		}
	}

	/**
	 * Supply missing flexible_id for the rules setting.
	 *
	 * @param string $setting_key The setting key for the flexible items.
	 *
	 * @return void
	 */
	public function supply_missing_id( $setting_key = 'offers' ) {
		$option = 'merchant_' . $this->module_id . '_missing_id_flag';

		if ( get_option( $option, false ) || ! method_exists( 'Merchant_Admin_Options', 'set' ) ) {
			return;
		}

		$flexible_items = Merchant_Admin_Options::get( $this->module_id, $setting_key, array() );
		if ( ! empty( $flexible_items ) ) {
			$update = 0;
			foreach ( $flexible_items as $key => $item ) {
				if ( empty( $item['flexible_id'] ) ) {
					$flexible_items[ $key ]['flexible_id'] = wp_generate_uuid4();
					++ $update;
				}
			}

			if ( $update > 0 ) {
				Merchant_Admin_Options::set( $this->module_id, $setting_key, $flexible_items );
			}

			update_option( $option, true, false );
		}
	}
}

// Rules Repository.
require_once MERCHANT_DIR . 'inc/modules/pre-orders/classes/class-pre-orders-rules.php';

// Main functionality.
require_once MERCHANT_DIR . 'inc/modules/pre-orders/class-pre-orders-main-functionality.php';

// Initialize the module.
add_action( 'init', function () {
	Merchant_Modules::create_module( new Merchant_Pre_Orders( new Merchant_Pre_Orders_Main_Functionality() ) );
} );
