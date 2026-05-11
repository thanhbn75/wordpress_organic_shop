<?php


class Xoo_El_Code_Forms{

	protected static $_instance = null;

	public $forms = array();

	public static function get_instance(){
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct(){
		$this->init();
	}

	public function init(){

		include_once XOO_EL_PATH.'/includes/verification/class-xoo-el-code-form.php';

		if( xoo_el_helper()->get_general_option('m-reset-pw') === 'code' ){
			$this->forms['reset_password'] = include_once XOO_EL_PATH.'/includes/verification/class-xoo-el-code-form-resetpw.php';
		}

		$this->forms = apply_filters( 'xoo_el_code_verification_forms', $this->forms );


	}
}

function xoo_el_code_forms(){
	return Xoo_El_Code_Forms::get_instance();
}

add_action( 'init', 'xoo_el_code_forms' );

?>