<?php

/**
 * Pre-Orders Product Metabox
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Pre-Orders Metabox Class
 *
 * @since 1.0.0
 */
class Merchant_Pre_Orders_Metabox extends Merchant_Metabox {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_filter( 'merchant_metabox_options', array( $this, 'add_metabox_options' ), 10, 1 );
	}

	/**
	 * Add metabox options.
	 *
	 * @param array $options Existing metabox options.
	 *
	 * @return array Modified metabox options.
	 */
	public function add_metabox_options( $options ) {
		// Add Pre-Orders section
		$this->add_section( 'merchant_pre_orders', array(
			'title'     => esc_html__( 'Pre-Orders', 'merchant' ),
			'post_type' => array( 'product' ),
			'priority'  => 20,
		) );

		// General Tab Fields
		$this->add_field( '_merchant_pre_order_mode', array(
			'section' => 'merchant_pre_orders',
			'type'    => 'select',
			'title'   => esc_html__( 'Pre-Order Mode', 'merchant' ),
			'desc'    => esc_html__( 'Choose how pre-orders should be handled for this product.', 'merchant' ),
			'options' => array(
				'global'   => esc_html__( 'Global Settings (Default)', 'merchant' ),
				'product'  => esc_html__( 'Product Specific Settings', 'merchant' ),
				'disabled' => esc_html__( 'Disable Pre-Orders', 'merchant' ),
			),
			'default' => 'global',
		) );

		$this->add_field( '_merchant_pre_order_start', array(
			'section'     => 'merchant_pre_orders',
			'type'        => 'date_time',
			'title'       => esc_html__( 'Pre-Order Starts At', 'merchant' ),
			'desc'        => esc_html__( 'If you want your pre-order settings to take effect immediately, leave the pre-order start empty.', 'merchant' ),
			'placeholder' => esc_html__( 'Select date and time', 'merchant' ),
			'options'     => array(
				'timepicker' => true,
				'minDate'    => 'today',
			),
			'conditions'  => array(
				'relation' => 'AND',
				'terms'    => array(
					array( '_merchant_pre_order_mode', '==', 'product' ),
				),
			),
		) );

		$this->add_field( '_merchant_pre_order_end', array(
			'section'     => 'merchant_pre_orders',
			'type'        => 'date_time',
			'title'       => esc_html__( 'Pre-Order Ends At', 'merchant' ),
			'desc'        => sprintf(
			/* Translators: %1$s: Time zone, %2$s WordPress setting link */
				esc_html__( 'Leave it empty if you don’t want to have an end date. The times set above are in the %1$s timezone, according to your settings from %2$s.', 'merchant' ),
				'<strong>' . wp_timezone_string() . '</strong>',
				'<a href="' . esc_url( admin_url( 'options-general.php' ) ) . '" target="_blank">' . esc_html__( 'WordPress Settings', 'merchant' ) . '</a>'
			),
			'placeholder' => esc_html__( 'Select date and time', 'merchant' ),
			'options'     => array(
				'timepicker' => true,
				'minDate'    => 'today',
			),
			'conditions'  => array(
				'relation' => 'AND',
				'terms'    => array(
					array( '_merchant_pre_order_mode', '==', 'product' ),
				),
			),
		) );

		$this->add_field( '_merchant_pre_order_shipping_date', array(
			'section'     => 'merchant_pre_orders',
			'type'        => 'date_time',
			'title'       => esc_html__( 'Expected Shipping Date', 'merchant' ),
			'desc'        => sprintf(
			/* Translators: %1$s: Time zone, %2$s WordPress setting link */
				esc_html__( 'The times set above are in the %1$s timezone, according to your settings from %2$s.', 'merchant' ),
				'<strong>' . wp_timezone_string() . '</strong>',
				'<a href="' . esc_url( admin_url( 'options-general.php' ) ) . '" target="_blank">' . esc_html__( 'WordPress Settings', 'merchant' ) . '</a>'
			),
			'placeholder' => esc_html__( 'Select date and time', 'merchant' ),
			'options'     => array(
				'timepicker' => true,
				'minDate'    => 'today',
			),
			'conditions'  => array(
				'relation' => 'AND',
				'terms'    => array(
					array( '_merchant_pre_order_mode', '==', 'product' ),
				),
			),
		) );

		// Discount Tab Fields
		$this->add_field( '_merchant_pre_order_discount_toggle', array(
			'section'    => 'merchant_pre_orders',
			'type'       => 'switcher',
			'title'      => esc_html__( 'Offer Discount', 'merchant' ),
			'desc'       => esc_html__( 'Enable to offer a discount for pre-ordering this product.', 'merchant' ),
			'default'    => 0,
			'conditions' => array(
				'relation' => 'AND',
				'terms'    => array(
					array( '_merchant_pre_order_mode', '==', 'product' ),
				),
			),
		) );

		$this->add_field( '_merchant_pre_order_discount_type', array(
			'section'    => 'merchant_pre_orders',
			'type'       => 'radio',
			'title'      => esc_html__( 'Discount Type', 'merchant' ),
			'options'    => array(
				'percentage' => esc_html__( 'Percentage', 'merchant' ),
				'fixed'      => esc_html__( 'Fixed Amount', 'merchant' ),
			),
			'default'    => 'percentage',
			'conditions' => array(
				'relation' => 'AND',
				'terms'    => array(
					array( '_merchant_pre_order_mode', '==', 'product' ),
					array( '_merchant_pre_order_discount_toggle', '==', '1' ),
				),
			),
		) );

		$this->add_field( '_merchant_pre_order_discount_amount', array(
			'section'    => 'merchant_pre_orders',
			'type'       => 'number',
			'title'      => esc_html__( 'Discount Amount', 'merchant' ),
			'desc'       => esc_html__( 'Enter the discount amount (percentage or fixed).', 'merchant' ),
			'default'    => 0,
			'conditions' => array(
				'relation' => 'AND',
				'terms'    => array(
					array( '_merchant_pre_order_mode', '==', 'product' ),
					array( '_merchant_pre_order_discount_toggle', '==', '1' ),
				),
			),
		) );

		// User Conditions Tab Fields
		$this->add_field( '_merchant_pre_order_user_condition', array(
			'section'    => 'merchant_pre_orders',
			'type'       => 'select',
			'title'      => esc_html__( 'User Condition', 'merchant' ),
			'desc'       => esc_html__( 'Who can pre-order this product?', 'merchant' ),
			'options'    => array(
				'all'   => esc_html__( 'All Users', 'merchant' ),
				'roles' => esc_html__( 'Specific Roles', 'merchant' ),
				'users' => esc_html__( 'Specific Users', 'merchant' ),
			),
			'default'    => 'all',
			'conditions' => array(
				'relation' => 'AND',
				'terms'    => array(
					array( '_merchant_pre_order_mode', '==', 'product' ),
				),
			),
		) );

		$this->add_field( '_merchant_pre_order_user_condition_roles', array(
			'section'    => 'merchant_pre_orders',
			'type'       => 'select',
			'title'      => esc_html__( 'Allowed Roles', 'merchant' ),
			'desc'       => esc_html__( 'This will limit the rule to users with these roles.', 'merchant' ),
			'options'    => $this->get_user_roles_choices(),
			'multiple'   => true,
			'conditions' => array(
				'relation' => 'AND',
				'terms'    => array(
					array( '_merchant_pre_order_mode', '==', 'product' ),
					array( '_merchant_pre_order_user_condition', '==', 'roles' ),
				),
			),
		) );

		$this->add_field( '_merchant_pre_order_user_condition_users', array(
			'section'    => 'merchant_pre_orders',
			'type'       => 'select-ajax',
			'title'      => esc_html__( 'Allowed Users', 'merchant' ),
			'desc'       => esc_html__( 'This will limit the rule to the selected customers.', 'merchant' ),
			'source'     => 'user',
			'multiple'   => true,
			'conditions' => array(
				'relation' => 'AND',
				'terms'    => array(
					array( '_merchant_pre_order_mode', '==', 'product' ),
					array( '_merchant_pre_order_user_condition', '==', 'users' ),
				),
			),
		) );

		$this->add_field( '_merchant_pre_order_user_exclusion_enabled', array(
			'section'    => 'merchant_pre_orders',
			'type'       => 'switcher',
			'title'      => esc_html__( 'Enable Exclusions', 'merchant' ),
			'desc'       => esc_html__( 'Select the users that will not show the offer.', 'merchant' ),
			'default'    => 0,
			'conditions' => array(
				'relation' => 'AND',
				'terms'    => array(
					array( '_merchant_pre_order_mode', '==', 'product' ),
				),
			),
		) );

		$this->add_field( '_merchant_pre_order_exclude_roles', array(
			'section'    => 'merchant_pre_orders',
			'type'       => 'select',
			'title'      => esc_html__( 'Excluded Roles', 'merchant' ),
			'desc'       => esc_html__( 'This will exclude the offer for users with these roles.', 'merchant' ),
			'options'    => $this->get_user_roles_choices(),
			'multiple'   => true,
			'conditions' => array(
				'relation' => 'AND',
				'terms'    => array(
					array( '_merchant_pre_order_mode', '==', 'product' ),
					array( '_merchant_pre_order_user_exclusion_enabled', '==', '1' ),
				),
			),
		) );

		$this->add_field( '_merchant_pre_order_exclude_users', array(
			'section'    => 'merchant_pre_orders',
			'type'       => 'select-ajax',
			'title'      => esc_html__( 'Excluded Users', 'merchant' ),
			'desc'       => esc_html__( 'This will exclude the offer for the selected customers.', 'merchant' ),
			'source'     => 'user',
			'multiple'   => true,
			'conditions' => array(
				'relation' => 'AND',
				'terms'    => array(
					array( '_merchant_pre_order_mode', '==', 'product' ),
					array( '_merchant_pre_order_user_exclusion_enabled', '==', '1' ),
				),
			),
		) );

		// Styles / Text Tab Fields
		$this->add_field( '_merchant_pre_order_button_text', array(
			'section'     => 'merchant_pre_orders',
			'type'        => 'text',
			'title'       => esc_html__( 'Button Text', 'merchant' ),
			'desc'        => esc_html__( 'Custom text for the pre-order button.', 'merchant' ),
			'placeholder' => esc_html__( 'Pre-Order Now', 'merchant' ),
			'conditions'  => array(
				'relation' => 'AND',
				'terms'    => array(
					array( '_merchant_pre_order_mode', '==', 'product' ),
				),
			),
		) );

		$this->add_field( '_merchant_pre_order_cart_label_text', array(
			'section'     => 'merchant_pre_orders',
			'type'        => 'text',
			'title'       => esc_html__( 'Cart Label Text', 'merchant' ),
			'desc'        => esc_html__( 'Text to display in the cart for pre-order items.', 'merchant' ),
			'placeholder' => esc_html__( 'Pre-Order', 'merchant' ),
			'conditions'  => array(
				'relation' => 'AND',
				'terms'    => array(
					array( '_merchant_pre_order_mode', '==', 'product' ),
				),
			),
		) );

		$this->add_field( '_merchant_pre_order_additional_text', array(
			'section'     => 'merchant_pre_orders',
			'type'        => 'text',
			'title'       => esc_html__( 'Additional Information Text', 'merchant' ),
			'desc'        => __( 'Additional text to display on the product page, use <strong>{date}</strong> to display the shipping date.', 'merchant' ),
			'placeholder' => esc_html__( 'Ships on {date}', 'merchant' ),
			'conditions'  => array(
				'relation' => 'AND',
				'terms'    => array(
					array( '_merchant_pre_order_mode', '==', 'product' ),
				),
			),
		) );

		$this->add_field( '_merchant_pre_order_placement', array(
			'section'    => 'merchant_pre_orders',
			'type'       => 'radio',
			'title'      => esc_html__( 'Placement', 'merchant' ),
			'desc'       => esc_html__( 'Choose where to display the pre-order information.', 'merchant' ),
			'options'    => array(
				'before' => esc_html__( 'Before Button', 'merchant' ),
				'after'  => esc_html__( 'After Button', 'merchant' ),
			),
			'default'    => 'before',
			'conditions' => array(
				'relation' => 'AND',
				'terms'    => array(
					array( '_merchant_pre_order_mode', '==', 'product' ),
				),
			),
		) );

		$this->add_field( '_merchant_pre_order_text_color', array(
			'section'    => 'merchant_pre_orders',
			'type'       => 'color',
			'title'      => esc_html__( 'Button Text Color', 'merchant' ),
			'default'    => '#ffffff',
			'conditions' => array(
				'relation' => 'AND',
				'terms'    => array(
					array( '_merchant_pre_order_mode', '==', 'product' ),
				),
			),
		) );

		$this->add_field( '_merchant_pre_order_text_hover_color', array(
			'section'    => 'merchant_pre_orders',
			'type'       => 'color',
			'title'      => esc_html__( 'Button Text Color (Hover)', 'merchant' ),
			'default'    => '#ffffff',
			'conditions' => array(
				'relation' => 'AND',
				'terms'    => array(
					array( '_merchant_pre_order_mode', '==', 'product' ),
				),
			),
		) );

		$this->add_field( '_merchant_pre_order_border_color', array(
			'section'    => 'merchant_pre_orders',
			'type'       => 'color',
			'title'      => esc_html__( 'Button Border Color', 'merchant' ),
			'default'    => '#212121',
			'conditions' => array(
				'relation' => 'AND',
				'terms'    => array(
					array( '_merchant_pre_order_mode', '==', 'product' ),
				),
			),
		) );

		$this->add_field( '_merchant_pre_order_border_hover_color', array(
			'section'    => 'merchant_pre_orders',
			'type'       => 'color',
			'title'      => esc_html__( 'Button Border Color (Hover)', 'merchant' ),
			'default'    => '#000000',
			'conditions' => array(
				'relation' => 'AND',
				'terms'    => array(
					array( '_merchant_pre_order_mode', '==', 'product' ),
				),
			),
		) );

		$this->add_field( '_merchant_pre_order_border_width', array(
			'section'    => 'merchant_pre_orders',
			'type'       => 'range',
			'title'      => esc_html__( 'Button Border Width (px)', 'merchant' ),
			'min'        => 0,
			'max'        => 10,
			'step'       => 1,
			'default'    => 0,
			'conditions' => array(
				'relation' => 'AND',
				'terms'    => array(
					array( '_merchant_pre_order_mode', '==', 'product' ),
				),
			),
		) );

		$this->add_field( '_merchant_pre_order_border_radius', array(
			'section'    => 'merchant_pre_orders',
			'type'       => 'range',
			'title'      => esc_html__( 'Button Border Radius (px)', 'merchant' ),
			'min'        => 0,
			'max'        => 50,
			'step'       => 1,
			'default'    => 0,
			'conditions' => array(
				'relation' => 'AND',
				'terms'    => array(
					array( '_merchant_pre_order_mode', '==', 'product' ),
				),
			),
		) );

		$this->add_field( '_merchant_pre_order_background_color', array(
			'section'    => 'merchant_pre_orders',
			'type'       => 'color',
			'title'      => esc_html__( 'Button Background Color', 'merchant' ),
			'default'    => '#212121',
			'conditions' => array(
				'relation' => 'AND',
				'terms'    => array(
					array( '_merchant_pre_order_mode', '==', 'product' ),
				),
			),
		) );

		$this->add_field( '_merchant_pre_order_background_hover_color', array(
			'section'    => 'merchant_pre_orders',
			'type'       => 'color',
			'title'      => esc_html__( 'Button Background Color (Hover)', 'merchant' ),
			'default'    => '#000000',
			'conditions' => array(
				'relation' => 'AND',
				'terms'    => array(
					array( '_merchant_pre_order_mode', '==', 'product' ),
				),
			),
		) );

		return $options;
	}

	/**
	 * Get user roles for select choices.
	 *
	 * @return array User roles choices.
	 */
	private function get_user_roles_choices() {
		$user_roles = Merchant_Admin_Options::get_user_roles_select2_choices();

		if ( empty( $user_roles ) || ! is_array( $user_roles ) ) {
			return array();
		}

		return array_column( $user_roles, 'text', 'id' );
	}
}

// Initialize the metabox
new Merchant_Pre_Orders_Metabox();
