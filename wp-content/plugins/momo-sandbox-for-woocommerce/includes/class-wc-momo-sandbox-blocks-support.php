<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

defined( 'ABSPATH' ) || exit;

final class WC_MoMo_Sandbox_Blocks_Support extends AbstractPaymentMethodType {
	protected $name = 'momo_sandbox';

	public function initialize() {
		$this->settings = get_option( 'woocommerce_momo_sandbox_settings', array() );
	}

	public function is_active() {
		$payment_gateways = WC()->payment_gateways()->payment_gateways();
		return isset( $payment_gateways['momo_sandbox'] ) && $payment_gateways['momo_sandbox']->is_available();
	}

	public function get_payment_method_script_handles() {
		$asset_path   = WC_MOMO_SANDBOX_PATH . 'assets/js/index.asset.php';
		$version      = WC_MOMO_SANDBOX_VERSION;
		$dependencies = array( 'wc-blocks-registry', 'wc-settings', 'wp-element', 'wp-html-entities', 'wp-i18n' );

		if ( file_exists( $asset_path ) ) {
			$asset        = require $asset_path;
			$version      = is_array( $asset ) && isset( $asset['version'] ) ? $asset['version'] : $version;
			$dependencies = is_array( $asset ) && isset( $asset['dependencies'] ) ? $asset['dependencies'] : $dependencies;
		}

		wp_register_script(
			'wc-momo-sandbox-blocks-integration',
			WC_MOMO_SANDBOX_URL . 'assets/js/index.js',
			$dependencies,
			$version,
			true
		);

		return array( 'wc-momo-sandbox-blocks-integration' );
	}

	public function get_payment_method_data() {
		return array(
			'title'       => isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'MoMo Sandbox', 'wc-momo-sandbox' ),
			'description' => isset( $this->settings['description'] ) ? $this->settings['description'] : '',
			'supports'    => $this->get_supported_features(),
		);
	}

	public function get_supported_features() {
		$payment_gateways = WC()->payment_gateways()->payment_gateways();
		return isset( $payment_gateways['momo_sandbox'] ) ? $payment_gateways['momo_sandbox']->supports : array( 'products' );
	}
}
