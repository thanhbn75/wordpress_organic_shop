<?php

/**
 * Buy X Get Y
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Buy X Get Y Class.
 *
 */
class Merchant_Buy_X_Get_Y extends Merchant_Add_Module {

	/**
	 * Module ID.
	 */
	const MODULE_ID = 'buy-x-get-y';

	/**
	 * Module path.
	 */
	const MODULE_DIR = MERCHANT_DIR . 'inc/modules/' . self::MODULE_ID;

	/**
	 * Module template path.
	 */
	const MODULE_TEMPLATES = 'modules/' . self::MODULE_ID;

	/**
     * Set the module as having analytics.
     *
	 * @var bool
	 */
	protected $has_analytics = true;

	/**
	 * Constructor.
	 *
	 */
	public function __construct() {
		// Module id.
		$this->module_id = self::MODULE_ID;

		// WooCommerce only.
		$this->wc_only = true;

		// Parent construct.
		parent::__construct();

		// Module section.
		$this->module_section = 'boost-revenue';

		// Module data.
		$this->module_data = Merchant_Admin_Modules::$modules_data[ self::MODULE_ID ];

		// Module options path.
		$this->module_options_path = self::MODULE_DIR . "/admin/options.php";

		// Enqueue admin styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );

		// Add preview box
		add_filter( 'merchant_module_preview', array( $this, 'render_admin_preview' ), 10, 2 );

		if ( Merchant_Modules::is_module_active( self::MODULE_ID ) && is_admin() ) {
			// Init translations.
			$this->init_translations();
		}
	}

	/**
	 * Init translations.
	 *
	 * @return void
	 */
	public function init_translations() {
		$settings = $this->get_module_settings();
		$strings  = array(
			'offer-title' => 'Buy X Get Y: Campaign title',
			'title'       => 'Buy X Get Y: title',
			'buy_label'   => 'Buy X Get Y: buy label',
			'get_label'   => 'Buy X Get Y: get label',
			'button_text' => 'Buy X Get Y: button text',
		);
		if ( ! empty( $settings['rules'] ) ) {
			foreach ( $settings['rules'] as $rule ) {
				foreach ( $strings as $key => $string ) {
					if ( ! empty( $rule['product_single_page'][ $key ] ) ) {
						Merchant_Translator::register_string( $rule['product_single_page'][ $key ], $string . ' - product single page' );
					}
					if ( ! empty( $rule['cart_page'][ $key ] ) ) {
						Merchant_Translator::register_string( $rule['cart_page'][ $key ], $string . ' - cart page' );
					}
				}
			}
		}
	}

	/**
	 * Enqueue admin page content scripts.
	 *
	 * @return void
	 */
	public
	function enqueue_admin_styles() {
		if ( $this->is_module_settings_page() ) {
			// Module styling.
			wp_enqueue_style(
				'merchant-' . self::MODULE_ID,
				MERCHANT_URI . 'assets/css/modules/' . self::MODULE_ID . '/' . self::MODULE_ID . '.min.css',
				array(),
				MERCHANT_VERSION
			);

			// Preview-specific styling.
			wp_enqueue_style(
				'merchant-preview-' . self::MODULE_ID,
				MERCHANT_URI . 'assets/css/modules/' . self::MODULE_ID . '/admin/preview.min.css',
				array(),
				MERCHANT_VERSION
			);

			wp_enqueue_script(
				'merchant-preview-' . self::MODULE_ID,
				MERCHANT_URI . 'assets/js/modules/' . self::MODULE_ID . '/admin/preview.min.js',
				array(),
				MERCHANT_VERSION,
				true
			);
		}
	}

	/**
	 * Render admin preview.
	 *
	 * @param Merchant_Admin_Preview $preview The preview instance.
	 * @param string                 $module  The module ID.
	 *
	 * @return Merchant_Admin_Preview
	 */
	public function render_admin_preview( $preview, $module ) {
		if ( $module !== self::MODULE_ID ) {
			return $preview;
		}

		$template_args = $this->get_preview_template_args();

		$preview_html  = $this->get_single_product_preview_html( $template_args );
		$preview_html .= $this->cart_preview();
		$preview_html .= $this->checkout_page_preview();
		$preview_html .= $this->thank_you_page_preview();

		$preview->set_html( $preview_html );

		$this->register_preview_live_bindings( $preview );

		return $preview;
	}

