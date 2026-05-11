<?php
/**
 * Code verification Form
 *
 * This template can be overridden by copying it to yourtheme/templates/easy-login-woocommerce/xoo-el-code-form.php.
 *
 * HOWEVER, on occasion we will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen.
 * @see     https://docs.xootix.com/mobile-login-woocommerce/
 * @version 3.0.0
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


$parentFormSelector = $parentFormSelector ? 'data-parentform=".'.$parentFormSelector.'"' : '';
?>

<form class="xoo-el-code-form" <?php echo $parentFormSelector ?> data-code="<?php echo $code_form_id ?>">

	<?php do_action( 'xoo_el_code_form_start' ) ?>

	<div class="xoo-el-code-sent-txt">
		<span class="xoo-el-code-no-txt"></span>
		<?php if( $allow_change ): ?>
		<span class="xoo-el-code-no-change"> <?php _e( "Change", 'easy-login-woocommerce' ); ?></span>
		<?php endif; ?>
	</div>

	<div class="xoo-el-code-notice-cont">
		<div class="xoo-el-code-notice"></div>
	</div>

	<div class="xoo-el-code-input-cont">
		<?php for ( $i= 0; $i < $digits; $i++ ): ?>
			<input type="tel" autocomplete="off" name="xoo-el-code[]" class="xoo-el-code-input">
		<?php endfor; ?>
	</div>

	<input type="hidden" name="xoo-el-code-phone-no" >
	<input type="hidden" name="xoo-el-code-phone-code" >

	<button type="submit" class="button btn xoo-el-code-submit-btn xoo-el-action-btn"><?php echo $verify_btn ?></button>

	<?php if( $resend ): ?>

	<div class="xoo-el-code-resend">
		<a class="xoo-el-code-resend-link"><?php echo $resend_txt ?></a>
		<span class="xoo-el-code-resend-timer"></span>
	</div>

	<?php endif; ?>

	<input type="hidden" name="xoo_el_code_form_id" value="<?php echo $code_form_id; ?>">

	<?php do_action( 'xoo_el_code_form_end' ) ?>

</form>
