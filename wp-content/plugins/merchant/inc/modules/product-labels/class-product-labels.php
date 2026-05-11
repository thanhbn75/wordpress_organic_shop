<?php

/**
 * Product Labels.
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Product Labels Class.
 *
 */
class Merchant_Product_Labels extends Merchant_Add_Module {

	/**
	 * Module ID.
	 *
	 */
	const MODULE_ID = 'product-labels';

	/**
	 * Is module preview.
	 *
	 */
	public static $is_module_preview = false;

	/**
	 * Whether the module has a shortcode or not.
	 *
	 * @var bool
	 */
	public $has_shortcode = true;

	/**
	 * Constructor.
	 *
	 */
	public function __construct() {
		// Module id.
		$this->module_id = self::MODULE_ID;

		// WooCommerce only.
		$this->wc_only = true;

		// Parent construct.
		parent::__construct();

		// Module section.
		$this->module_section = 'convert-more';

		// Module default settings.
		$this->module_default_settings = array(
			'layout' => 'single-label',
            'labels' => array(
	            array(
		            'label_type'       => 'text',
		            'label'            => esc_html__( 'SALE', 'merchant' ),
                    'label_text_shape' => 'text-shape-1',
	            ),
            ),
		);

		// Module data.
		$this->module_data = Merchant_Admin_Modules::$modules_data[ self::MODULE_ID ];

		// Module options path.
		$this->module_options_path = MERCHANT_DIR . 'inc/modules/' . self::MODULE_ID . '/admin/options.php';

		// Is module preview page.
		if ( is_admin() && parent::is_module_settings_page() ) {
			self::$is_module_preview = true;

			// Enqueue admin styles.
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_css' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_js' ) );

			// Admin preview box.
			add_filter( 'merchant_module_preview', array( $this, 'render_admin_preview' ), 10, 2 );