	/**
	 * Prepare template args for the single product admin preview.
	 *
	 * Handles all data computation: prices, labels, inline styles.
	 *
	 * @return array Ready-to-render template args.
	 */
	private function get_preview_template_args() {
		// Currency symbol.
		$currency_symbol = function_exists( 'get_woocommerce_currency_symbol' )
			? get_woocommerce_currency_symbol()
			: '$';

		// Dummy offer values.
		$buy_price     = 20;
		$get_price     = 18;
		$discount      = 10;
		$discount_type = 'percentage';
		$min_quantity  = 2;
		$gift_quantity = 1;

		// Calculate discounted price.
		if ( $discount_type === 'percentage' ) {
			$reduced_price = $get_price * ( 1 - $discount / 100 );
		} else {
			$reduced_price = max( 0, $get_price - $discount );
		}
		$reduced_price = round( $reduced_price, 2 );

		// Discount display text.
		$discount_display = $discount_type === 'percentage'
			? $discount . '%'
			: merchant_preview_price( $discount, $currency_symbol );

		return array(
			'title_text'         => __( 'Buy One Get One', 'merchant' ),
			/* translators: %s: quantity */
			'buy_label_text'     => sprintf( __( 'Buy %s', 'merchant' ), $min_quantity ),
			/* translators: %1$s: quantity, %2$s: discount value */
			'get_label_text'     => sprintf( __( 'Get %1$s with %2$s off', 'merchant' ), $gift_quantity, $discount_display ),
			'button_text'        => __( 'Add To Cart', 'merchant' ),
			'buy_price_html'     => merchant_preview_price( $buy_price, $currency_symbol ),
			'get_price_html'     => merchant_preview_price( $get_price, $currency_symbol ),
			'reduced_price_html' => merchant_preview_price( $reduced_price, $currency_symbol ),
			'title_style'        => '',
			'label_style'        => '',
			'arrow_style'        => '',
			'offer_y_style'      => '',
		);
	}

	/**
	 * Build the single product preview HTML with the product page simulation layout.
	 *
	 * @param array $template_args Pre-computed template args from get_preview_template_args().
	 *
	 * @return string The HTML string.
	 */
	private function get_single_product_preview_html( $template_args ) {
		ob_start();
		?>
		<div class="merchant-single-product-preview">
			<div class="mrc-preview-single-product-elements">
				<div class="mrc-preview-left-column">
					<div class="mrc-preview-product-image-wrapper">
						<div class="mrc-preview-product-image"></div>
					</div>
				</div>
				<div class="mrc-preview-right-column">
					<div class="mrc-preview-product-info-placeholder">
						<div class="mrc-preview-placeholder-bar mrc-preview-placeholder-bar--long mrc-preview-placeholder-bar--thick"></div>
						<div class="mrc-preview-placeholder-bar mrc-preview-placeholder-bar--short mrc-preview-placeholder-bar--thick"></div>
						<div class="mrc-preview-placeholder-bar mrc-preview-placeholder-bar--spacer"></div>
						<div class="mrc-preview-placeholder-bar mrc-preview-placeholder-bar--long"></div>
						<div class="mrc-preview-placeholder-bar mrc-preview-placeholder-bar--medium"></div>
					</div>
					<?php
					merchant_get_template_part(
						self::MODULE_TEMPLATES,
						'single-product-admin-preview',
						$template_args
					);
					?>
				</div>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Register live-binding text replacements for the admin preview.
	 *
	 * @param Merchant_Admin_Preview $preview The preview instance.
	 *
	 * @return void
	 */
	private function register_preview_live_bindings( $preview ) {
		$preview->set_text( 'title', '.merchant-bogo-title' );

		$preview->set_text( 'buy_label', '.merchant-bogo-product-buy-label', array(
			array( '{quantity}' ),
			array( '2' ),
		) );

		$preview->set_text( 'get_label', '.merchant-bogo-product-get-label', array(
			array( '{quantity}', '{discount}' ),
			array( '2', '10%' ),
		) );

		$preview->set_text( 'button_text', '.merchant-bogo-add-to-cart' );
	}

	/**
	 * Cart item admin preview.
	 *
	 * @return string
	 */
	public function cart_preview() {
		return merchant_get_template_part( self::MODULE_TEMPLATES, 'cart-admin-preview', array(), true );
	}

	/**
	 * Checkout page preview.
	 *
	 * @return string
	 */
	public function checkout_page_preview() {
		return merchant_get_template_part( self::MODULE_TEMPLATES, 'checkout-admin-preview', array(), true );
	}

	/**
	 * Thank you page preview.
	 *
	 * @return string
	 */
	public function thank_you_page_preview() {
		return merchant_get_template_part( self::MODULE_TEMPLATES, 'thank-you-admin-preview', array(), true );
	}
}

// Initialize the module.
add_action( 'init', function () {
	Merchant_Modules::create_module( new Merchant_Buy_X_Get_Y() );
} );
