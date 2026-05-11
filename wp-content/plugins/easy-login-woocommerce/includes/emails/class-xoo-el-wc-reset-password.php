<?php
/**
 * Class WC_Email_Customer_Reset_Password file.
 *
 * @package WooCommerce\Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !class_exists('Xoo_EL_WC_Reset_Password') ){
	class Xoo_EL_WC_Reset_Password extends WC_Email {

		/**
		 * User ID.
		 *
		 * @var integer
		 */
		public $user_id;

		/**
		 * User login name.
		 *
		 * @var string
		 */
		public $user_login;

		/**
		 * User email.
		 *
		 * @var string
		 */
		public $user_email;

		public $codeForm;

		public $email_group;


		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id             = 'xoo_el_wc_reset_password';
			$this->customer_email = true;

			$this->title       = '[Login/Signup popup] Reset Passsword';
			$this->description = 'Send an email to customers notifying them that their password has been reset';

			$this->email_group = 'accounts';

			// Call parent constructor.
			parent::__construct();
		}

		/**
		 * Get email subject.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_subject() {
			return xoo_el_helper()->get_general_option('m-reset-pw-subject');
		}

		public function get_subject() {
			return $this->codeForm->get_email_subject();
		}

		/**
		 * Get email heading.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Reset your password', 'easy-login-woocommerce' );
				
		}

		/**
		 * Trigger.
		 *
		 * @param string $user_login User login.
		 * @param string $reset_key Password reset key.
		 */
		public function trigger( $codeForm ) {

			$this->object     	= $codeForm->object;
			$this->codeForm 	= $codeForm;

			$this->setup_locale();

			$this->user_id    = $this->object->ID;
			$this->user_login = $this->object->user_login;
			$this->user_email = $this->codeForm->get_email_recipient();
			$this->recipient  = $this->user_email;

			if ( $this->is_enabled() && $this->get_recipient() ) {
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}

			$this->restore_locale();
		}

		/**
		 * Get content html.
		 *
		 * @return string
		 */
		public function get_content_html() {

			$this->xoo_admin_preview_setup();

			return xoo_el_helper()->get_template( '/emails/xoo-el-wc-reset-password.php', array(
				'email_heading'      	=> $this->get_heading(),
				'additional_content' 	=> $this->get_additional_content(),
				'email'              	=> $this,
				'email_text' 			=> $this->codeForm->get_email_text()
			), '', true );
		}

		/**
		 * Get content plain.
		 *
		 * @return string
		 */
		public function get_content_plain() {
			return $this->get_content_html();
		}


		/**
		 * Default content to show below main email content.
		 *
		 * @since 3.7.0
		 * @return string
		 */
		public function get_default_additional_content() {
			return '';
		}


		public function xoo_admin_preview_setup(){
			if( is_admin() && (!$this->object || !($this->object instanceof WP_User ) ) ){

				$object                  = new WP_User( 0 );
				$object->user_email      = 'user_preview@example.com';
				$object->user_login      = 'user_preview';
				$object->first_name      = 'John';
				$object->last_name       = 'Doe';
				$this->user_email = $object->user_email;
				$this->user_login = $object->user_login;

				$this->codeForm 			= xoo_el_code_forms()->forms['reset_password'];
				$this->codeForm->object 	= $object;
				$this->codeForm->code 		= '000000';

				if ( property_exists( $this, 'user_id' ) ) {
					$this->user_id = 0;
				}

				$this->set_object( $object );
			}

		}
	}
}

return new Xoo_EL_WC_Reset_Password();
