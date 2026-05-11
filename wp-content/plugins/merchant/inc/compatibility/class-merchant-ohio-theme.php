<?php
/**
 * Ohio Theme compatibility layer
 *
 * @package Merchant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ohio Theme compatibility layer
 */
if ( ! class_exists( 'Merchant_Ohio_Theme' ) ) {
	class Merchant_Ohio_Theme {

		/**
		 * Constructor.
		 */
		public function __construct() {
			if ( ( is_admin() && ! wp_doing_ajax() ) || ! merchant_is_ohio_active() ) {
				return;
			}

			// Custom CSS.
			add_filter( 'merchant_custom_css', array( $this, 'frontend_custom_css' ) );

			$ajax_cart = ! class_exists( 'OhioOptions' ) || OhioOptions::get( 'woocommerce_product_ajax_cart', true );

            // Custom scripts
			add_action( 'wp_footer', function() {
				?>
                <script>
					jQuery( document ).ready( function ( $ ) {

						/**
                         * Ohio theme using their own AJAX scripts and not using 'added_to_cart' event which is required.
                         * So adding `added_to_cart` event here
 						 */
						const originalAjax = $.ajax;
						$.ajax = function( options ) {
							if (
                                options.url === wc_cart_fragments_params.ajax_url
                                && ( options.data.action === 'ohio_ajax_add_to_cart_woo' || options.data.action === 'ohio_ajax_add_to_cart_woo_single' ) ) {
								const originalSuccess = options.success;
								options.success = function(response) {
									// Call the original Ohio success handler
									originalSuccess.apply( this, arguments );
									if ( response && ! response.error ) {
										// Ensure fragments and cart_hash are present
										const fragments = response.fragments || {};
										const cart_hash = response.cart_hash || '';
										const $button = $( '.data_button_ajax.loading' ); // Find the button that triggered it

										// Trigger added_to_cart with the response data
										$( document.body ).trigger( 'added_to_cart', [ fragments, cart_hash, $button ] );
									}
								};
							}
							return originalAjax.apply( this, arguments );
						};

						// Add loading class to show loading indicator
						$( '.merchant-sticky-add-to-cart-wrapper button.button' ).addClass( 'data_button_ajax' );
						$( 'body' ).on( 'adding_to_cart', function( event, $button, data ) {
							if ( $button.closest( '.merchant-quick-view-content' ).length || $button.closest( '.merchant-sticky-add-to-cart-wrapper' ).length ) {
								$button.addClass( 'btn-loading' );
                            }
                        } );

						// Quick View, Wishlist, Product Labels, Product Video/Audio Position
						$( '.merchant-product-labels, .merchant-quick-view-button, .merchant-wishlist-button, .merchant-product-video, .merchant-product-audio' ).each( function() {
							const $product = $( this ).closest( 'li.product' );
							const $thumbnail = $product.find( '.product-item-thumbnail' );

							if ( $thumbnail.length ) {
								$( this )
									.appendTo( $thumbnail )
									.css( { 'visibility': 'visible' } );
							} else {
								$( this )
                                    .appendTo( $product )
									.css( { 'visibility': 'visible' } );
							}
						} );
					} );
                </script>
				<?php
			} );

			// Product labels
			if ( Merchant_Modules::is_module_active( Merchant_Product_Labels::MODULE_ID ) ) {
				$Product_Labels = Merchant_Modules::get_module( Merchant_Product_Labels::MODULE_ID );

				// Archives/Loop
				remove_action( 'woocommerce_before_shop_loop_item', array( $Product_Labels, 'loop_product_output' ) );
				add_action( 'woocommerce_after_shop_loop_item_title', array( $Product_Labels, 'loop_product_output' ) );

				// Single Product
				remove_action( 'woocommerce_product_thumbnails', array( $Product_Labels, 'single_product_output' ) );
				add_action( 'woocommerce_before_single_product_summary', array( $Product_Labels, 'single_product_output' ) );
			}

			// Quick View
			if ( Merchant_Modules::is_module_active( Merchant_Quick_View::MODULE_ID ) && $ajax_cart ) {
				$Quick_View = Merchant_Modules::get_module( Merchant_Quick_View::MODULE_ID );
				remove_action( 'woocommerce_after_shop_loop_item', array( $Quick_View, 'quick_view_button' ) );
				add_action( 'woocommerce_after_shop_loop_item_title', array( $Quick_View, 'quick_view_button' ) );
			}

			// Buy Now
			if ( Merchant_Modules::is_module_active( Merchant_Buy_Now::MODULE_ID ) && $ajax_cart ) {
				$Buy_Now = Merchant_Modules::get_module( Merchant_Buy_Now::MODULE_ID );
				remove_action( 'woocommerce_after_shop_loop_item', array( $Buy_Now, 'shop_archive_product_buy_now_button' ) );
				add_action( 'woocommerce_after_shop_loop_item_title', array( $Buy_Now, 'shop_archive_product_buy_now_button' ) );
			}

			if ( merchant_is_pro_active() ) {
				// FBT
				if ( Merchant_Modules::is_module_active( Merchant_Frequently_Bought_Together::MODULE_ID ) ) {
					add_filter( 'merchant_frequently_bought_together_woo_hook', function( $hook ) {
						return 'woocommerce_product_after_tabs';
					} );
				}

				// Recently Viewed Products
				if ( Merchant_Modules::is_module_active( Merchant_Recently_Viewed_Products::MODULE_ID ) ) {
					add_filter( 'merchant_recently_viewed_products_woo_hook', function( $hook ) {
						return 'woocommerce_product_after_tabs';
					} );

                    add_filter( 'merchant_recently_viewed_products_wrapper_classes', function( $classes ) {
	                    $classes[] = 'woo-products';

						return $classes;
					} );
				}

				// Wishlist
				if ( Merchant_Modules::is_module_active( Merchant_Wishlist::MODULE_ID ) ) {
					add_filter( 'merchant_wishlist_archive_woo_hook', function( $hook ) {
						return 'woocommerce_after_shop_loop_item_title';
					} );
                }

				// Advanced Reviews
				if ( Merchant_Modules::is_module_active( Merchant_Advanced_Reviews::MODULE_ID ) ) {
					add_filter( 'merchant_advanced_review_woo_hook', function( $hook ) {
						return 'woocommerce_product_after_tabs';
					} );
                }

				// Side Cart
				if ( Merchant_Modules::is_module_active( Merchant_Side_Cart::MODULE_ID ) ) {
                    // remove_filter( 'woocommerce_cart_item_name', 'ohio_add_cart_product_category', 99 );
				}

				// Product Video/Audio
				if ( Merchant_Modules::is_module_active( Merchant_Product_Video::MODULE_ID ) || Merchant_Modules::is_module_active( Merchant_Product_Audio::MODULE_ID ) ) {
                    // Archive
                    add_filter( 'merchant_product_video_before_woo_hook', function() {
                        return 'woocommerce_after_shop_loop_item_title';
                    } );

                    add_filter( 'merchant_product_audio_before_woo_hook', function() {
                        return 'woocommerce_after_shop_loop_item_title';
                    } );

                    /*
                     * Single Product
                     *
                     * `YITH_Featured_Audio_Video_Init` function is needed in Ohio theme for firing `woocommerce_single_product_image_thumbnail_html` action
                     * That's why just defining it here to make sure it exists.
                     * No need any implementation
                     *
                     */
					if ( ! function_exists( 'YITH_Featured_Audio_Video_Init' ) ) {
						function YITH_Featured_Audio_Video_Init() {}
                    }
				}

                if ( Merchant_Modules::is_module_active( Merchant_Complementary_Products::MODULE_ID ) ) {
                    // Complementary products
                    add_action( 'woocommerce_ajax_added_to_cart', array( $this, 'complementary_products_support' ) );
                }
			}
		}

		/**
		 * Frontend custom CSS.
		 *
		 * @param string $css The custom CSS.
		 * @return string $css The custom CSS.
		 */
		public function frontend_custom_css( $css ) {
			$css .= Merchant_Side_Cart::get_module_custom_css();

            $css .= '
                body .cart_item .product-name .variation,
                body .cart_item .product-total .variation {
                    display: block;
                }
                body .cart_item .product-name .variation dd,
                body .cart_item .product-total .variation dd {
                    margin-inline: 0;
                }
                body .cart_item .product-name .variation p,
                body .cart_item .product-total .variation p {
                    margin: 0;
                }
                body.wp-theme-ohio .merchant-cart-offers .merchant-cart-offers-container{
                    position: static;
                }
            ';

			// Product labels
			if ( Merchant_Modules::is_module_active( Merchant_Product_Labels::MODULE_ID ) ) {
				$css .= '
				    .product-item {
					    position: relative;
					}
					li.product .merchant-product-labels {
                        visibility: hidden;
                    }
				';
			}

			// Quick View
			if ( Merchant_Modules::is_module_active( Merchant_Quick_View::MODULE_ID ) ) {
				$css .= '
				    li.product .merchant-quick-view-button {
                        visibility: hidden;
                    }
				';
			}

            // Buy Now
			if ( Merchant_Modules::is_module_active( Merchant_Buy_Now::MODULE_ID ) ) {
				$css .= '
				    .data_button_ajax.btn-loading.loading:before {
                        position: absolute;
                    }
				';
			}

			if ( merchant_is_ohio_active() ) {
				// Wishlist
				if ( Merchant_Modules::is_module_active( Merchant_Wishlist::MODULE_ID ) ) {
					$css .= '
                        li.product .merchant-wishlist-button {
                            visibility: hidden;
                        }
                    ';
				}

				// Side Cart
				if ( Merchant_Modules::is_module_active( Merchant_Side_Cart::MODULE_ID ) ) {
					$css .= '
					    .merchant-side-cart-item .woo-product-name,
					    .merchant-side-cart-item .woo-category {
                            display: none !important;
                        }
                        .merchant-side-cart-widget .product_list_widget .merchant-quantity-wrap span.merchant-cart-item-name a {
                             display: block !important;
                        }
					';
				}

				// Advanced Review
				if ( Merchant_Modules::is_module_active( Merchant_Advanced_Reviews::MODULE_ID ) ) {
					$css .= '
						.merchant-adv-reviews-modal-photo-slider .star-rating:before,
						.merchant-adv-reviews .star-rating:before {
                            content: "★★★★★" !important;
                        }
					';
				}

                // Product Video/Audio
				if ( Merchant_Modules::is_module_active( Merchant_Product_Video::MODULE_ID ) || Merchant_Modules::is_module_active( Merchant_Product_Audio::MODULE_ID ) ) {
					$css .= '
						li.product:has(.merchant-product-video) .product-item-thumbnail .image-holder,
						li.product:has(.merchant-product-audio) .product-item-thumbnail .image-holder {
                            display: none;
                        }
                        li.product .merchant-product-video,
                        li.product .merchant-product-audio {
                            visibility: hidden;
                        }
					';
				}
			}

			return $css;
		}

        /**
         * Get the variation ID from attributes for a variable product.
         *
         * @param int $product_id The variable product ID.
         * @param array $variation_data The selected variation attributes.
         *
         * @return int The variation ID or 0 if not found.
         */
        private function get_variation_id_from_attributes( $product_id, $variation_data ) {
            // Get the product object.
            $product = wc_get_product( $product_id );
            if ( ! $product || $product->get_type() !== 'variable' ) {
                return 0;
            }

            // Ensure variation data keys are prefixed correctly.
            $formatted_variation_data = array();
            foreach ( $variation_data as $key => $value ) {
                // If the key doesn't start with 'attribute_', assume it's the attribute name and prefix it.
                $formatted_key                              = strpos( $key, 'attribute_' ) === 0 ? $key : 'attribute_' . sanitize_title( $key );
                $formatted_variation_data[ $formatted_key ] = sanitize_text_field( $value );
            }

            // Get available variations for comparison (optional debugging).
            $available_variations = $product->get_available_variations();
            $data_store           = WC_Data_Store::load( 'product' );
            $variation_id         = $data_store->find_matching_product_variation( $product, $formatted_variation_data );

            /**
             * Filter the variation ID found for a complementary product.
             *
             * @param int $variation_id The found variation ID (0 if not found).
             * @param int $product_id The variable product ID.
             * @param array $formatted_variation_data The formatted variation attributes.
             * @param array $available_variations The available variations for the product.
             *
             * @since 2.2.1
             */
            return apply_filters( 'merchant_ohio_complementary_products_variation_id',
                    (int) $variation_id,
                    $product_id,
                    $formatted_variation_data,
                    $available_variations
            );
        }

        /**
         * Add support for complementary products module with Ohio theme custom add to cart action.
         *
         * @param int $cart_product_id The ID of the product added to cart.
         *
         * @return void
         * @throws Exception When the cart fails to add the product.
         */
        public function complementary_products_support( $cart_product_id ) {
            if ( ! $this->should_handle_complementary_products() ) {
                return;
            }

            $request = $this->get_complementary_request_data();
            if ( ! $request ) {
                return;
            }

            $cart      = WC()->cart;
            $cart_item = $this->find_parent_cart_item( $cart, $cart_product_id, $request['variation_id'] );

            if ( ! $cart_item ) {
                return;
            }

            $this->mark_parent_cart_item(
                    $cart,
                    $cart_item['key'],
                    $cart_item['data'],
                    $request['campaign_id']
            );

            $this->add_complementary_products_to_cart(
                    $cart,
                    $cart_product_id,
                    $cart_item['key'],
                    $request['campaign_id'],
                    $request['products'],
                    $request['quantity']
            );
        }

        /**
         * Check if complementary products logic should run.
         *
         * @return bool
         */
        private function should_handle_complementary_products() {
            if ( ! Merchant_Modules::is_module_active( Merchant_Complementary_Products::MODULE_ID ) ) {
                return false;
            }

            if ( ! merchant_is_ohio_active() ) {
                return false;
            }

            if ( empty( $_POST['complementary_offer_products'] ) || '[]' === $_POST['complementary_offer_products'] ) {
                return false;
            }

            if (
                    ! isset( $_POST['complementary_offer_products_nonce'] )
                    || ! wp_verify_nonce(
                            sanitize_text_field( wp_unslash( $_POST['complementary_offer_products_nonce'] ) ),
                            'complementary_offer_products'
                    )
            ) {
                return false;
            }

            return true;
        }

        /**
         * Read and sanitize complementary products request data.
         *
         * We are ignoring nonce verification here because it is already verified in should_handle_complementary_products().
         *
         * @return array|null
         */
        private function get_complementary_request_data() {
            if ( ! isset( $_POST['complementary_offer_products'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
                return null;
            }
            //phpcs:ignore WordPress.Security.NonceVerification.Missing
            $complementary_products_json = sanitize_text_field( wp_unslash( $_POST['complementary_offer_products'] ) );
            $complementary_products      = json_decode( $complementary_products_json, true );

            if ( ! is_array( $complementary_products ) || empty( $complementary_products ) ) {
                return null;
            }

            //phpcs:ignore WordPress.Security.NonceVerification.Missing
            if ( ! isset( $_POST['complementary_offer_id'] ) ) {
                return null;
            }

            //phpcs:ignore WordPress.Security.NonceVerification.Missing
            $quantity = isset( $_POST['quantity'] ) ? absint( wp_unslash( $_POST['quantity'] ) ) : 1;

            //phpcs:ignore WordPress.Security.NonceVerification.Missing
            $variation_id = isset( $_POST['variation_id'] ) ? absint( wp_unslash( $_POST['variation_id'] ) ) : 0;

            return array(
                    'products'     => $complementary_products,
                    //phpcs:ignore WordPress.Security.NonceVerification.Missing
                    'campaign_id'  => sanitize_text_field( wp_unslash( $_POST['complementary_offer_id'] ) ),
                    'quantity'     => $quantity,
                    'variation_id' => $variation_id,
            );
        }

        /**
         * Find the parent cart item (main product) in the cart.
         *
         * @param WC_Cart $cart Cart instance.
         * @param int $product_id Product ID added to cart.
         * @param int $variation_id Variation ID (if any).
         *
         * @return array|null {
         * @type string $key
         * @type array $data
         * }
         */
        private function find_parent_cart_item( WC_Cart $cart, $product_id, $variation_id ) {
            $cart_items = $cart->get_cart();

            foreach ( $cart_items as $key => $cart_item ) {
                if ( $variation_id && isset( $cart_item['variation_id'] ) && (int) $cart_item['variation_id'] === (int) $variation_id ) {
                    return array(
                            'key'  => $key,
                            'data' => $cart_item,
                    );
                }

                if ( ! $variation_id && isset( $cart_item['product_id'] ) && (int) $cart_item['product_id'] === (int) $product_id ) {
                    return array(
                            'key'  => $key,
                            'data' => $cart_item,
                    );
                }
            }

            return null;
        }

        /**
         * Mark the parent cart item with complementary metadata.
         *
         * @param WC_Cart $cart Cart instance.
         * @param string $cart_item_key Cart item key.
         * @param array $cart_item_data Existing cart item data.
         * @param string $campaign_id Campaign/offer ID.
         *
         * @return void
         */
        private function mark_parent_cart_item( WC_Cart $cart, $cart_item_key, array $cart_item_data, $campaign_id ) {
            $cart_item_data['is_complementary_parent'] = true;
            $cart_item_data['merchant_added_via']      = 'complementary_products';
            $cart_item_data['merchant_module_id']      = Merchant_Complementary_Products::MODULE_ID;
            $cart_item_data['complementary_offer_id']  = $campaign_id;

            $cart->cart_contents[ $cart_item_key ] = array_merge( $cart->cart_contents[ $cart_item_key ], $cart_item_data );
        }

        /**
         * Add all complementary products to the cart.
         *
         * @param WC_Cart $cart
         * @param int $main_product_id
         * @param string $parent_cart_item_key
         * @param string $campaign_id
         * @param array $complementary_products
         * @param int $quantity
         *
         * @return void
         */
        private function add_complementary_products_to_cart(
                WC_Cart $cart,
                $main_product_id,
                $parent_cart_item_key,
                $campaign_id,
                $complementary_products,
                $quantity
        ) {
            foreach ( $complementary_products as $comp_product ) {
                $this->add_single_complementary_product(
                        $cart,
                        $main_product_id,
                        $parent_cart_item_key,
                        $campaign_id,
                        $comp_product,
                        $quantity
                );
            }
        }

        /**
         * Add a single complementary product to the cart.
         *
         * @param WC_Cart $cart
         * @param int $main_product_id
         * @param string $parent_cart_item_key
         * @param string $campaign_id
         * @param array $comp_product
         * @param int $quantity
         *
         * @return void
         */
        private function add_single_complementary_product(
                WC_Cart $cart,
                $main_product_id,
                $parent_cart_item_key,
                $campaign_id,
                array $comp_product,
                $quantity
        ) {
            $comp_product_id     = absint( $comp_product['product_id'] );
            $comp_product_object = wc_get_product( $comp_product_id );

            if ( ! $comp_product_object ) {
                wc_add_notice(
                        sprintf(
                                /* translators: %s: Product ID */
                                __( 'Complementary product with ID "%s" does not exist.', 'merchant' ),
                                $comp_product_id
                        ),
                        'error'
                );

                return;
            }

            $product_type   = sanitize_text_field( $comp_product['product_type'] );
            $variation_data = $this->sanitize_variation_data(
                    isset( $comp_product['variation_data'] ) ? $comp_product['variation_data'] : array()
            );

            $comp_variation_id = 0;
            if ( 'variable' === $product_type && ! empty( $variation_data ) ) {
                $comp_variation_id = $this->get_variation_id_from_attributes( $comp_product_id, $variation_data );
                if ( ! $comp_variation_id ) {
                    wc_add_notice(
                            sprintf(
                            /* translators: %s: Product name */
                                    __( 'Invalid variation selected for complementary product "%s".', 'merchant' ),
                                    $comp_product_object->get_name()
                            ),
                            'error'
                    );

                    return;
                }
            }

            $comp_cart_item_data = array(
                    'complementary_main_product' => $main_product_id,
                    'complementary_parent'       => $parent_cart_item_key,
                    'complementary_offer_id'     => $campaign_id,
                    'merchant_added_via'         => 'complementary_products',
                    'merchant_module_id'         => Merchant_Complementary_Products::MODULE_ID,
                    'is_complementary'           => true,
            );

            $added_to_cart = $cart->add_to_cart(
                    $comp_product_id,
                    $quantity,
                    $comp_variation_id,
                    $variation_data,
                    $comp_cart_item_data
            );

            if ( ! $added_to_cart ) {
                wc_add_notice(
                        sprintf(
                        /* translators: %s: Product name */
                                __( 'Failed to add complementary product "%s" to the cart.', 'merchant' ),
                                $comp_product_object->get_name()
                        ),
                        'error'
                );
            }
        }

        /**
         * Sanitize variation data array.
         *
         * @param array $variation_data
         *
         * @return array
         */
        private function sanitize_variation_data( array $variation_data ) {
            $sanitized = array();

            foreach ( $variation_data as $key => $value ) {
                $sanitized_key               = sanitize_key( $key );
                $sanitized_value             = map_deep( $value, 'sanitize_text_field' );
                $sanitized[ $sanitized_key ] = $sanitized_value;
            }

            return $sanitized;
        }
    }

	add_action( 'init', function() {
		new Merchant_Ohio_Theme();
	} );
}