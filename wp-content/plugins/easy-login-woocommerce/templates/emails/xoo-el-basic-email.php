<?php
/**
 *
 * This template can be overridden by copying it to yourtheme/templates/easy-login-woocommerce/emails/xoo-el-basic-email.php
 *
 * HOWEVER, on occasion we will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen.
 * @see     https://docs.xootix.com/easy-login-woocommerce/
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}


?>

<?php do_action( 'xoo_el_basic_email_header' ); ?>


 <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
		<td>
			<!-- Outer wrapper to limit width -->
			<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px;">
				<tr>
					<td>
						<?php echo $email_text; ?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<?php do_action( 'xoo_el_basic_email_footer' ); ?>