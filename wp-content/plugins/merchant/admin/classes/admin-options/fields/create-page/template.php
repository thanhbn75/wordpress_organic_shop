<?php
/**
 * Template: Create Page field.
 *
 * @var array<string, mixed>  $settings  Field configuration.
 * @var mixed  $value     Current saved value.
 * @var string $module_id Module ID.
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$page_id  = get_option( $settings['option_name'] );

echo '<div class="merchant-create-page-control">';

if ( $page_id && post_exists( get_the_title( $page_id ) ) && 'publish' === get_post_status( $page_id ) ) {
	echo wp_kses_post(
		sprintf(  /* translators: 1: link to edit page */
			__( '<p class="merchant-module-page-setting-field-desc mrc-mt-0">Your page is created!</p><p class="merchant-module-page-setting-field-desc">Click <a href="%1$s" target="_blank">here</a> if you want to edit the page.</p><p class="merchant-module-page-setting-field-desc mrc-mb-0">To display the page in your theme header area, assign the page to the primary menu by clicking <a href="%2$s" target="_blank">here</a></p>',
				'merchant' ),
			get_admin_url() . 'post.php?post=' . $page_id . '&action=edit',
			get_admin_url() . 'nav-menus.php'
		)
	);
} else {
	echo '<div class="merchant-create-page-control-create-message">';
	echo wp_kses_post(
		'<p class="merchant-module-page-setting-field-desc mrc-mt-0">'.
		sprintf( /* translators: 1: page name */
			__( 'It looks like you haven\'t created a <strong>%1$s</strong> page yet. Click the below button to create the page.',
				'merchant' ),
			$settings['page_title']
		). '</p>'
	);
	echo '</div>';
	echo '<div class="merchant-create-page-control-success-message" style="display: none;">';
	echo wp_kses_post(
		sprintf( /* translators: 1: link to edit page */
			__( '<p class="merchant-module-page-setting-field-desc">Page created with success!</p><p class="merchant-module-page-setting-field-desc">Click <a href="%1$s" target="_blank">here</a> if you want to edit the page.</p><p class="merchant-module-page-setting-field-desc mrc-mb-0">To display the page in your theme header area, assign the page to the primary menu by clicking <a href="%2$s" target="_blank">here</a></p>',
				'merchant' ),
			get_admin_url() . 'post.php?post=&action=edit',
			get_admin_url() . 'nav-menus.php'
		)
	);
	echo '</div>';
	echo wp_kses_post(
		sprintf( /* translators: 1: page title, 2: page meta key, 3: page meta value, 4: option name, 5: nonce, 6: loading text, 7: success text  */
			__( '<a href="#" class="merchant-create-page-control-button button-tertiary" data-page-title="%2$s" data-page-meta-key="%3$s" data-page-meta-value="%4$s" data-option-name="%5$s" data-nonce="%6$s" data-creating-text="%7$s" data-created-text="%8$s">%1$s</a>',
				'merchant' ),
			__( 'Create Page', 'merchant' ),
			$settings['page_title'],
			$settings['page_meta_key'],
			$settings['page_meta_value'],
			$settings['option_name'],
			wp_create_nonce( 'customize-create-page-control-nonce' ),
			__( 'Creating...', 'merchant' ),
			__( 'Created!', 'merchant' )
		)
	);
}

echo '</div>';
