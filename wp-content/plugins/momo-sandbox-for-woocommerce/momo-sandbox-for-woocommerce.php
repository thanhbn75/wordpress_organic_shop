<?php
/**
 * Plugin Name: MoMo Sandbox for WooCommerce
 * Plugin URI: https://developers.momo.vn/
 * Description: Automatic WooCommerce payment gateway using MoMo sandbox.
 * Author: Organic Shop
 * Version: 1.0.0
 * Text Domain: wc-momo-sandbox
 * Requires Plugins: woocommerce
 * WC requires at least: 6.0
 * WC tested up to: 9.0
 */

defined( 'ABSPATH' ) || exit;

define( 'WC_MOMO_SANDBOX_VERSION', '1.0.0' );
define( 'WC_MOMO_SANDBOX_FILE', __FILE__ );
define( 'WC_MOMO_SANDBOX_PATH', plugin_dir_path( __FILE__ ) );
define( 'WC_MOMO_SANDBOX_URL', plugin_dir_url( __FILE__ ) );

add_action( 'before_woocommerce_init', 'wc_momo_sandbox_declare_compatibility' );
function wc_momo_sandbox_declare_compatibility() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
}

add_action( 'plugins_loaded', 'wc_momo_sandbox_init', 11 );
function wc_momo_sandbox_init() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	require_once WC_MOMO_SANDBOX_PATH . 'includes/class-wc-gateway-momo-sandbox.php';
}

add_filter( 'woocommerce_payment_gateways', 'wc_momo_sandbox_add_gateway' );
function wc_momo_sandbox_add_gateway( $gateways ) {
	$gateways[] = 'WC_Gateway_MoMo_Sandbox';
	return $gateways;
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_momo_sandbox_action_links' );
function wc_momo_sandbox_action_links( $links ) {
	$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=momo_sandbox' ) ) . '">' . esc_html__( 'Settings', 'wc-momo-sandbox' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}

add_action( 'woocommerce_blocks_loaded', 'wc_momo_sandbox_blocks_support' );
function wc_momo_sandbox_blocks_support() {
	if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
		require_once WC_MOMO_SANDBOX_PATH . 'includes/class-wc-momo-sandbox-blocks-support.php';

		add_action(
			'woocommerce_blocks_payment_method_type_registration',
			function ( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
				$payment_method_registry->register( new WC_MoMo_Sandbox_Blocks_Support() );
			}
		);
	}
}
