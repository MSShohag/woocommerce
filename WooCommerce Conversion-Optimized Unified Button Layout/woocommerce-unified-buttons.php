<?php
// Adding custom unified buttons to WooCommerce product page --start

/**
 * 1. ADD CUSTOM BUTTONS (BUY NOW, MESSENGER, WHATSAPP)
 */
add_action( 'woocommerce_after_add_to_cart_button', 'custom_woo_unified_product_buttons', 15 );
function custom_woo_unified_product_buttons() {
    global $product;

    // --- Configuration (WHITE LABEL: Add Client Details Here) ---
    $phone_number = '1234567890';       // Replace with Client WhatsApp number (include country code, no +)
    $fb_page_id   = 'your_page_id';     // Replace with Client Messenger username/ID
    
    $product_id   = $product->get_id();
    $product_name = $product->get_name();
    $product_url  = get_permalink($product_id);
    
    // Initial Links
    $buy_now_url   = esc_url( add_query_arg( 'quick_buy', $product_id, wc_get_checkout_url() ) );
    $whatsapp_url  = "https://wa.me/" . $phone_number . "?text=" . rawurlencode("Hi, I'm interested in " . $product_name . " (" . $product_url . ")");
    $messenger_url = "https://m.me/" . $fb_page_id;

    // Buy Now Button
    echo '<a href="' . $buy_now_url . '" class="button buy-now-btn" data-parent-id="' . $product_id . '">Buy Now - Grab Your Offer</a>';
    
    // Messenger & WhatsApp
    echo '<a href="' . $messenger_url . '" class="button messenger-btn" target="_blank"><i class="fab fa-facebook-messenger"></i> Messenger</a>';
    echo '<a href="' . $whatsapp_url . '" class="button whatsapp-btn" target="_blank"><i class="fa fa-whatsapp" aria-hidden="true"></i> WhatsApp</a>';
    
    // JavaScript for Variable Product Support
    if ( $product->is_type( 'variable' ) ) {
        ?>
        <script type="text/javascript">
            jQuery(function($) {
                $('.variations_form').on('found_variation', function(event, variation) {
                    var baseUrl = "<?php echo wc_get_checkout_url(); ?>";
                    $('.buy-now-btn').attr('href', baseUrl + '?quick_buy=' + variation.variation_id);
                }).on('reset_data', function() {
                    var baseUrl = "<?php echo wc_get_checkout_url(); ?>";
                    var parentId = $('.buy-now-btn').data('parent-id');
                    $('.buy-now-btn').attr('href', baseUrl + '?quick_buy=' + parentId);
                });
            });
        </script>
        <?php
    }
}

/**
 * 2. BUY NOW LOGIC
 */
add_action( 'template_redirect', 'custom_woo_handle_quick_buy_logic' );
function custom_woo_handle_quick_buy_logic() {
    if ( isset( $_GET['quick_buy'] ) && !empty( $_GET['quick_buy'] ) ) {
        $id_to_add = absint( $_GET['quick_buy'] );
        WC()->cart->empty_cart();
        WC()->cart->add_to_cart( $id_to_add );
        wp_safe_redirect( wc_get_checkout_url() );
        exit;
    }
}

/**
 * 3. FINAL REFINED STYLING & QUANTITY JS
 */
