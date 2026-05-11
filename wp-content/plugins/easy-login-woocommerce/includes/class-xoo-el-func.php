<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class Xoo_El_Func{

	private static $_instance = null;

	public $glSettings;

	public static function get_instance(){

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct(){
		$this->glSettings = xoo_el_helper()->get_general_option();
		$this->hooks();
	}

	public function hooks(){

		if( class_exists('wfWAF') && apply_filters( 'xoo_el_wordfence_support', true ) ){

			
		}


		add_action( 'xoo_el_login_add_fields', array( $this, 'custom_login_fields' ) );
		add_action( 'xoo_el_single_add_fields', array( $this, 'custom_single_fields' ) );

		add_filter( 'woocommerce_email_classes', array( $this, 'register_wc_resetpw_email' ) );

	}


	public function custom_login_fields( $args ){

		if( isset( $args['forms'] ) && isset( $args['forms']['single'] ) && $args['forms']['single']['enable'] === "yes" ){
			?>
			<input type="hidden" name="_xoo_el_login_has_single" value="yes">
			<?php
		}

	}

	public function custom_single_fields( $args ){
		if( isset( $args['forms'] ) && isset( $args['forms']['register'] ) && $args['forms']['register']['enable'] === "yes" ){
			?>
			<input type="hidden" name="_xoo_el_login_has_register" value="yes">
			<?php
		}
	}


	public function register_wc_resetpw_email( $emails ){
		$emails[ 'xoo_el_wc_reset_password' ] = include XOO_EL_PATH.'/includes/emails/class-xoo-el-wc-reset-password.php';
		return $emails;
	}


}


function xoo_el_func(){
	return Xoo_El_Func::get_instance();
}
xoo_el_func();
