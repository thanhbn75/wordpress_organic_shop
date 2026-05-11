<?php

class Xoo_El_Code_Form_Resetpw extends Xoo_El_Code_Form {

	public $maskEmail = false;


    public function __construct() {
        $this->form_id = 'reset_password';
        $this->parentFormSelector = 'xoo-el-form-lostpw';
        $this->codeFormArgs['resend'] = false;
        parent::__construct();
    }


    public function request_code( $user ){

    	$this->object = $user;

		$sent = $this->sendEmailCode( $user->user_email );

		if( is_wp_error( $sent ) ){
			return $sent;
		}

		$email_display = $this->maskEmail ? $this->mask_email( $user->user_email ) : $user->user_email;

		return array(
			'code_txt' =>  sprintf( __( 'Please enter the code sent to <br> %s', 'easy-login-woocommerce' ), $email_display ),
		);
		
   		exit;

    }


	public function triggerCodeEmail(){
		if( class_exists('woocommerce') && apply_filters( 'xoo_el_reset_password_email_template', 'woocommerce' ) === 'woocommerce' ){
			WC_Emails::instance()->emails['xoo_el_wc_reset_password']->trigger($this);
		}
		else{
			xoo_el_helper()->send_email(
				'reset_password',
				$this->get_email_recipient(),
				$this->get_email_subject(),
				$this->get_email_text(),
				$headers = array(),
				$attachments = array(),
				get_user_locale( $this->object )
			);

		}
	}

	public function get_email_recipient(){
		return stripslashes( $this->object->user_email );
	}


	public function get_email_text(){

		$email_text 	= $this->parse_placeholders( self::$glSettings['m-reset-pw-email'] );

 		return xoo_el_helper()->get_template( '/emails/xoo-el-basic-email.php', array('email_text' => $email_text), '', true );

	}

	public function parse_placeholders( $text ){

		$user = $this->object;

		$placeholders = array(
 			'{verify_code}' 	=> $this->code,
 			'{user_login}' 		=> $user->user_login,
 			'{user_firstname}' 	=> $user->first_name,
 			'{site_title}' 		=> get_bloginfo('name')
 		);

 		return xoo_el_helper()->parsePlaceHolders( $text, $placeholders );

	}


	public function get_email_subject(){
		return $this->parse_placeholders( xoo_el_helper()->get_general_option('m-reset-pw-subject') );
	}

}

return new Xoo_El_Code_Form_Resetpw();

