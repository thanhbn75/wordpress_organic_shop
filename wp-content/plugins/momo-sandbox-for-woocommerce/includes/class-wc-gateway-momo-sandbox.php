<?php

defined( 'ABSPATH' ) || exit;

class WC_Gateway_MoMo_Sandbox extends WC_Payment_Gateway {
	const DEFAULT_ENDPOINT = 'https://test-payment.momo.vn/v2/gateway/api/create';

	public function __construct() {
		$this->id                 = 'momo_sandbox';
		$this->method_title       = __( 'MoMo Sandbox', 'wc-momo-sandbox' );
		$this->method_description = __( 'Pay automatically through the MoMo sandbox gateway for WooCommerce testing.', 'wc-momo-sandbox' );
		$this->has_fields         = false;
		$this->supports           = array( 'products' );

		$this->init_form_fields();
		$this->init_settings();

		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->enabled     = $this->get_option( 'enabled' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_api_wc_gateway_momo_sandbox', array( $this, 'handle_momo_callback' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'      => array(
				'title'   => __( 'Enable/Disable', 'woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable MoMo sandbox payment', 'wc-momo-sandbox' ),
				'default' => 'yes',
			),
			'title'        => array(
				'title'       => __( 'Title', 'woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Payment method title shown to customers during checkout.', 'woocommerce' ),
				'default'     => __( 'MoMo Sandbox', 'wc-momo-sandbox' ),
				'desc_tip'    => true,
			),
			'description'  => array(
				'title'       => __( 'Description', 'woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description shown to customers during checkout.', 'woocommerce' ),
				'default'     => __( 'Pay with MoMo test wallet. Orders are completed automatically after MoMo sends a valid payment result.', 'wc-momo-sandbox' ),
				'desc_tip'    => true,
			),
			'partner_code' => array(
				'title'       => __( 'Partner Code', 'wc-momo-sandbox' ),
				'type'        => 'text',
				'default'     => 'MOMOBKUN20180529',
				'description' => __( 'MoMo sandbox partnerCode.', 'wc-momo-sandbox' ),
			),
			'access_key'   => array(
				'title'       => __( 'Access Key', 'wc-momo-sandbox' ),
				'type'        => 'text',
				'default'     => 'klm05TvNBzhg7h7j',
				'description' => __( 'MoMo sandbox accessKey.', 'wc-momo-sandbox' ),
			),
			'secret_key'   => array(
				'title'       => __( 'Secret Key', 'wc-momo-sandbox' ),
				'type'        => 'password',
				'default'     => 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa',
				'description' => __( 'MoMo sandbox secretKey used to sign and verify HMAC SHA256 signatures.', 'wc-momo-sandbox' ),
			),
			'endpoint'     => array(
				'title'       => __( 'Create Payment Endpoint', 'wc-momo-sandbox' ),
				'type'        => 'text',
				'default'     => self::DEFAULT_ENDPOINT,
				'description' => __( 'Default sandbox endpoint: https://test-payment.momo.vn/v2/gateway/api/create', 'wc-momo-sandbox' ),
			),
			'order_prefix' => array(
				'title'       => __( 'Order ID Prefix', 'wc-momo-sandbox' ),
				'type'        => 'text',
				'default'     => 'WC',
				'description' => __( 'Only letters and numbers are used. Example MoMo orderId: WC123-1710000000.', 'wc-momo-sandbox' ),
			),
			'debug'        => array(
				'title'   => __( 'Debug log', 'woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Log MoMo requests and callbacks in WooCommerce logs', 'wc-momo-sandbox' ),
				'default' => 'yes',
			),
		);
	}

	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			wc_add_notice( __( 'Order not found.', 'woocommerce' ), 'error' );
			return array( 'result' => 'failure' );
		}

		$amount = $this->get_momo_amount( $order );
		if ( $amount < 1000 ) {
			wc_add_notice( __( 'MoMo sandbox requires a minimum payment amount of 1,000 VND.', 'wc-momo-sandbox' ), 'error' );
			return array( 'result' => 'failure' );
		}

		$response = $this->create_payment( $order, $amount );
		if ( is_wp_error( $response ) ) {
			$this->log( 'Create payment failed: ' . $response->get_error_message(), 'error' );
			wc_add_notice( __( 'Could not connect to MoMo. Please try again.', 'wc-momo-sandbox' ), 'error' );
			return array( 'result' => 'failure' );
		}

		$result_code = isset( $response['resultCode'] ) ? (int) $response['resultCode'] : null;
		$pay_url     = isset( $response['payUrl'] ) ? esc_url_raw( $response['payUrl'] ) : '';

		if ( 0 !== $result_code || empty( $pay_url ) ) {
			$message = isset( $response['message'] ) ? wc_clean( $response['message'] ) : __( 'MoMo did not return a payment URL.', 'wc-momo-sandbox' );
			$this->log( 'Create payment rejected: ' . wp_json_encode( $response ), 'error' );
			wc_add_notice( sprintf( __( 'MoMo error: %s', 'wc-momo-sandbox' ), $message ), 'error' );
			return array( 'result' => 'failure' );
		}

		$order->update_status( 'pending', __( 'Awaiting MoMo sandbox payment.', 'wc-momo-sandbox' ) );
		WC()->cart->empty_cart();

		return array(
			'result'   => 'success',
			'redirect' => $pay_url,
		);
	}

	private function create_payment( WC_Order $order, $amount ) {
		$partner_code = trim( $this->get_option( 'partner_code' ) );
		$access_key   = trim( $this->get_option( 'access_key' ) );
		$secret_key   = trim( $this->get_option( 'secret_key' ) );
		$request_type = 'captureWallet';
		$order_id     = $this->build_momo_order_id( $order );
		$request_id   = $order_id . '-' . wp_rand( 1000, 9999 );
		$order_info   = sprintf( 'Thanh toan don hang #%s tai %s', $order->get_order_number(), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
		$callback_url = WC()->api_request_url( 'wc_gateway_momo_sandbox' );
		$extra_data   = base64_encode( wp_json_encode( array( 'wc_order_id' => $order->get_id() ) ) );

		$raw_signature = $this->build_create_signature_string(
			array(
				'accessKey'   => $access_key,
				'amount'      => $amount,
				'extraData'   => $extra_data,
				'ipnUrl'      => $callback_url,
				'orderId'     => $order_id,
				'orderInfo'   => $order_info,
				'partnerCode' => $partner_code,
				'redirectUrl' => $callback_url,
				'requestId'   => $request_id,
				'requestType' => $request_type,
			)
		);

		$payload = array(
			'partnerCode' => $partner_code,
			'partnerName' => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
			'storeId'     => sanitize_key( wp_parse_url( home_url(), PHP_URL_HOST ) ),
			'requestId'   => $request_id,
			'amount'      => (string) $amount,
			'orderId'     => $order_id,
			'orderInfo'   => $order_info,
			'redirectUrl' => $callback_url,
			'ipnUrl'      => $callback_url,
			'lang'        => 'vi',
			'extraData'   => $extra_data,
			'requestType' => $request_type,
			'signature'   => hash_hmac( 'sha256', $raw_signature, $secret_key ),
		);

		$order->update_meta_data( '_momo_sandbox_order_id', $order_id );
		$order->update_meta_data( '_momo_sandbox_request_id', $request_id );
		$order->save();

		$this->log( 'Create payment payload: ' . wp_json_encode( $this->mask_payload( $payload ) ) );

		$response = wp_remote_post(
			esc_url_raw( $this->get_option( 'endpoint', self::DEFAULT_ENDPOINT ) ),
			array(
				'body'        => wp_json_encode( $payload ),
				'headers'     => array( 'Content-Type' => 'application/json' ),
				'timeout'     => 30,
				'data_format' => 'body',
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		return is_array( $body ) ? $body : new WP_Error( 'momo_invalid_response', __( 'Invalid MoMo response.', 'wc-momo-sandbox' ) );
	}

	public function handle_momo_callback() {
		$data     = $this->get_callback_data();
		$order_id = $this->extract_wc_order_id( isset( $data['orderId'] ) ? $data['orderId'] : '' );
		$order    = $order_id ? wc_get_order( $order_id ) : false;
		$method   = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : 'GET';
		$is_ipn   = 'POST' === $method;

		if ( ! $order ) {
			$this->respond_callback( array( 'message' => 'Order not found' ), 404 );
		}

		if ( ! $this->is_valid_callback_for_order( $data, $order ) ) {
			$order->add_order_note( __( 'MoMo callback rejected because the signature or order id is invalid.', 'wc-momo-sandbox' ) );
			$this->respond_callback( array( 'message' => 'Invalid signature' ), 400, $is_ipn ? null : $this->get_return_url( $order ) );
		}

		$result_code = isset( $data['resultCode'] ) ? (int) $data['resultCode'] : -1;
		$message     = isset( $data['message'] ) ? wc_clean( $data['message'] ) : '';
		$trans_id    = isset( $data['transId'] ) ? wc_clean( $data['transId'] ) : '';

		if ( 0 === $result_code ) {
			if ( ! $order->is_paid() ) {
				$order->payment_complete( $trans_id );
				$order->add_order_note( sprintf( __( 'MoMo sandbox payment completed. Transaction ID: %s', 'wc-momo-sandbox' ), $trans_id ) );
			}
		} elseif ( ! in_array( $order->get_status(), array( 'cancelled', 'failed' ), true ) ) {
			$order->update_status( 'failed', sprintf( __( 'MoMo sandbox payment failed: %s', 'wc-momo-sandbox' ), $message ) );
		}

		$order->update_meta_data( '_momo_sandbox_trans_id', $trans_id );
		$order->update_meta_data( '_momo_sandbox_result_code', $result_code );
		$order->save();

		$this->respond_callback( array( 'message' => 'Received payment result success' ), 200, $is_ipn ? null : $this->get_return_url( $order ) );
	}

	private function respond_callback( $response, $status_code, $redirect = null ) {
		if ( is_string( $redirect ) && '' !== $redirect ) {
			wp_safe_redirect( $redirect );
			exit;
		}

		wp_send_json( $response, $status_code );
	}

	private function get_callback_data() {
		if ( 'POST' === ( isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '' ) ) {
			$raw_body = file_get_contents( 'php://input' );
			$json     = json_decode( $raw_body, true );
			if ( is_array( $json ) ) {
				return wc_clean( wp_unslash( $json ) );
			}
		}

		return wc_clean( wp_unslash( $_REQUEST ) );
	}

	private function is_valid_callback_for_order( $data, WC_Order $order ) {
		if ( empty( $data['orderId'] ) || $data['orderId'] !== $order->get_meta( '_momo_sandbox_order_id' ) ) {
			return false;
		}

		if ( empty( $data['signature'] ) ) {
			return false;
		}

		$raw_signature = $this->build_callback_signature_string(
			array(
				'accessKey'    => trim( $this->get_option( 'access_key' ) ),
				'amount'       => isset( $data['amount'] ) ? $data['amount'] : '',
				'extraData'    => isset( $data['extraData'] ) ? $data['extraData'] : '',
				'message'      => isset( $data['message'] ) ? $data['message'] : '',
				'orderId'      => isset( $data['orderId'] ) ? $data['orderId'] : '',
				'orderInfo'    => isset( $data['orderInfo'] ) ? $data['orderInfo'] : '',
				'orderType'    => isset( $data['orderType'] ) ? $data['orderType'] : '',
				'partnerCode'  => isset( $data['partnerCode'] ) ? $data['partnerCode'] : '',
				'payType'      => isset( $data['payType'] ) ? $data['payType'] : '',
				'requestId'    => isset( $data['requestId'] ) ? $data['requestId'] : '',
				'responseTime' => isset( $data['responseTime'] ) ? $data['responseTime'] : '',
				'resultCode'   => isset( $data['resultCode'] ) ? $data['resultCode'] : '',
				'transId'      => isset( $data['transId'] ) ? $data['transId'] : '',
			)
		);

		$expected = hash_hmac( 'sha256', $raw_signature, trim( $this->get_option( 'secret_key' ) ) );
		return hash_equals( $expected, (string) $data['signature'] );
	}

	private function build_create_signature_string( $data ) {
		return 'accessKey=' . $data['accessKey'] . '&amount=' . $data['amount'] . '&extraData=' . $data['extraData'] . '&ipnUrl=' . $data['ipnUrl'] . '&orderId=' . $data['orderId'] . '&orderInfo=' . $data['orderInfo'] . '&partnerCode=' . $data['partnerCode'] . '&redirectUrl=' . $data['redirectUrl'] . '&requestId=' . $data['requestId'] . '&requestType=' . $data['requestType'];
	}

	private function build_callback_signature_string( $data ) {
		return 'accessKey=' . $data['accessKey'] . '&amount=' . $data['amount'] . '&extraData=' . $data['extraData'] . '&message=' . $data['message'] . '&orderId=' . $data['orderId'] . '&orderInfo=' . $data['orderInfo'] . '&orderType=' . $data['orderType'] . '&partnerCode=' . $data['partnerCode'] . '&payType=' . $data['payType'] . '&requestId=' . $data['requestId'] . '&responseTime=' . $data['responseTime'] . '&resultCode=' . $data['resultCode'] . '&transId=' . $data['transId'];
	}

	private function build_momo_order_id( WC_Order $order ) {
		$prefix = preg_replace( '/[^A-Za-z0-9]/', '', $this->get_option( 'order_prefix', 'WC' ) );
		$prefix = $prefix ? $prefix : 'WC';
		return $prefix . $order->get_id() . '-' . time();
	}

	private function extract_wc_order_id( $momo_order_id ) {
		$prefix = preg_replace( '/[^A-Za-z0-9]/', '', $this->get_option( 'order_prefix', 'WC' ) );
		$prefix = $prefix ? $prefix : 'WC';
		$regex  = '/^' . preg_quote( $prefix, '/' ) . '([0-9]+)-[0-9]+$/';

		if ( preg_match( $regex, (string) $momo_order_id, $matches ) ) {
			return absint( $matches[1] );
		}

		return 0;
	}

	private function get_momo_amount( WC_Order $order ) {
		return (int) round( (float) $order->get_total(), 0 );
	}

	public function thankyou_page( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( $order && $order->is_paid() ) {
			echo '<p>' . esc_html__( 'MoMo payment has been confirmed automatically.', 'wc-momo-sandbox' ) . '</p>';
		} else {
			echo '<p>' . esc_html__( 'We are waiting for MoMo to confirm your sandbox payment.', 'wc-momo-sandbox' ) . '</p>';
		}
	}

	private function mask_payload( $payload ) {
		if ( isset( $payload['signature'] ) ) {
			$payload['signature'] = '***';
		}
		return $payload;
	}

	private function log( $message, $level = 'info' ) {
		if ( 'yes' !== $this->get_option( 'debug', 'yes' ) ) {
			return;
		}

		wc_get_logger()->log( $level, $message, array( 'source' => 'momo-sandbox' ) );
	}
}
