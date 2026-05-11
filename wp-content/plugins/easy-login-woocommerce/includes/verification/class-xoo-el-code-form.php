<?php

class Xoo_El_Code_Form{

    public $form_id;

    public $limits;

    public $input;

    public $object;

    public $code;

    public $parentFormSelector = '';

    public $codeFormArgs = array();

    public $successMessage;

    public static $glSettings;

    public function __construct() {

        if( !self::$glSettings ){
            self::$glSettings = xoo_el_helper()->get_general_option();
        }

        $this->limits =  array(
            'resend_limit'      => 7,
            'incorrect_limit'   => 7,
            'resend_wait_time'  => 90,
            'ban_time'          => 600
        );

        $this->codeFormArgs = wp_parse_args( $this->codeFormArgs, array(
            'return'                => false,
            'digits'                => 6,
            'code_form_id'          => $this->form_id ,
            'verify_btn'            => __( 'Verify', 'easy-login-woocommerce' ),
            'resend'                => true,
            'resend_txt'            => __( 'Not received your code? Resend code', 'easy-login-woocommerce' ),
            'parentFormSelector'    => $this->parentFormSelector,
            'allow_change'          => true
        ) );

        $this->successMessage = __( 'Thank you for verifying.', 'easy-login-woocommerce' );

        $this->hooks();
    }


    public function hooks(){

        if( isset( $_POST['xoo_el_code_ajax'] ) && $_POST['xoo_el_code_ajax'] === $this->form_id ){

            add_action( 'wp_ajax_xoo_el_code_form_submit', array( $this, 'verify_code' ) );
            add_action( 'wp_ajax_nopriv_xoo_el_code_form_submit', array( $this, 'verify_code' ) );
        }
    }


    public function get_form_id() {
        return $this->form_id;
    }


    public function get_transient_name(){
        return 'xoo_el_user_'. $this->form_id.'_'.md5( xoo_el_helper()->geolocate()->get_ip_address() );
    }


    public function get_user_data( $subkey = '' ){

        $user_data = get_transient( $this->get_transient_name() );

        if( $subkey ){
            return isset( $user_data[$subkey] ) ? $user_data[$subkey] : null;
        }

        return $user_data;

    }



    public function set_user_data( $data = array() ){

        $existing_data          = $this->get_user_data();

        $data                   = wp_parse_args( $data, $existing_data );

        $data['last_updated']   = time();

        set_transient( $this->get_transient_name(), $data, DAY_IN_SECONDS );

    }


    public function request_code( $email ){

    }

  

    public function ok_to_send_code( $sendTo = '' ){

        $user_data = $this->get_user_data();

        if( !is_array( $user_data ) || empty( $user_data ) ) return;

        $limits = $this->limits;

        $time_passed = strtotime("now") - (int) $user_data['created'];

        if( $user_data['sent_times'] > $limits['resend_limit'] ){
            $unban_time_left = $limits['ban_time'] - $time_passed;
            if(  $unban_time_left < 0  ){
                $this->set_user_data( array( 'sent_times' => 0 ) );
            }
            else{
                return new WP_Error( 'limit-reached', sprintf( __( 'Code Limit reached. Please try again in %s.', 'easy-login-woocommerce' ), self::getTimeDuration( $unban_time_left) ) );
            }
        }


        //For resend
       /* if( $limits['resend_wait_time'] > $time_passed ){
            $unban_time_left = $limits['resend_wait_time'] - $time_passed;
            return new WP_Error( 'resend-wait', sprintf( __( 'Please wait %s for a new code.', 'easy-login-woocommerce' ), self::getTimeDuration( $unban_time_left) ) );
        }*/


        $incorrect_tries_limit_reached = $this->incorrect_tries_limit_reached();

        if( is_wp_error(  $incorrect_tries_limit_reached ) ){

            $unban_time_left = $limits['ban_time'] - $time_passed;

            if( $unban_time_left < 0 ){
                self::set_user_data( array( 'incorrect' => 0 ) );
            }
            else{
                return $incorrect_tries_limit_reached;
            }
        }


    }


    public function incorrect_tries_limit_reached(){

        $user_data = $this->get_user_data();

        if( isset( $user_data['incorrect'] ) && $user_data['incorrect'] >= $this->limits['incorrect_limit'] ){
            return new WP_Error( 'tries-exceeded', __( 'Number of tries exceeded, Please try again in few minutes', 'easy-login-woocommerce' ) );
        }

        return false;

    }


