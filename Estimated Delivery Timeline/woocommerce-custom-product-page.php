<?php
/**
 * WooCommerce Custom Product Page Snippets
 * Features: Unified Add to Cart/Buy Now layout and Estimated Delivery Timeline.
 */

// --- START UNIFIED BUTTONS WIDGET ---
add_action( 'woocommerce_after_add_to_cart_button', 'mss_unified_product_buttons', 15 );
function mss_unified_product_buttons() {
    global $product;
    $product_id   = $product->get_id();
    $buy_now_url  = esc_url( add_query_arg( 'quick_buy', $product_id, wc_get_checkout_url() ) );

    echo '<a href="' . $buy_now_url . '" class="button buy-now-btn" data-parent-id="' . $product_id . '">Buy Now - Grab Your Offer</a>';
    
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

add_action( 'wp_head', 'mss_custom_product_page_styles' );
function mss_custom_product_page_styles() {
    ?>
    <style>
        form.cart, .woocommerce-variation-add-to-cart { display: flex !important; flex-wrap: wrap !important; align-items: stretch !important; gap: 10px !important; width: 100% !important; max-width: 100% !important; }
        form.cart .quantity, .woocommerce-variation-add-to-cart .quantity { order: 1 !important; flex: 0 0 110px !important; display: flex !important; flex-wrap: nowrap !important; align-items: stretch !important; justify-content: center !important; border-radius: 4px !important; background: #ffffff !important; border: 1px solid #d1d1d1 !important; height: 48px !important; padding: 0 !important; margin: 0 !important; overflow: hidden !important; box-sizing: border-box !important; }
        form.cart .quantity input.qty { flex: 1 1 40px !important; width: 40px !important; min-width: 30px !important; max-width: 40px !important; height: 100% !important; border: none !important; background: transparent !important; font-size: 17px !important; font-weight: normal !important; color: #333 !important; text-align: center !important; padding: 0 !important; margin: 0 !important; -moz-appearance: textfield !important; box-shadow: none !important; }
        form.cart .quantity input.qty::-webkit-outer-spin-button, form.cart .quantity input.qty::-webkit-inner-spin-button { -webkit-appearance: none !important; margin: 0 !important; }
        .mss-qty-btn { background: #ffffff !important; border: none !important; color: #555 !important; flex: 0 0 34px !important; width: 34px !important; max-width: 34px !important; padding: 0 !important; margin: 0 !important; font-size: 18px !important; font-weight: normal !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important; transition: background 0.2s !important; box-sizing: border-box !important; }
        .mss-qty-btn:hover { background: #f7f7f7 !important; }
        .mss-qty-btn.mss-minus { border-right: 1px solid #d1d1d1 !important; }
        .mss-qty-btn.mss-plus { border-left: 1px solid #d1d1d1 !important; }
        form.cart .single_add_to_cart_button { order: 2 !important; flex: 0 0 calc(100% - 120px) !important; background-color: #FF5C00 !important; color: #fff !important; }
        form.cart .buy-now-btn, .woocommerce-variation-add-to-cart .buy-now-btn { order: 3 !important; flex: 1 1 100% !important; animation: mss-pulse 2s infinite !important; background-color: #FFA500 !important; color: #fff !important; }
        @keyframes mss-pulse { 0% { box-shadow: 0 0 0 0 rgba(255, 165, 0, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(255, 165, 0, 0); } 100% { box-shadow: 0 0 0 0 rgba(255, 165, 0, 0); } }
        form.cart .single_add_to_cart_button, form.cart .buy-now-btn { height: 48px !important; border-radius: 5px !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; margin: 0 !important; border: none !important; font-weight: 600 !important; text-transform: uppercase !important; font-size: 13px !important; padding: 0 10px !important; text-decoration: none !important; text-align: center !important; line-height: 1.2 !important; box-sizing: border-box !important; }
        @media (max-width: 768px) { form.cart .buy-now-btn, form.cart .single_add_to_cart_button { font-size: 12px !important; } }
    </style>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var $qtyDiv = $('form.cart .quantity');
            if ($qtyDiv.length && !$('.mss-qty-btn').length) {
                var $qtyInput = $qtyDiv.find('input.qty');
                $qtyInput.before('<button type="button" class="mss-qty-btn mss-minus">-</button>');
                $qtyInput.after('<button type="button" class="mss-qty-btn mss-plus">+</button>');
                $('.mss-qty-btn').on('click', function() {
                    var $input = $(this).siblings('input.qty');
                    var val   = parseFloat($input.val()) || 1;
                    var step  = parseFloat($input.attr('step')) || 1;
                    var min   = parseFloat($input.attr('min')) || 1;
                    var max   = parseFloat($input.attr('max'));
                    if ($(this).hasClass('mss-plus')) { if (!max || val < max) { $input.val(val + step); } } 
                    else { if (val > min) { $input.val(val - step); } }
                    $input.trigger('change');
                });
            }
        });
    </script>
    <?php
}
// --- END UNIFIED BUTTONS WIDGET ---

// --- START ESTIMATED DELIVERY WIDGET ---
add_shortcode('estimated_delivery_box', 'custom_estimated_delivery_shortcode');
function custom_estimated_delivery_shortcode() {
    $today          = date_i18n('j M'); 
    $dispatch       = date_i18n('j M', strtotime('+1 day'));
    $inside_dhaka   = date_i18n('j M', strtotime('+2 days'));
    $outside_dhaka  = date_i18n('j M', strtotime('+3 days'));

    ob_start(); ?>
    <style>
        .edb-container { background-color: #fdfaf7 !important; border: 1px solid #f0eaea !important; border-radius: 12px !important; padding: 25px 20px !important; text-align: center !important; margin: 20px 0 !important; box-shadow: none !important; }
        .edb-container h4.edb-title { color: #777777 !important; font-size: 12px !important; font-weight: 700 !important; letter-spacing: 1px !important; margin: 0 0 8px 0 !important; line-height: 1.2 !important; text-transform: uppercase !important; }
        .edb-main-dates { font-size: 20px !important; font-weight: 700 !important; color: #222222 !important; margin-bottom: 20px !important; line-height: 1.2 !important; }
        .edb-badges { display: flex !important; justify-content: center !important; gap: 10px !important; margin-bottom: 30px !important; flex-wrap: wrap !important; }
        .edb-badge { background-color: #333333 !important; color: #ffffff !important; font-size: 12px !important; font-weight: 500 !important; padding: 6px 14px !important; border-radius: 20px !important; border: none !important; }
        .edb-timeline-wrapper { display: flex !important; justify-content: space-between !important; position: relative !important; max-width: 480px !important; margin: 0 auto !important; }
        .edb-timeline-line { position: absolute !important; top: 25px !important; left: 12.5% !important; right: 12.5% !important; height: 2px !important; background-color: #e5e5e5 !important; z-index: 1 !important; }
        .edb-step { position: relative !important; z-index: 2 !important; display: flex !important; flex-direction: column !important; align-items: center !important; width: 25% !important; }
        .edb-icon { width: 50px !important; height: 50px !important; border-radius: 50% !important; display: flex !important; align-items: center !important; justify-content: center !important; color: #ffffff !important; margin-bottom: 12px !important; }
        .edb-icon svg { width: 22px !important; height: 22px !important; }
        .edb-step:nth-child(2) .edb-icon { background-color: #333333 !important; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15) !important; }
        .edb-step:nth-child(3) .edb-icon, .edb-step:nth-child(4) .edb-icon, .edb-step:nth-child(5) .edb-icon { background-color: #c4b5a5 !important; }
        .edb-step-text { font-size: 11px !important; font-weight: 700 !important; color: #444444 !important; margin: 0 !important; line-height: 1.2 !important; text-align: center !important; }
        .edb-step-date { font-size: 12px !important; color: #888888 !important; margin-top: 4px !important; line-height: 1.2 !important; }
    </style>
    <div class="edb-container">
        <h4 class="edb-title">ESTIMATED DELIVERY</h4>
        <div class="edb-main-dates"><?php echo $inside_dhaka; ?> - <?php echo $outside_dhaka; ?></div>
        <div class="edb-badges">
            <span class="edb-badge">Inside Dhaka: 1-2 Days</span>
            <span class="edb-badge">Outside Dhaka: 2-3 Days</span>
        </div>
        <div class="edb-timeline-wrapper">
            <div class="edb-timeline-line"></div>
            <div class="edb-step">
                <div class="edb-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg></div>
                <div class="edb-step-text">ORDER PLACED</div>
                <div class="edb-step-date"><?php echo $today; ?></div>
            </div>
            <div class="edb-step">
                <div class="edb-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg></div>
                <div class="edb-step-text">DISPATCHED</div>
                <div class="edb-step-date"><?php echo $dispatch; ?></div>
            </div>
            <div class="edb-step">
                <div class="edb-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg></div>
                <div class="edb-step-text">INSIDE DHAKA</div>
                <div class="edb-step-date"><?php echo $inside_dhaka; ?></div>
            </div>
            <div class="edb-step">
                <div class="edb-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg></div>
                <div class="edb-step-text">OUTSIDE DHAKA</div>
                <div class="edb-step-date"><?php echo $outside_dhaka; ?></div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
// --- END ESTIMATED DELIVERY WIDGET ---
?>