			// Custom CSS.
			// The custom CSS should be added here as well due to ensure preview box works properly.
			add_filter( 'merchant_custom_css', array( $this, 'admin_custom_css' ) );
		}

		if ( ! Merchant_Modules::is_module_active( self::MODULE_ID ) ) {
			return;
		}

        if ( is_admin() ) {
            // Init translations.
            $this->init_translations();
        }

        // Required for block editor
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_css' ) );

		// Return early if it's on admin but not in the respective module settings page.
		if ( is_admin() && ! wp_doing_ajax() && ! parent::is_module_settings_page() ) {
			return;
		}

        add_filter( 'merchant_product_labels_display_rules', array( $this, 'add_pre_order_option' ) );

		// Enqueue styles.
		add_action( 'merchant_enqueue_before_main_css_js', array( $this, 'enqueue_css' ) );

        // Enqueue scripts
		add_action( 'merchant_enqueue_before_main_css_js', array( $this, 'enqueue_js' ) );

		// Product Loop/Archives
		if ( merchant_is_botiga_active() ) {
			add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'loop_product_output' ) );
        } else {
			add_action( 'woocommerce_before_shop_loop_item', array( $this, 'loop_product_output' ) );
		}

		// Ultimate Addon Elementor
        if ( defined( 'UAEL_FILE' ) ) {
            add_action( 'uael_woo_products_before_summary_wrap', array( $this, 'loop_product_output' ) );
        }

		// Woo Block
		add_filter( 'woocommerce_blocks_product_grid_item_html', array( $this, 'products_block' ), 9999, 3 );

        // Product Single
        if ( merchant_is_flatsome_active() ) {
	        add_action( 'flatsome_sale_flash', array( $this, 'single_product_output' ) );
        } elseif ( class_exists( 'Woostify_WooCommerce' ) ) {
	        add_action( 'woostify_product_images_box_end', array( $this, 'single_product_output' ) );
        } else {
	        add_action( 'woocommerce_product_thumbnails', array( $this, 'single_product_output' ) );
        }

		// XStore Theme - Archive/Single/Elementor Widget
		if ( defined( 'ETHEME_FW' ) ) {
			// Product Single: Default
            add_action( 'woocommerce_before_single_product_summary', array( $this, 'single_product_output' ) );

			// Product Single: Elementor Product Image widget
			add_action( 'etheme_before_single_product_image', array( $this, 'single_product_output' ) );

            // Archive: Elementor Products Widgets by XStore
            add_filter( 'single_product_archive_thumbnail_size', array( $this, 'loop_product_output' ) );
		}

        add_action( 'woocommerce_single_product_image_gallery_classes', array( $this, 'single_product_image_gallery_classes' ) );

        add_filter( 'woocommerce_sale_flash', array( $this, 'remove_on_sale' ), 9999 );

		// Custom CSS.
		add_filter( 'merchant_custom_css', array( $this, 'frontend_custom_css' ) );

        $this->migrate_exclusion_list();

        // Migrate exclusion fields for backward compatibility
        $this->migrate_exclusion_fields();
	}

	/**
	 * Init translations.
	 *
	 * @return void
	 */
	public function init_translations() {
		$settings = $this->get_module_settings();
		if ( isset( $settings['labels'] ) ) {
			foreach ( $settings['labels'] as $label ) {
				if ( isset( $label['label'] ) ) {
					Merchant_Translator::register_string( $label['label'], esc_html__( 'Product Labels', 'merchant' ) );
				}
				if ( isset( $label['percentage_text'] ) ) {
					Merchant_Translator::register_string( $label['percentage_text'], esc_html__( 'Percentage text in product labels', 'merchant' ) );
				}
			}
		}
	}

	/**
     * Add option
     *
	 * @param $options
	 *
	 * @return mixed
	 */
	public function add_pre_order_option( $options ) {
        if ( class_exists( 'Merchant_Pre_Orders' ) && Merchant_Modules::is_module_active( Merchant_Pre_Orders::MODULE_ID ) ) {
            $options['pre-order'] = esc_html__( 'Pre-Order Products', 'merchant' );
        }

        return $options;
    }

	/**
	 * Function for `woocommerce_blocks_product_grid_item_html` filter-hook.
	 *
	 * @param string      $html    Product grid item HTML.
	 * @param array       $data    Product data passed to the template.
	 * @param \WC_Product $product Product object.
	 *
	 * @return string
	 */
	public function products_block( $html, $data, $product ) {

		/**
		 * Filters the HTML for products in the grid.
		 *
		 * @param string $html Product grid item HTML.
		 * @param array $data Product data passed to the template.
		 * @param \WC_Product $product Product object.
		 * @return string Updated product grid item HTML.
		 *
		 * @since 1.9.12
		 */
		return apply_filters(
			'merchant_blocks_product_grid_item_html',
			"<li class=\"wc-block-grid__product merchant_product-labels-grid_item_html\">
				<a href=\"{$data->permalink}\" class=\"wc-block-grid__product-link\">
					{$data->image}
					{$data->title}
					{$this->get_labels( $product, 'archive' )}
				</a>
				{$data->price}
				{$data->rating}
				{$data->button}
			</li>",
			$data,
			$product
		);
	}

	/**
     * Remove default sale message
     *
	 * @param $html
	 *
	 * @return string
	 */
	public function remove_on_sale( $html ) {
		return '';
	}

	/**
	 * Admin enqueue CSS.
	 *
	 * @return void
	 */
	public function admin_enqueue_css() {
		$page   = ( ! empty( $_GET['page'] ) ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$module = ( ! empty( $_GET['module'] ) ) ? sanitize_text_field( wp_unslash( $_GET['module'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( 'merchant' === $page && self::MODULE_ID === $module ) {
			wp_enqueue_style( 'merchant-' . self::MODULE_ID, MERCHANT_URI . 'assets/css/modules/' . self::MODULE_ID . '/product-labels.min.css', array(), MERCHANT_VERSION );
			wp_enqueue_style( 'merchant-admin-' . self::MODULE_ID, MERCHANT_URI . 'assets/css/modules/' . self::MODULE_ID . '/admin/preview.min.css', array(), MERCHANT_VERSION );
		}
	}

	/**
	 * Admin enqueue CSS.
	 *
	 * @return void
	 */
	public function admin_enqueue_js() {
		$page   = ( ! empty( $_GET['page'] ) ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$module = ( ! empty( $_GET['module'] ) ) ? sanitize_text_field( wp_unslash( $_GET['module'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( 'merchant' === $page && self::MODULE_ID === $module ) {
			wp_enqueue_script( 'merchant-' . self::MODULE_ID, MERCHANT_URI . 'assets/js/modules/product-labels/admin/preview.min.js', array( 'jquery', 'merchant-admin' ), '4.0.13',
				true );
		}
	}

	/**
	 * Enqueue CSS.
	 *
	 * @return void
	 */
	public function enqueue_css() {
		// Specific module styles.
		wp_enqueue_style( 'merchant-' . self::MODULE_ID, MERCHANT_URI . 'assets/css/modules/' . self::MODULE_ID . '/product-labels.min.css', array(), MERCHANT_VERSION );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_js() {
		wp_enqueue_script( 'merchant-' . self::MODULE_ID, MERCHANT_URI . 'assets/js/modules/' . self::MODULE_ID . '/product-labels.min.js', array(), MERCHANT_VERSION, true );
	}

	/**
	 * Render admin preview
	 *
	 * @param Merchant_Admin_Preview $preview
	 * @param string                 $module
	 *
	 * @return Merchant_Admin_Preview
	 */
	public function render_admin_preview( $preview, $module ) {
		if ( self::MODULE_ID === $module ) {
			ob_start();
			self::admin_preview_content();
			$content = ob_get_clean();

			// HTML.
			$preview->set_html( $content );

			// Label Text.
			$preview->set_text( 'label_text', '.merchant-onsale' );

			// Position.
			$preview->set_class( 'label_position', '.merchant-onsale', array( 'top-left', 'top-right' ) );
		}

		return $preview;
	}

	/**
	 * Admin preview content.
	 *
	 * @return void
	 */
	public function admin_preview_content() {
		?>
        <div class="merchant-product-labels-preview">
            <div class="image-wrapper">
                <div class="merchant-product-labels merchant-product-labels__regular" data-currency="<?php echo esc_attr( get_woocommerce_currency_symbol() ); ?>">
                    <span class="merchant-label merchant-label-top-left"></span>
                </div>
            </div>
            <h3><?php
				echo esc_html__( 'Product Title', 'merchant' ); ?></h3>
            <p><?php
				echo esc_html__( 'The product description normally goes here.', 'merchant' ); ?></p>
        </div>

		<?php
	}

	/**
	 * Get sale percentage & amount
	 *
	 * @return string label
	 */
	public function get_product_sale_data( $product, $label ) {
        $sale_data = array(
                'amount'     => 0,
                'percentage' => 0,
        );

        if ( $product->is_type( 'variable' ) ) {
	        $regular_price = (float) wc_get_price_to_display( $product, array( 'price' => $product->get_variation_regular_price( 'min' ) ) );
            $sale_price    = (float) wc_get_price_to_display( $product );

	        /**
	         * `merchant_product_labels_sale_data_sale_price`
	         *
	         * @since 1.10.5
	         */
	        $sale_price = apply_filters( 'merchant_product_labels_sale_data_sale_price', $sale_price, $product, $label );

	        if ( 0 !== $sale_price || ! empty( $sale_price ) ) {
		        $sale_data['amount']     = $regular_price - $sale_price;
		        $sale_data['percentage'] = $regular_price ? round( 100 - ( $sale_price / $regular_price * 100 ) ) : 0;
	        }
        } elseif ( $product->is_type( 'grouped' ) ) {
            $children_ids = $product->get_children();

	        $total_regular_price = 0;
	        $total_sale_price    = 0;

            foreach ( $children_ids as $child_id ) {
	            $child_product = wc_get_product( $child_id );

	            $regular_price = (float) $child_product->get_regular_price();
	            $sale_price    = (float) $child_product->get_sale_price();

                if ( $child_product->is_type( 'variable' ) ) {
	                $regular_price = (float) $child_product->get_variation_regular_price( 'min' );
	                $sale_price    = (float) $child_product->get_variation_sale_price( 'min' );
                }

	            $total_regular_price += $regular_price;
	            $total_sale_price    += ! empty( $sale_price ) ? $sale_price : $regular_price;
            }

	        if ( 0 !== $total_sale_price || ! empty( $total_sale_price ) ) {
		        $sale_data['amount']     = $total_regular_price - $total_sale_price;
		        $sale_data['percentage'] = $total_regular_price ? round( 100 - ( ( $total_sale_price / $total_regular_price ) * 100 ) ) : 0;
	        }
        } else {
            $regular_price = (float) wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) );
            $sale_price    = (float) wc_get_price_to_display( $product, array( 'price' => $product->get_sale_price() ) );

	        /**
	         * `merchant_product_labels_sale_data_sale_price`
             *
             * @since 1.10.5
	         */
            $sale_price = apply_filters( 'merchant_product_labels_sale_data_sale_price', $sale_price, $product, $label );

            if ( 0 !== $sale_price || ! empty( $sale_price ) ) {
	            $sale_data['amount']     = $regular_price - $sale_price;
	            $sale_data['percentage'] = $regular_price ? round( 100 - ( ( $sale_price / $regular_price ) * 100 ),0 ) : 0;
            }
        }

        /**
         * Filter the product sale dat .
         *
         * @param int        $percentage The product discount percentage.
         * @param WC_Product $product    The product object.
         * @param array      $label      The label data.
         *
         * @since 1.10.5
         */
        return apply_filters( 'merchant_product_labels_sale_data', $sale_data, $product, $label );
	}

	/**
	 * Custom CSS.
	 *
	 * @return string
	 */
	public function get_module_custom_css() {
		$css = '';

		return $css;
	}

	/**
	 * Admin custom CSS.
	 *
	 * @param string $css The custom CSS.
	 *
	 * @return string $css The custom CSS.
	 */
	public function admin_custom_css( $css ) {
		$css .= $this->get_module_custom_css();

		return $css;
	}

	/**
	 * Frontend custom CSS.
	 *
	 * @param string $css The custom CSS.
	 *
	 * @return string $css The custom CSS.
	 */
	public function frontend_custom_css( $css ) {
		// Append module custom CSS.
		$css .= $this->get_module_custom_css();

		// Append module custom CSS based on themes (ensure themes compatibility).
		// Detect the current theme.
		$theme      = wp_get_theme();
		$theme_name = $theme->get( 'Name' );

		// All themes.
		$css .= '
			.woocommerce .onsale,
			.wc-block-grid__product-onsale,
			.wc-block-grid__product .onsale { 
			    display: none !important;
			}
		';

		// Kadence
		if ( 'Kadence' === $theme_name ) {
			$css .= '
				.merchant_product-labels-grid_item_html a,
				.merchant_product-labels-grid_item_html .wc-block-grid__product-image,
				.merchant_product-labels-grid_item_html .wc-block-grid__product-image img {
					width: 100% !important;
				}
			';
		}

        if ( 'Flatsome' === $theme_name || 'Flatsome Child' === $theme_name ) {
            $css .= '
                .type-product .col-inner {
                    overflow: hidden;
                }
            ';
        }

        if ( 'OceanWP' === $theme_name ) {
            $css .= '
                .type-product .product-inner {
                    overflow: hidden;
                }
            ';
        }

        if ( 'Astra' === $theme_name ) {
            $css .= '
                .woocommerce-js div.product div.images.woocommerce-product-gallery .flex-viewport {
                    z-index: 999;
                }
                
                .ast-onsale-card {
                    display: none;
                }
                
                .merchant-product-labels__image img {
                    box-shadow: none;
                }
            ';
        }

        // Ultimate addon Elementor
        if ( defined( 'UAEL_FILE' ) ) {
            $css .= '
                .uael-woo-product-wrapper {
                    position: relative;
                }
            ';
        }

        // XStore Theme
        if ( defined( 'ETHEME_FW' ) ) {
            $css .= '
                .product-content .merchant-product-labels.position-top-left {
                    margin-left: 15px !important;
                }
                .product-content .merchant-product-labels.position-top-right {
                    margin-right: 15px !important;
                }
            ';
        }

		return $css;
	}

	/**
	 * Print shortcode content.
	 *
	 * @return string
	 */
	public function shortcode_handler() {
		if ( ! Merchant_Modules::is_module_active( $this->module_id ) ) {
			return '';
		}

		if ( ! $this->is_shortcode_enabled() ) {
			return '';
		}

		if ( ! is_singular( 'product' ) ) {
			// If user is admin, show error message.
			if ( current_user_can( 'manage_options' ) ) {
				return $this->shortcode_placement_error();
			}

			return '';
		}

		global $product;

		ob_start();

		echo wp_kses( $this->get_labels( $product, 'single' ), array(
			'div'    => array(
				'class' => array(),
				'style' => array(),
			),
			'strong' => array(),
			'span'   => array(
				'class' => array(),
				'style' => array(),
			),
			'img'   => array(
				'src'    => true,
				'width'  => true,
				'height' => true,
				'class'  => array(),
				'style'  => array(),
			),
		) );

		$shortcode_content = ob_get_clean();

		/**
		 * Filter the shortcode html content.
		 *
		 * @param string $shortcode_content shortcode html content
		 * @param string $module_id         module id
		 * @param int    $post_id           product id
		 *
		 * @since 1.8
		 */
		return apply_filters( 'merchant_module_shortcode_content_html', $shortcode_content, $this->module_id, get_the_ID() );
    }

	/**
	 * Single product output.
	 *
	 * @return void
	 */
	public function single_product_output() {
		if ( $this->is_shortcode_enabled() ) {
			return;
		}

		global $product;

		echo wp_kses( $this->get_labels( $product, 'single' ), array(
			'div'    => array(
				'class' => array(),
				'style' => array(),
			),
			'strong' => array(),
			'span'   => array(
				'class' => array(),
				'style' => array(),
			),
            'img'   => array(
				'src'    => true,
				'width'  => true,
				'height' => true,
				'class'  => array(),
				'style'  => array(),
			),
		) );
	}

	/**
     * Add class to image wrapper in Product page.
     *
	 * @param $classes
	 *
	 * @return mixed
	 */
	public function single_product_image_gallery_classes( $classes ) {
        $classes[] = 'merchant-product-labels-image-wrap';

        return $classes;
    }

	/**
	 * Loop product output.
	 *
	 * @return void
	 */
	public function loop_product_output() {
		global $product;

		echo wp_kses( $this->get_labels( $product, 'archive' ), array(
			'div'    => array(
				'class' => array(),
				'style' => array(),
			),
			'strong' => array(),
			'span'   => array(
				'class' => array(),
				'style' => array(),
			),
			'img'   => array(
				'src'    => true,
				'width'  => true,
				'height' => true,
				'class'  => array(),
				'style'  => array(),
			),
		) );
	}

    /**
     * Get module settings with defaults
     *
     * @return array Module settings
     */
    private function get_normalized_settings() {
        $settings = $this->get_module_settings();

        return array(
                'use_shortcode'    => $settings['use_shortcode'] ?? false,
                'multiple_labels'  => $settings['multiple_labels'] ?? false,
                'labels'           => $settings['labels'] ?? array(),
        );
    }

    /**
     * Check if label should be displayed based on status
     *
     * @param array $label Label configuration.
     *
     * @return bool True if label is active
     */
    private function is_label_active( $label ) {
        return ! isset( $label['campaign_status'] ) || $label['campaign_status'] !== 'inactive';
    }

    /**
     * Normalize label display settings with defaults
     *
     * @param array $label Label configuration.
     *
     * @return array Normalized label configuration
     */
    private function normalize_label_display_settings( $label ) {
        if ( ! isset( $label['show_pages'] ) ) {
            $label['show_pages'] = array( 'homepage', 'single', 'archive' );
        }

        if ( ! isset( $label['show_devices'] ) ) {
            $label['show_devices'] = array( 'desktop', 'mobile' );
        }

        return $label;
    }

    /**
     * Get taxonomy mapping for display rules
     *
     * @return array Taxonomy map with taxonomy and slug keys
     */
    private function get_taxonomy_map() {
        return array(
                'by_category' => array(
                        'taxonomy' => 'product_cat',
                        'key'      => 'product_cats',
                ),
                'by_tags'     => array(
                        'taxonomy' => 'product_tag',
                        'key'      => 'product_tags',
                ),
                'by_brands'   => array(
                        'taxonomy' => 'product_brand',
                        'key'      => 'product_brands',
                ),
        );
    }

    /**
     * Check if product has taxonomy term
     *
     * @param WC_Product $product Product object.
     * @param array      $label Label configuration.
     * @param string     $display_rule Display rule type.
     *
     * @return bool True if product has matching term
     */
    private function product_has_taxonomy_term( $product, $label, $display_rule ) {
        $taxonomy_map = $this->get_taxonomy_map();

        if ( ! isset( $taxonomy_map[ $display_rule ] ) ) {
            return false;
        }

        $taxonomy = $taxonomy_map[ $display_rule ]['taxonomy'];
        $key      = $taxonomy_map[ $display_rule ]['key'];
        $slugs    = $label[ $key ] ?? array();

        foreach ( $slugs as $slug ) {
            if ( has_term( $slug, $taxonomy, $product->get_id() ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if product matches specific products rule
     *
     * @param WC_Product $product Product object.
     * @param array      $label Label configuration.
     *
     * @return bool True if product ID matches
     */
    private function product_matches_specific_products( $product, $label ) {
        $product_ids = $label['product_ids'] ?? array();
        $product_ids = merchant_parse_product_ids( $product_ids );

        return in_array( $product->get_id(), $product_ids, true );
    }

    /**
     * Check if product should display label based on display rule
     *
     * @param WC_Product $product Product object.
     * @param array      $label Label configuration.
     *
     * @return bool True if product matches display rule
     */
    private function product_matches_display_rule( $product, $label ) {
        $display_rule = $label['display_rules'] ?? 'products_on_sale';

        switch ( $display_rule ) {
            case 'featured_products':
                return $this->is_featured( $product );

            case 'products_on_sale':
                return $this->is_on_sale( $product );

            case 'by_category':
            case 'by_tags':
            case 'by_brands':
                return $this->product_has_taxonomy_term( $product, $label, $display_rule );

            case 'out_of_stock':
                return $this->is_out_of_stock( $product );

            case 'pre-order':
                return $this->is_pre_order( $product );

            case 'new_products':
                if ( isset( $label['new_products_days'] ) ) {
                    return $this->is_new( $product, $label['new_products_days'] );
                }
                return false;

            case 'specific_products':
                return $this->product_matches_specific_products( $product, $label );

            case 'all_products':
                return true;

            default:
                return false;
        }
    }

    /**
     * Get product sale data for label replacements
     *
     * @param WC_Product $product Product object.
     * @param array      $label Label configuration.
     *
     * @return array Sale data with amount and percentage
     */
    private function get_sale_replacement_data( $product, $label ) {
        if ( ! $this->is_on_sale( $product ) ) {
            return array(
                    'amount'     => '',
                    'percentage' => '',
            );
        }

        $sale_data = $this->get_product_sale_data( $product, $label );
        if ( ! is_array( $sale_data ) ) {
            $sale_data = array( 'amount' => 0, 'percentage' => 0 );
        }

        $amount     = isset( $sale_data['amount'] ) && $sale_data['amount'] > 0
                ? wc_price( $sale_data['amount'] )
                : '';

        $percentage = isset( $sale_data['percentage'] ) && $sale_data['percentage'] > 0
                ? $sale_data['percentage'] . '%'
                : '';

        return array(
                'amount'     => $amount,
                'percentage' => $percentage,
        );
    }

    /**
     * Get product inventory data for label replacements
     *
     * @param WC_Product $product Product object.
     *
     * @return array Inventory data with status and quantity
     */
    private function get_inventory_replacement_data( $product ) {
        $status   = $product->is_in_stock() ? esc_html__( 'In stock', 'merchant' ) : esc_html__( 'Sold out', 'merchant' );
        $quantity = $product->get_stock_quantity();

        return array(
                'status'   => $status,
                'quantity' => $quantity,
        );
    }

    /**
     * Replace shortcodes in label HTML with product data
     *
     * @param string     $label_html Label HTML with shortcodes.
     * @param WC_Product $product Product object.
     * @param array      $label Label configuration.
     *
     * @return string Label HTML with replaced values
     */
    private function replace_label_shortcodes( $label_html, $product, $label ) {
        $sale_data      = $this->get_sale_replacement_data( $product, $label );
        $inventory_data = $this->get_inventory_replacement_data( $product );

        return str_replace(
                array(
                        '{sale}',
                        '{sale_amount}',
                        '{inventory}',
                        '{inventory_quantity}',
                ),
                array(
                        $sale_data['percentage'],
                        $sale_data['amount'],
                        $inventory_data['status'],
                        $inventory_data['quantity'],
                ),
                $label_html
        );
    }

    /**
     * Process single label and return label data
     *
     * @param array      $label Label configuration.
     * @param WC_Product $product Product object.
     * @param string     $context Display context.
     *
     * @return array|null Label data array or null if not displayed
     */
    private function process_single_label( $label, $product, $context ) {
        // Check if label is active
        if ( ! $this->is_label_active( $label ) ) {
            return null;
        }

        // Normalize display settings
        $label = $this->normalize_label_display_settings( $label );

        // Check if label should show in this context
        if ( ! $this->show_label( $label, $context ) ) {
            return null;
        }

        // Check if product is excluded
        if ( $this->is_product_excluded( $product->get_id(), $label ) ) {
            return null;
        }

        // Check if product matches display rule
        if ( ! $this->product_matches_display_rule( $product, $label ) ) {
            return null;
        }

        // Get and process label HTML
        $label_html = $this->label( $label );
        $label_html = $this->replace_label_shortcodes( $label_html, $product, $label );

        return array(
                'label' => $label,
                'html'  => $label_html,
        );
    }

    /**
     * Collect all matching labels for product
     *
     * @param array      $labels All labels configuration.
     * @param WC_Product $product Product object.
     * @param string     $context Display context.
     * @param bool       $multiple_labels Whether to collect multiple labels.
     *
     * @return array Array of matching label data
     */
    private function collect_matching_labels( $labels, $product, $context, $multiple_labels ) {
        $matching_labels = array();

        foreach ( $labels as $label ) {
            $label_data = $this->process_single_label( $label, $product, $context );

            if ( $label_data ) {
                $matching_labels[] = $label_data;

                // Stop after first match if multiple labels disabled
                if ( ! $multiple_labels ) {
                    break;
                }
            }
        }

        return $matching_labels;
    }

    /**
     * Get device visibility CSS classes
     *
     * @param array $label Label configuration.
     *
     * @return string CSS classes for device visibility
     */
    private function get_device_visibility_classes( $label ) {
        if ( ! isset( $label['show_devices'] ) ) {
            return '';
        }

        $classes = '';
        foreach ( $label['show_devices'] as $device ) {
            $classes .= ' show-on-' . $device;
        }

        return $classes;
    }

    /**
     * Get label shape CSS class
     *
     * @param array $label Label configuration.
     *
     * @return string CSS class for label shape
     */
    private function get_label_shape_class( $label ) {
        $label_type = $label['label_type'] ?? 'text';

        if ( $label_type !== 'text' ) {
            return '';
        }

        $shape = $label['label_text_shape'] ?? 'text-shape-1';
        return ' merchant-product-labels__' . $shape;
    }

    /**
     * Get label wrapper CSS classes
     *
     * @param array $label Label configuration.
     *
     * @return string CSS classes for label wrapper
     */
    private function get_label_wrapper_classes( $label ) {
        $label_type = $label['label_type'] ?? 'text';
        $position = $label['position_anchor'] ?? 'top-left';

        $classes = 'merchant-product-labels__label-wrapper';
        $classes .= ' merchant-product-labels__' . $label_type;
        $classes .= $this->get_label_shape_class( $label );
        $classes .= $position === 'top-right' ? ' position-top-right' : ' position-top-left';

        return $classes;
    }

    /**
     * Render single label HTML
     *
     * @param array $label_data Label data with configuration and HTML.
     *
     * @return string Rendered label HTML
     */
    private function render_single_label( $label_data ) {
        $label = $label_data['label'];
        $label_html = $label_data['html'];
        $label_styles = $this->get_shape_based_styles( $label );
        $label_classes = $this->get_label_wrapper_classes( $label );

        return sprintf(
                '<div class="%s" style="%s">%s</div>',
                esc_attr( $label_classes ),
                merchant_array_to_css( $label_styles ),
                $label_html
        );
    }

    /**
     * Get main container CSS classes
     *
     * @param bool   $is_shortcode Whether using shortcode.
     * @param string $context Display context.
     * @param string $device_classes Device visibility classes.
     *
     * @return string CSS classes for main container
     */
    private function get_main_container_classes( $is_shortcode, $context, $device_classes ) {
        $classes = 'merchant-product-labels';
        $classes .= ' merchant-product-labels__' . ( ( $is_shortcode && $context === 'single' ) ? 'shortcode' : 'regular' );
        $classes .= ' merchant-product-labels--multiple';
        $classes .= $device_classes;

        return $classes;
    }

    /**
     * Render all matching labels
     *
     * @param array  $matching_labels Array of matching label data.
     * @param bool   $is_shortcode Whether using shortcode.
     * @param string $context Display context.
     *
     * @return string Rendered labels HTML
     */
    private function render_labels_output( $matching_labels, $is_shortcode, $context ) {
        if ( empty( $matching_labels ) ) {
            return '';
        }

        $output = '';
        $device_classes = '';

        foreach ( $matching_labels as $label_data ) {
            // Get device visibility classes from first label
            if ( empty( $device_classes ) ) {
                $device_classes = $this->get_device_visibility_classes( $label_data['label'] );
            }

            $output .= $this->render_single_label( $label_data );
        }

        $main_classes = $this->get_main_container_classes( $is_shortcode, $context, $device_classes );

        return sprintf(
                '<div class="%s">%s</div>',
                esc_attr( $main_classes ),
                $output
        );
    }

    /**
     * Get labels group.
     *
     * @param WC_Product $product Product object.
     * @param string     $context Context archive or single.
     *
     * @return string Product labels HTML.
     */
    public function get_labels( $product, $context = 'both' ) {
        $settings = $this->get_normalized_settings();

        // Handle legacy mode
        if ( empty( $settings['labels'] ) ) {
            return $this->legacy_product_label( $product );
        }

        // Collect matching labels
        $matching_labels = $this->collect_matching_labels(
                $settings['labels'],
                $product,
                $context,
                $settings['multiple_labels']
        );

        // Render and return output
        return $this->render_labels_output(
                $matching_labels,
                $settings['use_shortcode'],
                $context
        );
    }

    /**
     * Get all styles for the current label
     *
	 * @param $label
	 *
	 * @return array
	 */
	public function get_shape_based_styles( $label ) {
		$styles = array();

		if ( ! is_array( $label ) || empty( $label ) ) {
			return $styles;
		}

		$label_type  = $label['label_type'] ?? 'text';
		$label_shape = $label['label_text_shape'] ?? 'text-shape-1';

		$styles['margin']  = 0;
		$styles['padding'] = 0;

		if ( $label_type === 'text' ) {
			$styles['width']     = ( $label['label_width'] ?? 100 ) . 'px';
			$styles['height']    = ( $label['label_height'] ?? 32 ) . 'px';
			$styles['font-size'] = ( $label['font_size'] ?? 14 ) . 'px';

			$font_style = $label['font_style'] ?? 'normal';

			if ( strpos( $font_style, 'bold' ) !== false ) {
				$styles['font-weight'] = 'bold';
			}

			if ( strpos( $font_style, 'italic' ) !== false ) {
				$styles['font-style'] = 'italic';
			}

			$styles['background-color'] = $label['background_color'] ?? '#212121';
			$styles['color']            = $label['text_color'] ?? '#ffffff';
			$styles['border-radius']    = ( $label['shape_radius'] ?? 5 ) . 'px';
		}

		// Always use absolute positioning
		$x = $label['position_x'] ?? 10;
		$y = $label['position_y'] ?? 10;
		$x_unit = $label['position_x_unit'] ?? 'px';
		$anchor = $label['position_anchor'] ?? 'top-left';

		$styles['top'] = $y . 'px';

		// Handle anchor point - use left or right based on anchor
		if ( $anchor === 'top-right' ) {
			$styles['right'] = $x . $x_unit;
		} else {
			// top-left (default)
			$styles['left'] = $x . $x_unit;
		}

		// Apply rotation for shapes 5 and 6 based on anchor point
		if ( $label_type === 'text' && in_array( $label_shape, array( 'text-shape-5', 'text-shape-6' ), true ) ) {
			$is_top_right = ( $anchor === 'top-right' );

			if ( $label_shape === 'text-shape-5' ) {
				$rotation = $is_top_right ? '45deg' : '-45deg';
				$translateX = $is_top_right ? '50%' : '-50%';
				$translateY = '50%';
				$origin = $is_top_right ? 'right' : 'left';
			} else { // text-shape-6
				$rotation = $is_top_right ? '45deg' : '-45deg';
				$translateX = $is_top_right ? '50%' : '-50%';
				$translateY = '25%';
				$origin = $is_top_right ? 'right' : 'left';
			}

			$styles['transform'] = "rotate({$rotation}) translate({$translateX}, {$translateY})";
			$styles['transform-origin'] = $origin;
		}


		return $styles;
	}

	/**
     * Should show the label for current page.
     *
	 * @param $label
	 *
	 * @return bool
	 */
	public function show_label( $label, $context = '' ) {
		$show       = false;
        $show_pages = $label['show_pages' ] ?? array();

        if ( empty( $show_pages ) ) {
            return $show;
        }

		if ( is_product() && in_array( 'single', $show_pages, true ) ) {
			$show = true;
		}

		if ( in_array( 'archive', $show_pages, true ) && ( is_product_taxonomy() || is_shop() ) ) {
			$show = true;
		}

        // Block
		if ( in_array( 'archive', $show_pages, true ) && $context === 'archive' ) {
			$show = true;

            // Don't show for blocks on homepage if `homepage` is unchecked
            if ( ! in_array( 'homepage', $show_pages, true ) && is_front_page() ) {
	            $show = false;
            }
		}

		if ( in_array( 'homepage', $show_pages, true ) && is_front_page() ) {
			$show = true;
		}

		return $show;
	}

	/**
	 * Product label output.
	 *
	 * @return string legacy product label html.
	 */
	private function legacy_product_label( $product ) {
		if ( $this->is_shortcode_enabled() ) {
			return '';
		}

		global $product;
		$settings = $this->get_module_settings();
		$styles   = array();

		if ( ! empty( $product ) && $this->is_on_sale( $product ) ) {
			$label_text = $settings['label_text'];
			if ( ! empty( $settings['display_percentage'] ) ) {
				if ( $product->is_type( 'variable' ) ) {
					$percentages = array();
					$prices      = $product->get_variation_prices();

					foreach ( $prices['price'] as $key => $price ) {
						if ( $prices['regular_price'][ $key ] !== $price ) {
							$percentages[] = round( 100 - ( floatval( $prices['sale_price'][ $key ] ) / floatval( $prices['regular_price'][ $key ] ) * 100 ) );
						}
					}

					$percentage = max( $percentages );
				} elseif ( $product->is_type( 'grouped' ) ) {
					$percentages  = array();
					$children_ids = $product->get_children();

					foreach ( $children_ids as $child_id ) {
						$child_product = wc_get_product( $child_id );
						$regular_price = (float) $child_product->get_regular_price();
						$sale_price    = (float) $child_product->get_sale_price();

						if ( 0 !== $sale_price || ! empty( $sale_price ) ) {
							$percentages[] = round( 100 - ( ( $sale_price / $regular_price ) * 100 ) );
						}
					}
					$percentage = max( $percentages );
				} else {
					$regular_price = (float) $product->get_regular_price();
					$sale_price    = (float) $product->get_sale_price();

					if ( 0 !== $sale_price || ! empty( $sale_price ) ) {
						$percentage = round( 100 - ( ( $sale_price / $regular_price ) * 100 ) );
					}
				}

				$label_text = str_replace( '{value}', $percentage, Merchant_Translator::translate( $settings['percentage_text'] ) );
			}

			$styles['background-color'] = isset( $settings['background_color'] ) ? $settings['background_color'] : '#212121';
			$styles['color']            = isset( $settings['text_color'] ) ? $settings['text_color'] : '#ffffff';
			$styles['border-radius']    = isset( $settings['label_shape'] ) ? $settings['label_shape'] . 'px' : 8 . 'px';

			return '<div class="merchant-product-labels merchant-product-labels__regular position-' . esc_attr( $settings['label_position'] ) . '"><span class="merchant-label merchant-label-'
					. esc_attr( $settings['label_position'] ) . ' merchant-onsale-shape-' . esc_attr( $settings['label_shape'] ) . '" style="' . merchant_array_to_css( $styles )
					. '">' . esc_html( Merchant_Translator::translate( $label_text ) ) . '</span></div>';
		}

		return '';
	}

	/**
	 * Get label HTML.
	 *
	 * @return string
	 */
	public function label( $label_data ) {
		$html = '';
		if ( empty( $label_data['label'] ) ) {
			return $html;
		}

		$label_position = $label_data['label_position'] ?? 'top-left';

        $label_type = $label_data['label_type'] ?? 'text';

        $styles = array();

        if ( $label_type === 'text' ) {
	        $html = trim( esc_html( Merchant_Translator::translate( $label_data['label'] ) ) );
        } else {
	        $styles['width']  = ( $label_data['label_width'] ?? 45 ) . 'px';
	        $styles['height'] = ( $label_data['label_height'] ?? 45 ) . 'px';

            $custom_shape_id = $label_data['label_image_shape_custom'] ?? false;
            $shape_url       = $custom_shape_id ? wp_get_attachment_url( $custom_shape_id ) : MERCHANT_URI . 'assets/images/icons/' . self::MODULE_ID . '/' . ( $label_data['label_image_shape'] ?? 'image-shape-1' ) .'.svg';

	        $html = '<img src="' . esc_url( $shape_url ) . '"/>';
        }


		$label = '<span class="merchant-label merchant-label-' . esc_attr( $label_position ) . '" style="' . merchant_array_to_css( $styles ) . '">'
				. $html . '</span>';

		/**
		 * Filter the single product label.
		 *
		 * @param string $label      The label HTML.
		 * @param array  $label_data The label data.
		 *
		 * @since 1.6
		 */
		return apply_filters( 'merchant_product_label', $label, $label_data );
	}

	/**
	 * Get label data.
	 *
	 * @param $product WC_Product product object.
	 *
	 * @return bool
	 */
	private function is_on_sale( $product ) {
		return $product !== null && $product->is_on_sale();
	}

	/**
	 * Check if product is in categories.
	 *
	 * @param $product WC_Product product object.
	 * @param $slugs    array category slugs.
	 *
	 * @return bool
	 */
	private function is_in_category( $product, $slugs ) {
		if ( ! is_array( $slugs ) ) {
			$slugs = array( $slugs );
		}

		$terms = get_the_terms( $product->get_id(), 'product_cat' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				if ( in_array( $term->slug, $slugs, true ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if product is out of stock.
	 *
	 * @param $product WC_Product product object.
	 *
	 * @return bool
	 */
	private function is_out_of_stock( $product ) {
		return $product !== null && ! $product->is_in_stock();
	}

	/**
	 * Check if product is featured.
	 *
	 * @param $product WC_Product product object.
	 *
	 * @return bool
	 */
	private function is_featured( $product ) {
		return $product !== null && $product->is_featured();
	}

	/**
	 * Check if the product is new.
	 *
	 * @param $product WC_Product product object.
	 * @param $days    number of days to check.
	 *
	 * @return bool
	 */
	private function is_new( $product, $days ) {
		$product_creation_date = get_the_date( 'Y-m-d', $product->get_id() );

		$current_date    = gmdate( 'Y-m-d' );
		$days_difference = abs( strtotime( $current_date ) - strtotime( $product_creation_date ) ) / DAY_IN_SECONDS;

		return $days_difference <= $days;
	}

	/**
     * Check if product is a pre-order item.
     *
	 * @param $product
	 *
	 * @return bool
	 */
	public function is_pre_order( $product ) {
        if ( ! is_object( $product ) ) {
            return false;
        }

		if ( class_exists( 'Merchant_Pre_Orders' ) && Merchant_Modules::is_module_active( Merchant_Pre_Orders::MODULE_ID ) && class_exists( 'Merchant_Pre_Orders_Main_Functionality' ) ) {
			$available_pre_order = Merchant_Pre_Orders_Main_Functionality::available_product_rule( $product->get_id() );

			if ( ! empty( $available_pre_order ) ) {
				return true; // Pre-order available
			}

            // If Pre-order Not available for variable, check if available for any of its variation
			if ( $product->is_type( 'variable' ) ) {
				$variations = $product->get_available_variations();

				foreach ( $variations as $variation ) {
					$variation_id = $variation['variation_id'];

					// Check if the variation is available for pre-order
					$available_pre_order = Merchant_Pre_Orders_Main_Functionality::available_product_rule( $variation_id );

					if ( ! empty( $available_pre_order ) ) {
						return true; // Pre-order available for variation
					}
				}
			}
		}

		return false;
	}

	/**
	 * Migrate Exclusion list which was introduced later for the Excluded products.
	 *
	 * By default, it's off. So turn it off if condition matches.
	 *
	 * @return void
	 */
	private function migrate_exclusion_list() {
		$option = 'merchant_' . $this->module_id .'_exclusion_list';

		if ( get_option( $option, false ) || ! method_exists( 'Merchant_Admin_Options', 'set' ) ) {
			return;
		}

		$labels = Merchant_Admin_Options::get( $this->module_id, 'labels', array() );
		if ( ! empty( $labels ) ) {
			$update = false;
			foreach ( $labels as $key => $offer ) {
				$excluded_products   = $offer['excluded_products'] ?? '';
				$excluded_categories = $offer['excluded_categories'] ?? array();
				$excluded_tags       = $offer['excluded_tags'] ?? array();
				$excluded_brands     = $offer['excluded_brands'] ?? array();

				if ( ! empty( $excluded_products ) || ! empty( $excluded_categories ) || ! empty( $excluded_tags ) || ! empty( $excluded_brands ) ) {
					$labels[ $key ]['exclusion_enabled'] = true;
					$update = true;
				}
			}

			// Update only if necessary.
			if ( $update ) {
				Merchant_Admin_Options::set( $this->module_id, 'labels', $labels );
			}

			update_option( $option, true, false );
		}
	}

	/**
	 * Check if product is excluded based on label exclusion settings
	 *
	 * @param int   $product_id Product ID
	 * @param array $label     Label settings
	 * @return bool
	 * @since 2.1.8
	 */
	private function is_product_excluded( $product_id, $label ) {
        $display_rule = $label['display_rules'] ?? 'products_on_sale';
		$product = wc_get_product( $product_id );
		$_product_id = $product && $product->is_type( 'variation' ) ? $product->get_parent_id() : $product_id;

		// Exclude products
		$exclude_products = ! empty( $label['exclude_products_toggle'] );
		if ( $exclude_products ) {
			$excluded_product_ids = $label['excluded_products'] ?? array();
			$excluded_product_ids = merchant_parse_product_ids( $excluded_product_ids );

			if ( in_array( (int) $product_id, $excluded_product_ids, true ) || in_array( (int) $_product_id, $excluded_product_ids, true ) ) {
				return true;
			}
		}

		// Exclude categories (only when not targeting specific categories)
		$exclude_categories = ! empty( $label['exclude_categories_toggle'] );
		if ( $exclude_categories && $display_rule !== 'by_category' ) {
			$excluded_categories_slugs = $label['excluded_categories'] ?? array();

			if ( ! empty( $excluded_categories_slugs ) && has_term( $excluded_categories_slugs, 'product_cat', $_product_id ) ) {
				return true;
			}
		}

		// Exclude tags (only when not targeting specific tags)
		$exclude_tags = ! empty( $label['exclude_tags_toggle'] );
		if ( $exclude_tags && $display_rule !== 'by_tags' ) {
			$excluded_tags_slugs = $label['excluded_tags'] ?? array();

			if ( ! empty( $excluded_tags_slugs ) && has_term( $excluded_tags_slugs, 'product_tag', $_product_id ) ) {
				return true;
			}
		}

		// Exclude brands (only when not targeting specific brands)
		$exclude_brands = ! empty( $label['exclude_brands_toggle'] );
		if ( $exclude_brands && $display_rule !== 'by_brands' ) {
			$excluded_brands_slugs = $label['excluded_brands'] ?? array();

			if ( ! empty( $excluded_brands_slugs ) && has_term( $excluded_brands_slugs, 'product_brand', $_product_id ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Migrate exclusion fields for backward compatibility
	 *
	 * @since 2.1.8
	 */
    private function migrate_exclusion_fields() {
        $option_name = 'merchant_' . $this->module_id . '_ex_fields';
        if ( ! get_option( $option_name, false ) ) {
            $labels = Merchant_Admin_Options::get( self::MODULE_ID, 'labels', array() );
            if ( empty( $labels ) ) {
                // No labels to migrate, so we can skip this.
                update_option( $option_name, true, false );

                return;
            }
            $needs_migration = false;
            $updated_labels = array();
            foreach ( $labels as $label ) {
                $n = 0;
                // Check if individual toggles don't exist yet
                if ( ! isset( $label['exclude_products_toggle'] ) ) {
                    $label['exclude_products_toggle'] = ! empty( $label['excluded_products'] );
                    ++ $n;
                }

                if ( ! isset( $label['exclude_categories_toggle'] ) ) {
                    $label['exclude_categories_toggle'] = ! empty( $label['excluded_categories'] );
                    ++ $n;
                }

                if ( ! isset( $label['exclude_tags_toggle'] ) ) {
                    $label['exclude_tags_toggle'] = ! empty( $label['excluded_tags'] );
                    ++ $n;
                }

                if ( ! isset( $label['exclude_brands_toggle'] ) ) {
                    $label['exclude_brands_toggle'] = ! empty( $label['excluded_brands'] );
                    ++ $n;
                }
                if ( $n > 0 ) {
                    $needs_migration = true; // Set to true if any iteration needs migration
                }

                $updated_labels[] = $label;
            }

            if ( $needs_migration ) {
                Merchant_Admin_Options::set( self::MODULE_ID, 'labels', $updated_labels );
            }
            // We set autoload to false because this is a flag option only and needed once.
            update_option( $option_name, true, false );
        }
    }
}

// Initialize the module.
add_action( 'init', function () {
	Merchant_Modules::create_module( new Merchant_Product_Labels() );
} );
