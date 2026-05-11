<?php
/**
 * Send Usage Task class.
 *
 * @package Merchant
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Merchant_Send_Usage_Task class.
 */
class Merchant_Send_Usage_Task {

	/**
	 * Action name for this task.
	 *
	 * @since 2.2.0
	 */
	const ACTION = 'merchant_send_usage_data';

	/**
	 * Server URL to send requests to.
	 *
	 * @since 2.2.0
	 */
	const TRACK_URL = 'https://athemesusage.com/merchant/v1/track';

	/**
	 * Option name to store the timestamp of the last run.
	 *
	 * @since 2.2.0
	 */
	const LAST_RUN = 'merchant_send_usage_last_run';

	/**
	 * Initialize the task.
	 *
	 * @since 2.2.0
	 */
	public function init() {

		$this->hooks();
	}

	/**
	 * Attach hooks to the WordPress API.
	 *
	 * @since 2.2.0
	 */
	public function hooks() {
		
		// Register the action handler.
		add_action( self::ACTION, array( $this, 'process' ) );
	}

	/**
	 * Send the actual data in a POST request.
	 *
	 * @since 2.2.0
	 */
	public function process() {

		$last_run = get_option( self::LAST_RUN );

		// Make sure we do not run it more than once a day.
		if (
			$last_run !== false &&
			( time() - $last_run ) < DAY_IN_SECONDS
		) {
			return;
		}

		// Send data to the usage tracking API.
		$ut = new Merchant_Usage_Tracking();

		$response = wp_remote_post(
			self::TRACK_URL,
			array(
				'timeout'     => 5,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking'    => true,
				'body'        => $ut->get_data(),
				'user-agent'  => $ut->get_user_agent(),
			)
		);

		// Update the last run option to the current timestamp.
		update_option( self::LAST_RUN, time(), false );

		/**
		 * Action fired after usage data is sent.
		 *
		 * @param array|WP_Error $response The response from the API.
		 *
		 * @since 2.2.0
		 */
		do_action( 'merchant_usage_tracking_sent', $response );
	}
}

