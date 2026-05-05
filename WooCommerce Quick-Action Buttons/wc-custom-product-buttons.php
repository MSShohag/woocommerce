// Adding a custom WhatsApp button to your WooCommerce product page
/**
 * 1. ADD CUSTOM BUTTONS (BUY NOW, MESSENGER, WHATSAPP)
 */
add_action( 'woocommerce_after_add_to_cart_button', 'mss_unified_product_buttons', 15 );
function mss_unified_product_buttons() {
    global $product;

    // --- Configuration ---
    $phone_number = '8801402124767';  // Your WhatsApp number
    $fb_page_id   = '61556531841480';   // Your Messenger username
    
    $product_id   = $product->get_id();
    $product_name = $product->get_name();
    $product_url  = get_permalink($product_id);
    
    // Initial Links
    $buy_now_url   = esc_url( add_query_arg( 'quick_buy', $product_id, wc_get_checkout_url() ) );
    $whatsapp_url  = "https://wa.me/" . $phone_number . "?text=" . rawurlencode("Hi, I'm interested in " . $product_name . " (" . $product_url . ")");
    $messenger_url = "https://m.me/" . $fb_page_id;

    // Buy Now Button (Top Row)
    echo '<a href="' . $buy_now_url . '" class="button buy-now-btn" data-parent-id="' . $product_id . '" style="margin-left: 10px !important;">Buy Now</a>';
    
    // Messenger & WhatsApp Row (Bottom Row)
    echo '<div class="mss-social-button-row">';
        echo '<a href="' . $messenger_url . '" class="button messenger-btn" target="_blank"><i class="fab fa-facebook-messenger"></i> Messenger</a>';
        echo '<a href="' . $whatsapp_url . '" class="button whatsapp-btn" target="_blank"><i class="fa fa-whatsapp" aria-hidden="true"></i> WhatsApp</a>';
    echo '</div>';
    
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
add_action( 'template_redirect', 'mss_handle_quick_buy_logic' );
function mss_handle_quick_buy_logic() {
    if ( isset( $_GET['quick_buy'] ) && !empty( $_GET['quick_buy'] ) ) {
        $id_to_add = absint( $_GET['quick_buy'] );
        WC()->cart->empty_cart();
        WC()->cart->add_to_cart( $id_to_add );
        wp_safe_redirect( wc_get_checkout_url() );
        exit;
    }
}

/**
 * 3. FINAL REFINED STYLING
 * Fixes quantity visibility and adds requested spacing.
 */
add_action( 'wp_head', 'mss_custom_product_page_styles' );
function mss_custom_product_page_styles() {
    ?>
    <style>
        /* Container Layout */
        form.cart {
            display: flex !important;
            flex-wrap: wrap !important;
            align-items: center;
            gap: 12px; /* Adds gap between all elements in the first row */
            max-width: 600px;
        }

        /* Standardize height, pill shape, and font-size */
        form.cart .single_add_to_cart_button,
        form.cart .buy-now-btn,
        .mss-social-button-row .button {
            height: 42px !important; /* Slightly increased for better text visibility */
            border-radius: 50px !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            margin: 0 !important;
            border: none !important;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px !important;
            letter-spacing: 0.3px;
            padding: 0 25px !important;
            text-decoration: none !important;
            white-space: nowrap;
        }

        /* Quantity Field Visibility Fix */
        form.cart .quantity {
            display: inline-flex !important;
            align-items: center;
            border-radius: 50px !important;
            background: #fff;
            border: 1px solid #e0e0e0 !important;
            height: 42px !important;
            width: 20% !important;
            padding: 0 5px !important;
            overflow: visible !important;
        }

        form.cart .quantity input.qty {
            width: 40px !important;
            height: 100% !important;
            border: none !important;
            background: transparent !important;
            font-size: 15px !important;
            color: #333 !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        /* Social Row Spacing (Gap on top) */
        .mss-social-button-row {
            display: flex;
            width: 100%; 
            gap: 12px; /* Spacing between Messenger and WhatsApp */
            margin-top: 10px !important; /* Extra gap above the second row */
        }
        .mss-social-button-row .button { flex: 1; }

        /* Colors */
        .single_add_to_cart_button { background-color: #FF5C00 !important; color: #fff !important; }
        .buy-now-btn { background-color: #FFA500 !important; color: #fff !important; flex-grow: 1; }
        .messenger-btn { background-color: #0084FF !important; color: #fff !important; }
        .whatsapp-btn { background-color: #25D366 !important; color: #fff !important; }

        @media (max-width: 480px) {
            form.cart { max-width: 100%; gap: 5px; }
            form.cart .quantity { width: 50% !important; }
        }
    </style>
    <?php
}