    public function code_expired(){

        $user_data = $this->get_user_data();

        if( isset( $user_data['expiry'] ) && strtotime('now') > (int) $user_data['expiry'] ){
            return new WP_Error( 'code-expired', __( 'Code Expired', 'easy-login-woocommerce' ) );
        }

        return false;
    }


    public static function getTimeDuration( $time ){
        return $time > 60 ? round($time/60). ' minutes' : $time. ' seconds';
    }


    public function sendEmailCode( $email ){

        $ok_to_send_code = $this->ok_to_send_code( $email );

        if( is_wp_error( $ok_to_send_code ) ){
            return $ok_to_send_code;
        }

        $this->code = $this->generate_code_digits();

        $this->triggerCodeEmail();
       
        $user_data = $this->get_user_data();

        if( $user_data){
            $sent_times     = (int) $user_data['sent_times'];
            $incorrect      = $user_data['incorrect'];
        }else{
            $incorrect = $sent_times = 0;
        }

        $sent_times++;

        $this->set_user_data(
            array(
                'code'           => $this->code,
                'verified'      => false,
                'sendTo'        => $email,
                'incorrect'     => $incorrect,
                'sent_times'    => $sent_times,
                'created'       => strtotime('now'),
                'expiry'        => strtotime('60000 seconds'),
            )
        );

    }


    public function resend_code(){

        $sendTo = $this->get_user_data('sendTo');

        if( !$sendTo ){
            return new Wp_Error( 'no-phone', __( "Phone Number not found", 'mobile-login-woocommerce' ) );
        }
        $code = $this->sendEmailCode( $user_data['sendTo'] );
        return $code;
    }



    public function get_code_email_content(){

    }


    public function generate_code_digits(){

        $digits = 6;

        $pattern = 'number';

        switch ($pattern) {

            case 'alphabet':
                $code = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $digits);
                break;

            case 'numb_alpha':
                $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $code = '';
                for ($i = 0; $i < $digits; $i++) {
                    $code .= $characters[rand(0, strlen($characters) - 1)];
                }
                break;

            default:
                $code = rand( pow( 10, $digits - 1 ) , pow( 10, $digits ) - 1 );
                break;
        } 

        return $code;
    }


    public function get_code_form( $args = array() ){
        return xoo_el_helper()->get_template( 'xoo-el-code-form.php', wp_parse_args( $args, $this->codeFormArgs ), '', $this->codeFormArgs['return'] );
    }


    public function onSuccess(){

    }

    public function verify_code(){

        try {
            
            $incorrect_limit_reached = $this->incorrect_tries_limit_reached();

            //Check for incorrect limit
            if( is_wp_error( $incorrect_limit_reached )  ){
                throw new Xoo_Exception( $incorrect_limit_reached );
            }

            $user_data = $this->get_user_data();
            
            if( !isset( $user_data['code'] ) || $user_data['code'] != $_POST['code'] ){

                $incorrect = isset( $user_data['incorrect'] ) ? $user_data['incorrect'] + 1 : 1;

                $this->set_user_data( array( 'incorrect' => $incorrect ) );

                throw new Xoo_Exception( __( 'Invalid Code', 'easy-login-woocommerce' ) );
            }

            $code_expired = $this->code_expired();

            if( is_wp_error( $code_expired ) ){
                throw new Xoo_Exception( $code_expired );
            }

            $this->set_user_data( array(
                'verified'          => true,
                'incorrect'         => 0,
                'sent_times'        => 0,
                'expiry'            => '',
                'created'           => '', 
            ) );

            $updated_user_data = $this->get_user_data();

            //Hook functions on Code verification
            do_action( 'xoo_el_code_validation_success', $this->form_id, $updated_user_data );

            $this->onSuccess();

            $notice = apply_filters( 'xoo_el_code_validation_success_notice', $this->successMessage, $this->form_id, $updated_user_data );

            wp_send_json(array(
                'error'     => 0,
                'notice'    => xoo_el_add_notice( 'success', $notice )
            ));

        } catch ( Xoo_Exception $e ) {

            $notice = apply_filters( 'xoo_el_code_verify_errors', $e->getMessage() );

            wp_send_json(array(
                'error'     => 1,
                'notice'    => xoo_el_add_notice( 'error', $notice )
            ));

        }
        
    }


    public function mask_email($email){

        $email = preg_replace('/\B[^@.]/', '*', $email);

        return $email;

    }


    public function is_user_verified(){
        return $this->get_user_data('verified');
    }

}