<?php

if( class_exists( 'Xoo_Aff_fields' ) ){

	class Xoo_El_Aff_Fields{

		public $fields;

		public function __construct(){
			add_action( 'xoo_aff_easy-login-woocommerce_add_predefined_fields', array( $this, 'add_el_predefined_fields' ) );
			add_filter( 'xoo_aff_easy-login-woocommerce_before_fields_update', array( $this, 'manage_password_field' ) );
			add_filter( 'xoo_aff_easy-login-woocommerce_default_field_settings', array( $this, 'modify_default_field_settings' ) );
			add_filter( 'xoo_aff_easy-login-woocommerce_field_setting_options', array( $this, 'add_custom_settings_option' ) );
			add_filter( 'xoo_aff_easy-login-woocommerce_default_field_types', array( $this, 'autocomplete_address_text_filter' ) );

			add_action( 'xoo_aff_easy-login-woocommerce_add_predefined_fields', array( $this, 'group_otp_login_fields' ), 999 );

		}

		public function group_otp_login_fields(){

			$fields = xoo_el()->aff->fields->get_fields_data();

			if( !empty( $fields ) ){

				$groups = array(
					'xoo-el-username' 			=> 'login',
					'xoo-el-password' 			=> 'login',
					'user_login' 				=> 'resetpw',
					'xoo-el-rp-pass' 			=> 'resetpw',
					'xoo-el-rp-pass-again' 		=> 'resetpw',
					'xoo-el-sing-user' 			=> 'others',
				);


				foreach ( $fields as $field_id => $data ) {
					$fields[ $field_id ]['group'] = isset( $groups[ $field_id ] ) ? $groups[$field_id] : 'register';
				}

				xoo_el()->aff->fields->update_field_option( $fields );

			}

		}


		public function autocomplete_address_text_filter( $types ){

			if( !defined('XOO_ELACDR_VERSION') ){
				$types['xoo_aff_autocomplete_address'][2] = $types['xoo_aff_autocomplete_address'][2].' ( requires add-on ) ';
			}

			return $types;
			
		}


		public function manage_password_field( $fields ){
			
			if( $fields[ 'xoo_el_reg_pass' ]['settings']['active'] === "no" ){
				$fields['xoo_el_reg_pass_again']['settings']['active'] = "no";
			}
			return $fields;

		}


		public function add_el_predefined_fields(){

			$this->fields = xoo_el()->aff->fields;

			$this->fields->add_group( 'login', 'Login', false, 10 );
			$this->fields->add_group( 'register', 'Register', true, 20 );
			$this->fields->add_group( 'resetpw', 'Reset Password', false, 30 );

			$this->predefined_field_username();
			$this->predefined_field_userrole();
			$this->predefined_field_useremail();
			$this->predefined_field_firstname();
			$this->predefined_field_lastname();
			$this->predefined_field_userpassword();
			$this->predefined_field_userpasswordagain();
			
			$this->predefined_field_terms();
			$this->login_predefined_field_useremail();
			$this->login_predefined_field_userpassword();
			$this->lostpw_predefined_field_useremail();
			$this->resetpw_predefined_field_password();
			$this->resetpw_predefined_field_passwordagain();
			$this->predefined_field_single();


			$this->predefined_mailchimp_subscribe();
			$this->predfined_mc4wp_subscribe();
			$this->predfined_mailpoet_subscribe();
		}


		


		public function predefined_field_username(){

			$field_type_id = $field_id = 'xoo_el_reg_username';

			$this->fields->add_type(
				$field_type_id,
				'text',
				'Register',
				array(
					'is_selectable' => 'no',
					'can_delete'	=> 'no',
					'icon' 			=> 'fas fa-user',
				)
			);
				
			$setting_options = array(
				'active' => array(
					'value' => 'no'
				),
				'label',
				'cols',
				'icon' => array(
					'value' => 'fas fa-user-plus'
				),
				'placeholder' => array(
					'value' => 'Username',
				),
				'minlength' => array(
					'value' => 3,
				),
				'maxlength' => array(
					'value' => 20,
				),
				'unique_id' => array(
					'disabled' => 'disabled',
				),
				'class'
			);

			$this->fields->create_field_settings(
				$field_type_id,
				$setting_options
			);

			$this->fields->add_field(
				$field_id,
				$field_type_id,
				array(
					'unique_id' => $field_id,
					'required' 	=> 'yes',
				),
				array(
					'group' 	=> 'register',
					'priority' 	=> 100,
				)
			);

		}


		public function predefined_field_userrole(){

			global $wp_roles;

			$field_type_id = $field_id = 'xoo_el_reg_userrole';

			if (!isset($wp_roles)) {
				$wp_roles = new WP_Roles();
			}

			$user_roles = array();

			foreach ( $wp_roles->roles as $role_id => $role_data ) {

				if( $role_id === 'administrator' ) continue;

			 	$user_roles[$role_id] = array(
			 		'label' 	=> $role_data['name'],
			 		'value' 	=> $role_id,
			 		'checked' 	=> '',
			 	);
			}

			$this->fields->add_type(
				$field_type_id,
				'select_list',
				'Register',
				array(
					'is_selectable' => 'no',
					'can_delete'	=> 'no',
					'icon' 			=> 'fas fa-user',
				)
			);
				
			$setting_options = array(
				'active' => array(
					'value' => 'no'
				),
				'required',
				'show_label', 
				'label',
				'cols',
				'icon' => array(
					'value' => 'far fa-user'
				),
				'use_select2',
				'placeholder' => array(
					'value' => 'User Role',
				),
				'select_list' => array(
					'value' => $user_roles
				),
				'unique_id' => array(
					'disabled' => 'disabled',
				),
				'class'
			);


			$this->fields->create_field_settings(
				$field_type_id,
				$setting_options
			);

			$this->fields->add_field(
				$field_id,
				$field_type_id,
				array(
					'unique_id' => $field_id,
					'required' 	=> 'yes',
				),
				array(
					'group' 	=> 'register',
					'priority' 	=> 200,
				)
			);

		}
		
		public function predefined_field_useremail(){

			$field_type_id = $field_id = 'xoo_el_reg_email';

			$this->fields->add_type(
				$field_type_id,
				'email',
				'Register',
				array(
					'is_selectable' => 'no',
					'can_delete'	=> 'no',
					'icon' 			=> 'far fa-envelope',
				)
			);

			$setting_options = array(	
				'active' => array(
					'value' => 'yes'
				),
				'required' 	=> array(
					'value' => 'yes'
				),
				'label',
				'cols',
				'icon' => array(
					'value' => 'far fa-envelope'
				),
				'placeholder' => array(
					'value' => 'Email',
				),
				'unique_id' => array(
					'disabled' => 'disabled',
				),
				'class'
			);

			$this->fields->create_field_settings(
				$field_type_id,
				$setting_options
			);

			$this->fields->add_field(
				$field_id,
				$field_type_id,
				array(
					'unique_id' => $field_id,
				),
				array(
					'group' 	=> 'register',
					'priority' 	=> 300,
				)			
			);

		}

		public function predefined_field_userpassword(){

			$field_type_id = $field_id = 'xoo_el_reg_pass';

			$this->fields->add_type(
				$field_type_id,
				'password',
				'Register',
				array(
					'is_selectable' => 'no',
					'can_delete'	=> 'no',
					'icon' 			=> 'fas fa-lock'
				)
			);

			$setting_options = array(
				'label',
				'cols',
				'icon' => array(
					'value' => 'fas fa-lock'
				),
				'placeholder' => array(
					'value' => 'Password',
				),
				'minlength' => array(
					'value' => 6,
				),
				'maxlength' => array(
					'value' => 20,
				),
				'password_visibility',
				'unique_id' => array(
					'disabled' => 'disabled',
				),
				'class'
			);


			$settings_value = array(
				'active' 	=> 'yes',
				'required' 	=> 'yes',
				'unique_id' 	=> $field_id,
			);

			$active_setting = array(
				'active' => array(
					'info' => 'If disabled, a password will be sent to user email address.'
				)
			);

			$setting_options = array_merge( $active_setting, $setting_options );

			unset( $settings_value['active'] );

			$this->fields->create_field_settings(
				$field_type_id,
				$setting_options
			);

			$this->fields->add_field(
				$field_type_id,
				$field_id,
				$settings_value,
				array(
					'group' 	=> 'register',
					'priority' 	=> 400,
				)
			);

		}

		public function predefined_field_userpasswordagain(){

			$field_type_id = $field_id = 'xoo_el_reg_pass_again';

			$this->fields->add_type(
				$field_type_id,
				'password',
				'Register',
				array(
					'is_selectable' => 'no',
					'can_delete'	=> 'no',
					'icon' 			=> 'fas fa-lock'
				)
			);

			$setting_options = array(
				'active',
				'label',
				'cols',
				'icon' => array(
					'value' => 'fas fa-lock'
				),
				'placeholder' => array(
					'value' => 'Confirm Password',
				),
				'password_visibility',
				'unique_id' => array(	
					'disabled' => 'disabled',
				),
				'class'
			);

			$this->fields->create_field_settings(
				$field_type_id,
				$setting_options
			);

			$this->fields->add_field(
				$field_id,
				$field_type_id,
				array(
					'required' 	=> 'yes',
					'unique_id' 	=> $field_id,
				),
				array(
					'group' 	=> 'register',
					'priority' 	=> 500,
				)	
			);

		}


		public function predefined_field_firstname(){

			$field_type_id = $field_id = 'xoo_el_reg_fname';

			$this->fields->add_type(
				$field_type_id,
				'text',
				'Register',
				array(
					'is_selectable' => 'no',
					'can_delete'	=> 'no',
					'icon' 			=> 'fas fa-font',
				)
			);

			$setting_options = array(
				'active',
				'required' => array(				
					'value' => 'yes'
				),
				'label',
				'cols' => array(
					'value' => 'onehalf'
				),
				'icon' => array(
					'value' => 'far fa-user'
				),
				'placeholder' => array(
					'value' => 'First Name',
				),
				'minlength',
				'maxlength',
				'unique_id' => array(
					'disabled' => 'disabled',
				),
				'class'
			);

			$this->merge_with_wc_fields_setting_option( $field_type_id );

			$this->fields->create_field_settings(
				$field_type_id,
				$setting_options
			);

			$this->fields->add_field(
				$field_id,
				$field_type_id,
				array(
					'unique_id' => $field_id,
				),
				array(
					'group' 	=> 'register',
					'priority' 	=> 600,
				)
			);

		}


		public function predefined_field_lastname(){

			$field_type_id = $field_id = 'xoo_el_reg_lname';

			$this->fields->add_type(
				$field_type_id,
				'text',
				'Register',
				array(
					'is_selectable' => 'no',
					'can_delete'	=> 'no',
					'icon' 			=> 'fas fa-font',
				)
			);

			$setting_options = array(
				'active',
				'required' => array(
					'value' => 'yes'
				),
				'label',
				'cols' => array(
					'value' => 'onehalf'
				),
				'icon' => array(
					'value' => 'far fa-user'
				),
				'placeholder' => array(
					'value' => 'Last Name',
				),
				'minlength',
				'maxlength',
				'unique_id' => array(
					'disabled' => 'disabled',
				),
				'class'
			);

			$this->merge_with_wc_fields_setting_option( $field_type_id );

			$this->fields->create_field_settings(
				$field_type_id,
				$setting_options
			);

			$this->fields->add_field(
				$field_id,
				$field_type_id,
				array(
					'unique_id' => 'xoo_el_reg_lname',
				),
				array(
					'group' 	=> 'register',
					'priority' 	=> 700,
				)
			);

		}


		public function newsletter_field( $field_id, $args = array() ){

			$args = wp_parse_args( $args, array(
				'fieldTitle' => ''
			) );

			$field_type_id = $field_id;

			$this->fields->add_type(
				$field_type_id,
				'checkbox_single',
				'Register',
				array(
					'is_selectable' => 'no',
					'can_delete'	=> 'no',
					'icon' 			=> 'fas fa-envelope',
				)
			);

			$setting_options = array(				
				'active',
				'required' => array(
					'value' => 'no'
				),
				'label',
				'placeholder' => array(
					'value' => 'Subscribe to our newsletter',
				),
				'cols',
				'checkbox_single' => array(
					'value' 	=>array(
						'yes' => array(
							'value' 	=> 'yes',
							'label' 	=> 'Subscribe to our newsletter',
							'checked' 	=> false
						)
					)
				),
				'unique_id' => array(
					'disabled' => 'disabled',
				),
				'class'
			);

			$this->fields->create_field_settings(
				$field_type_id,
				$setting_options
			);

			$this->fields->add_field(
				$field_id,
				$field_type_id,
				array(
					'unique_id' => $field_id,
				),
				array(
					'group' 	=> 'register',
					'priority' 	=> 800,
				)
			);
		}


		public function predfined_mc4wp_subscribe(){

			if( !function_exists('run_mailchimp_woocommerce') ) return;

			$this->newsletter_field( 'mailchimp_woocommerce_newsletter', array(
				'fieldTitle' => 'Mailchimp Newsletter'
			) );

		}


		public function predefined_mailchimp_subscribe(){

			if( !function_exists('mc4wp') ) return;

			$this->newsletter_field( 'mc4wp-subscribe', array(
				'fieldTitle' => 'Mailchimp Newsletter (MC4WP)'
			) );

		}


		public function predfined_mailpoet_subscribe(){

			if( !defined('MAILPOET_VERSION') ) return;

			$this->newsletter_field( 'xoo-mailpoet-subscribe', array(
				'fieldTitle' => 'MailPoet Newsletter'
			) );

		}


		public function predefined_field_terms(){

			$field_type_id = $field_id = 'xoo_el_reg_terms';

			$this->fields->add_type(
				$field_type_id,
				'checkbox_single',
				'Register',
				array(
					'is_selectable' => 'no',
					'can_delete'	=> 'no',
					'icon' 			=> 'fas fa-check-square',
				)
			);


			$privacyLink = class_exists( 'woocommerce' ) && function_exists( 'wc_privacy_policy_page_id' ) && wc_privacy_policy_page_id() ? get_page_link( wc_privacy_policy_page_id() ) : 'privacy-policy';

			$setting_options = array(				
				'active',
				'required' => array(
					'value' => 'yes'
				),
				'label',
				'placeholder' => array(
					'value' => 'The Terms and Conditions',
				),
				'cols',
				'checkbox_single' => array(
					'value' 	=>array(
						'yes' => array(
							'value' 	=> 'yes',
							'label' 	=> 'I accept the <a href="'.$privacyLink.'" target="_blank"> Terms of Service and Privacy Policy </a>',
							'checked' 	=> false
						)
					)
				),
				'unique_id' => array(
					'disabled' => 'disabled',
				),
				'class'
			);

			$this->fields->create_field_settings(
				$field_type_id,
				$setting_options
			);

			$this->fields->add_field(
				$field_id,
				$field_type_id,
				array(
					'unique_id' => $field_id,
				),
				array(
					'group' 	=> 'register',
					'priority' 	=> 700,
				)
			);

		}


		public function login_predefined_field_useremail(){

			$field_type_id = $field_id = 'xoo-el-username';

			$this->fields->add_type(
				$field_type_id,
				'text',
				'Login',
				array(
					'is_selectable' => 'no',
					'can_delete'	=> 'no',
					'is_sortable' 	=> 'no',
					'icon' 			=> 'fas fa-user',
				)
			);
				
			$setting_options = array(
				'label',
				'cols',
				'icon' => array(
					'value' => 'far fa-envelope'
				),
				'placeholder' => array(
					'value' => __( 'Username / Email', 'easy-login-woocommerce' ),
				),
				'unique_id' => array(
					'disabled' => 'disabled',
				),
				'class'
			);

			$this->fields->create_field_settings(
				$field_type_id,
				$setting_options
			);

			$this->fields->add_field(
				$field_id,
				$field_type_id,
				array(
					'unique_id' 	=> $field_id,
					'required' 		=> 'yes',
					'active' 		=> 'yes',
					'elType'		=> 'login',
					'autocomplete' 	=> 'username'
				),
				array(
					'group' 	=> 'login',
					'priority' 	=> 100,
				)
			);

		}


		public function login_predefined_field_userpassword(){

			$field_type_id = $field_id = 'xoo-el-password';

			$this->fields->add_type(
				$field_type_id,
				'password',
				'Login',
				array(
					'is_selectable' => 'no',
					'is_sortable' 	=> 'no',
					'can_delete'	=> 'no',
					'icon' 			=> 'fas fa-key'
				)
			);

			$setting_options = array(
				'label',
				'cols',
				'icon' => array(
					'value' => 'fas fa-key'
				),
				'placeholder' => array(
					'value' => __( 'Password', 'easy-login-woocommerce' ),
				),
				'password_visibility',
				'unique_id' => array(
					'disabled' => 'disabled',
				),
				'class'
			);


			$settings_value = array(
				'active' 		=> 'yes',
				'required' 		=> 'yes',
				'unique_id' 	=> $field_id,
				'elType'		=> 'login',
				'autocomplete' => 'current-password',
			);

			unset( $settings_value['active'] );

			$this->fields->create_field_settings(
				$field_type_id,
				$setting_options
			);

			$this->fields->add_field(
				$field_type_id,
				$field_id,
				$settings_value,
				array(
					'group' 	=> 'login',
					'priority' 	=> 200,
				)
			);

		}


		public function lostpw_predefined_field_useremail(){

			$field_type_id = $field_id = 'user_login';

			$this->fields->add_type(
				$field_type_id,
				'text',
				'Lost Password',
				array(
					'is_selectable' => 'no',
					'can_delete'	=> 'no',
					'is_sortable' 	=> 'no',
					'icon' 			=> 'fas fa-user',
				)
			);
				
			$setting_options = array(
				'label',
				'cols',
				'icon' => array(
					'value' => 'fas fa-user-plus'
				),
				'placeholder' => array(
					'value' => __( 'Username / Email', 'easy-login-woocommerce' ),
				),
				'unique_id' => array(
					'disabled' => 'disabled',
				),
				'class'
			);

			$this->fields->create_field_settings(
				$field_type_id,
				$setting_options
			);

			$this->fields->add_field(
				$field_id,
				$field_type_id,
				array(
					'unique_id' => $field_id,
					'required' 	=> 'yes',
					'active' 	=> 'yes',
					'elType' 	=> 'lostpw'
				),
				array(
					'group' 	=> 'resetpw',
					'priority' 	=> 100,
				)
			);

		}		


		public function resetpw_predefined_field_password(){

			$field_type_id = $field_id = 'xoo-el-rp-pass';

			$this->fields->add_type(
				$field_type_id,
				'password',
				'Reset Password',
				array(
					'is_selectable' => 'no',
					'is_sortable' 	=> 'no',
					'can_delete'	=> 'no',
					'icon' 			=> 'fas fa-key'
				)
			);

			$setting_options = array(
				'label',
				'cols',
				'icon' => array(
					'value' => 'fas fa-key'
				),
				'placeholder' => array(
					'value' => __( 'New Password', 'easy-login-woocommerce' ),
				),
				'minlength' => array(
					'value' => 6,
				),
				'maxlength' => array(
					'value' => 20,
				),
				'password_visibility',
				'unique_id' => array(
					'disabled' => 'disabled',
				),
				'class'
			);


			$settings_value = array(
				'active' 		=> 'yes',
				'required' 		=> 'yes',
				'unique_id' 	=> $field_id,
				'elType'		=> 'resetpw'
			);

			unset( $settings_value['active'] );

			$this->fields->create_field_settings(
				$field_type_id,
				$setting_options
			);

			$this->fields->add_field(
				$field_type_id,
				$field_id,
				$settings_value,
				array(
					'group' 	=> 'resetpw',
					'priority' 	=> 200,
				)
			);

		}


		public function resetpw_predefined_field_passwordagain(){

			$field_type_id = $field_id = 'xoo-el-rp-pass-again';

			$this->fields->add_type(
				$field_type_id,
				'password',
				'Reset Password',
				array(
					'is_selectable' => 'no',
					'is_sortable' 	=> 'no',
					'can_delete'	=> 'no',
					'icon' 			=> 'fas fa-key'
				)
			);

			$setting_options = array(
				'label',
				'cols',
				'icon' => array(
					'value' => 'fas fa-key'
				),
				'placeholder' => array(
					'value' => __( 'Confirm Password', 'easy-login-woocommerce' ),
				),
				'password_visibility',
				'unique_id' => array(
					'disabled' => 'disabled',
				),
				'class'
			);


			$settings_value = array(
				'active' 		=> 'yes',
				'required' 		=> 'yes',
				'unique_id' 	=> $field_id,
				'elType'		=> 'resetpw'
			);

			unset( $settings_value['active'] );

			$this->fields->create_field_settings(
				$field_type_id,
				$setting_options
			);

			$this->fields->add_field(
				$field_type_id,
				$field_id,
				$settings_value,
				array(
					'group' 	=> 'resetpw',
					'priority' 	=> 300,
				)
			);

		}


		public function predefined_field_single(){

			$field_type_id = $field_id = 'xoo-el-sing-user';

			$this->fields->add_type(
				$field_type_id,
				'text',
				'Single Field Pattern',
				array(
					'is_selectable' => 'no',
					'can_delete'	=> 'no',
					'is_sortable' 	=> 'no',
					'icon' 			=> 'fas fa-hand-point-right',
				)
			);
				
			$setting_options = array(
				'label',
				'cols',
				'icon' => array(
					'value' => 'far fa-envelope'
				),
				'placeholder' => array(
					'value' => __( 'Username / Email', 'easy-login-woocommerce' ),
				),
				'unique_id' => array(
					'disabled' => 'disabled',
				),
				'class'
			);


			$this->fields->add_setting(
				'xoo_el_username',
				'Allow Login with username',
				'checkbox',
				$field_type_id,
				array(
					'priority' 	=> 5,
					'value' 	=> 'yes',
					'info' 		=> 'Allow users to login with the username'
				)
			);

			$this->fields->create_field_settings(
				$field_type_id,
				$setting_options
			);

			$this->fields->add_field(
				$field_id,
				$field_type_id,
				array(
					'unique_id' 	=> $field_id,
					'required' 		=> 'yes',
					'active' 		=> 'yes',
					'elType'		=> 'single',
					'autocomplete' 	=> 'username'
				),
				array(
					'group' 	=> 'others',
					'priority' 	=> 300,
				)
			);

		}



		public function merge_with_wc_fields_setting_option( $field_type_id ){
			if( !class_exists( 'woocommerce' ) ) return;
			$this->fields->add_setting(
				'xoo_el_merge_wc_field',
				'Auto fill woocommerce billing & shipping fields',
				'checkbox',
				$field_type_id,
				array(
					'priority' 	=> 100,
					'value' 	=> 'yes'
				)
			);
		}

		//Add option to show field on woocommerce my account page
		public function modify_default_field_settings( $settings ){

			
			foreach ( $settings as $setting_id => $setting_options ) {
				if( class_exists( 'woocommerce' ) ){
					$settings[ $setting_id ][] = 'display_myacc';
				}
				$settings[ $setting_id ][] = 'display_usercol';
			}
			
			
			return $settings;

		}


		public function add_custom_settings_option( $setting_options ){

			//Show on my account page
			if( class_exists( 'woocommerce' ) ){
				$setting_options['display_myacc'] = array(
					'type' 		=> 'checkbox',
					'id'		=> 'display_myacc',
					'section' 	=> 'basic',	
					'title' 	=> 'Show on WC myaccount page',
					'width'		=> 'half',
					'value'		=> 'yes',
					'priority' 	=> 15
				);
			}

			$setting_options['display_usercol'] = array(
				'type' 		=> 'checkbox',
				'id'		=> 'display_usercol',
				'section' 	=> 'basic',	
				'title' 	=> 'Add under user column',
				'width'		=> 'half',
				'value'		=> 'yes',
				'info' 		=> 'Under wp-admin -> users table, this field will be added as a column.',
				'priority' 	=> 20
			);
			
			return $setting_options;
		}

	}


}

new Xoo_El_Aff_Fields();
?>