add_action( 'wp_head', 'custom_woo_product_page_styles' );
function custom_woo_product_page_styles() {
    ?>
    <style>
        /* --- Container Layout --- */
        form.cart,
        .woocommerce-variation-add-to-cart {
            display: flex !important;
            flex-wrap: wrap !important;
            align-items: stretch !important;
            gap: 10px !important; 
            width: 100% !important;
            max-width: 100% !important;
        }

        /* --- ROW 1: Quantity + Buy Now --- */
        
        /* 1. Quantity Setup */
        form.cart .quantity,
        .woocommerce-variation-add-to-cart .quantity {
            order: 1 !important; 
            flex: 0 0 110px !important; 
            display: flex !important;
            flex-wrap: nowrap !important;
            align-items: stretch !important;
            justify-content: center !important;
            border-radius: 4px !important;
            background: #ffffff !important;
            border: 1px solid #d1d1d1 !important; 
            height: 48px !important; 
            padding: 0 !important;
            margin: 0 !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
        }

        form.cart .quantity input.qty {
            flex: 1 1 40px !important; 
            width: 40px !important;
            min-width: 30px !important;
            max-width: 40px !important;
            height: 100% !important;
            border: none !important;
            background: transparent !important;
            font-size: 17px !important;
            font-weight: normal !important;
            color: #333 !important;
            text-align: center !important;
            padding: 0 !important;
            margin: 0 !important;
            -moz-appearance: textfield !important;
            box-shadow: none !important;
        }
        
        form.cart .quantity input.qty::-webkit-outer-spin-button,
        form.cart .quantity input.qty::-webkit-inner-spin-button {
            -webkit-appearance: none !important;
            margin: 0 !important;
        }

        .custom-qty-btn {
            background: #ffffff !important; 
            border: none !important;
            color: #555 !important;
            flex: 0 0 34px !important; 
            width: 34px !important;
            max-width: 34px !important;
            padding: 0 !important;
            margin: 0 !important;
            font-size: 18px !important;
            font-weight: normal !important;
            cursor: pointer !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            transition: background 0.2s !important;
            box-sizing: border-box !important;
        }
        
        .custom-qty-btn:hover { 
            background: #f7f7f7 !important; 
        }

        /* Vertical dividers for quantity buttons */
        .custom-qty-btn.custom-minus { border-right: 1px solid #d1d1d1 !important; }
        .custom-qty-btn.custom-plus  { border-left: 1px solid #d1d1d1 !important; }

        /* 2. Buy Now Setup */
        form.cart .buy-now-btn,
        .woocommerce-variation-add-to-cart .buy-now-btn {
            order: 2 !important; 
            flex: 0 0 calc(100% - 120px) !important; 
            animation: custom-pulse 2s infinite !important; 
            background-color: #FFA500 !important; 
            color: #fff !important;
        }

        @keyframes custom-pulse {
            0% { box-shadow: 0 0 0 0 rgba(255, 165, 0, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(255, 165, 0, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 165, 0, 0); }
        }

        /* --- ROW 2: Add to Cart + Messenger + WhatsApp --- */
        form.cart .single_add_to_cart_button { order: 3 !important; flex: 1 1 0 !important; background-color: #FF5C00 !important; color: #fff !important; }
        form.cart .messenger-btn             { order: 4 !important; flex: 1 1 0 !important; background-color: #0084FF !important; color: #fff !important; }
        form.cart .whatsapp-btn              { order: 5 !important; flex: 1 1 0 !important; background-color: #25D366 !important; color: #fff !important; }

        /* --- Standardize Button Styling --- */
        form.cart .single_add_to_cart_button,
        form.cart .buy-now-btn,
        form.cart .messenger-btn,
        form.cart .whatsapp-btn {
            height: 48px !important; 
            border-radius: 5px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin: 0 !important;
            border: none !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            font-size: 13px !important;
            padding: 0 10px !important;
            text-decoration: none !important;
            text-align: center !important;
            line-height: 1.2 !important;
            box-sizing: border-box !important;
        }

        /* --- Mobile Responsiveness --- */
        @media (max-width: 768px) {
            form.cart .buy-now-btn { font-size: 12px !important; }
            form.cart .single_add_to_cart_button,
            form.cart .messenger-btn,
            form.cart .whatsapp-btn { font-size: 11px !important; padding: 0 5px !important; }
        }
    </style>

    <!-- JS for adding [ - ] & [ + ] buttons to Quantity -->
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var $qtyDiv = $('form.cart .quantity');
            
            if ($qtyDiv.length && !$('.custom-qty-btn').length) {
                var $qtyInput = $qtyDiv.find('input.qty');
                
                $qtyInput.before('<button type="button" class="custom-qty-btn custom-minus">-</button>');
                $qtyInput.after('<button type="button" class="custom-qty-btn custom-plus">+</button>');
                
                $('.custom-qty-btn').on('click', function() {
                    var $input = $(this).siblings('input.qty');
                    var val   = parseFloat($input.val()) || 1;
                    var step  = parseFloat($input.attr('step')) || 1;
                    var min   = parseFloat($input.attr('min')) || 1;
                    var max   = parseFloat($input.attr('max'));
                    
                    if ($(this).hasClass('custom-plus')) {
                        if (!max || val < max) {
                            $input.val(val + step);
                        }
                    } else {
                        if (val > min) {
                            $input.val(val - step);
                        }
                    }
                    $input.trigger('change');
                });
            }
        });
    </script>
    <?php
}
// Adding custom unified buttons to WooCommerce product page --end
