<?php
/**
 * Plugin Name: WooCommerce Pre-Order with Partial Payment & Delivery Note
 * Plugin URI:  https://github.com/
 * Description: Lightweight WooCommerce extension to allow pre-orders with partial deposit advance (e.g. 50%) and custom estimated delivery notes for simple and variable products.
 * Version:     1.0.0
 * Author:      M Shahrear Shohag
 * Author url:  https://msshohag.com
 * Text Domain: wc-preorder-partial-payment
 * License:     GPL-2.0+
 */

defined('ABSPATH') || exit;

/* ==========================================================================
   1. ADMIN SETTINGS: Simple Products (Product Data > General Tab)
   ========================================================================== */
add_action('woocommerce_product_options_general_product_data', 'wc_preorder_admin_fields');
function wc_preorder_admin_fields() {
    echo '<div class="options_group show_if_simple">';
    
    woocommerce_wp_checkbox(array(
        'id'          => '_is_preorder',
        'label'       => __('Enable Pre-Order', 'wc-preorder-partial-payment'),
        'description' => __('Check to mark this product as a pre-order with partial deposit.', 'wc-preorder-partial-payment')
    ));

    woocommerce_wp_text_input(array(
        'id'                => '_deposit_percent',
        'label'             => __('Deposit Advance (%)', 'wc-preorder-partial-payment'),
        'placeholder'       => '50',
        'type'              => 'number',
        'custom_attributes' => array('step' => 'any', 'min' => '1', 'max' => '99'),
        'description'       => __('Percentage required as advance payment (default: 50%).', 'wc-preorder-partial-payment')
    ));

    woocommerce_wp_text_input(array(
        'id'          => '_preorder_delivery_note',
        'label'       => __('Delivery Note', 'wc-preorder-partial-payment'),
        'placeholder' => '15 working days delivery',
        'description' => __('Custom note shown on product page, checkout, and order details.', 'wc-preorder-partial-payment')
    ));

    echo '</div>';
}

/* ==========================================================================
   2. ADMIN SETTINGS: Variable Products (Per-Variation Fields)
   ========================================================================== */
add_action('woocommerce_product_after_variable_attributes', 'wc_variable_preorder_admin_fields', 10, 3);
function wc_variable_preorder_admin_fields($loop, $variation_data, $variation) {
    echo '<div class="options_group pricing show_if_variable" style="padding: 10px; background: #f9f9f9; border-top: 1px solid #eee; margin-top: 10px;">';
    
    woocommerce_wp_checkbox(array(
        'id'          => '_is_preorder_' . $variation->ID,
        'label'       => __('Enable Pre-Order for this variation', 'wc-preorder-partial-payment'),
        'value'       => get_post_meta($variation->ID, '_is_preorder', true),
        'description' => __('Allow partial advance deposit on this variation.', 'wc-preorder-partial-payment')
    ));

    woocommerce_wp_text_input(array(
        'id'                => '_deposit_percent_' . $variation->ID,
        'label'             => __('Deposit Advance (%)', 'wc-preorder-partial-payment'),
        'value'             => get_post_meta($variation->ID, '_deposit_percent', true),
        'placeholder'       => '50',
        'type'              => 'number',
        'custom_attributes' => array('step' => 'any', 'min' => '1', 'max' => '99')
    ));

    woocommerce_wp_text_input(array(
        'id'          => '_preorder_delivery_note_' . $variation->ID,
        'label'       => __('Delivery Note', 'wc-preorder-partial-payment'),
        'value'       => get_post_meta($variation->ID, '_preorder_delivery_note', true),
        'placeholder' => '15 working days delivery'
    ));

    echo '</div>';
}

/* ==========================================================================
   3. SAVE ADMIN FIELDS (Simple & Variable Products)
   ========================================================================== */
add_action('woocommerce_process_product_meta', 'wc_save_preorder_admin_fields');
function wc_save_preorder_admin_fields($post_id) {
    $is_preorder = isset($_POST['_is_preorder']) ? 'yes' : 'no';
    update_post_meta($post_id, '_is_preorder', $is_preorder);

    if (isset($_POST['_deposit_percent'])) {
        update_post_meta($post_id, '_deposit_percent', sanitize_text_field($_POST['_deposit_percent']));
    }
    if (isset($_POST['_preorder_delivery_note'])) {
        update_post_meta($post_id, '_preorder_delivery_note', sanitize_text_field($_POST['_preorder_delivery_note']));
    }
}

add_action('woocommerce_save_product_variation', 'wc_save_variable_preorder_fields', 10, 2);
function wc_save_variable_preorder_fields($variation_id, $i) {
    $is_preorder = isset($_POST['_is_preorder_' . $variation_id]) ? 'yes' : 'no';
    update_post_meta($variation_id, '_is_preorder', $is_preorder);

    if (isset($_POST['_deposit_percent_' . $variation_id])) {
        update_post_meta($variation_id, '_deposit_percent', sanitize_text_field($_POST['_deposit_percent_' . $variation_id]));
    }
    if (isset($_POST['_preorder_delivery_note_' . $variation_id])) {
        update_post_meta($variation_id, '_preorder_delivery_note', sanitize_text_field($_POST['_preorder_delivery_note_' . $variation_id]));
    }
}

/* ==========================================================================
   4. EXPOSE VARIATION DATA TO JAVASCRIPT
   ========================================================================== */
add_filter('woocommerce_available_variation', 'wc_expose_preorder_variation_data', 10, 3);
function wc_expose_preorder_variation_data($data, $product, $variation) {
    $var_id    = $variation->get_id();
    $parent_id = $variation->get_parent_id();

    $is_preorder = get_post_meta($var_id, '_is_preorder', true);
    if (empty($is_preorder) || $is_preorder === 'no') {
        $is_preorder = get_post_meta($parent_id, '_is_preorder', true);
    }

    $deposit_percent = get_post_meta($var_id, '_deposit_percent', true);
    if (empty($deposit_percent)) {
        $deposit_percent = get_post_meta($parent_id, '_deposit_percent', true) ?: '50';
    }

    $delivery_note = get_post_meta($var_id, '_preorder_delivery_note', true);
    if (empty($delivery_note)) {
        $delivery_note = get_post_meta($parent_id, '_preorder_delivery_note', true) ?: '15 working days delivery';
    }

    $data['is_preorder']     = ($is_preorder === 'yes');
    $data['deposit_percent'] = floatval($deposit_percent);
    $data['delivery_note']   = esc_html($delivery_note);

    return $data;
}

/* ==========================================================================
   5. SINGLE PRODUCT BUTTON TEXT (Simple Products)
   ========================================================================== */
add_filter('woocommerce_product_single_add_to_cart_text', 'wc_preorder_button_text', 20, 2);
function wc_preorder_button_text($text, $product) {
    if (!$product->is_type('variable') && get_post_meta($product->get_id(), '_is_preorder', true) === 'yes') {
        $percent = get_post_meta($product->get_id(), '_deposit_percent', true) ?: '50';
        return sprintf(__('Pre-Order Now (%s%% Advance)', 'wc-preorder-partial-payment'), $percent);
    }
    return $text;
}

/* ==========================================================================
   6. FRONTEND SCRIPTS & STYLES (Dynamic Swatch & Delivery Note Update)
   ========================================================================== */
add_action('wp_footer', 'wc_preorder_single_product_js');
function wc_preorder_single_product_js() {
    if (!is_product()) return;

    global $post;
    $product_id = $post ? $post->ID : 0;
    if (!$product_id) return;

    $product = wc_get_product($product_id);
    if (!$product) return;

    $is_parent_preorder = (get_post_meta($product_id, '_is_preorder', true) === 'yes');
    ?>
    <style>
        .wc-preorder-note {
            display: inline-block;
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
            padding: 8px 14px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            margin: 12px 0;
        }
    </style>

    <script type="text/javascript">
    (function($) {
        $(document).ready(function() {
            var $form = $('form.variations_form');
            var defaultBtnText = $('.single_add_to_cart_button').text();
            var isParentPreorder = <?php echo $is_parent_preorder ? 'true' : 'false'; ?>;

            function setDeliveryNote(text) {
                $('.wc-preorder-note').remove();
                if (text) {
                    var $notice = $('<div class="wc-preorder-note"><strong>Estimated Delivery:</strong> ' + text + '</div>');
                    if ($('.single_variation_wrap').length) {
                        $('.single_variation_wrap').before($notice);
                    } else {
                        $('.single_add_to_cart_button').parent().before($notice);
                    }
                }
            }

            // Simple product check on load
            if (isParentPreorder && !$form.length) {
                var noteText = <?php 
                    $note = get_post_meta($product_id, '_preorder_delivery_note', true) ?: '15 working days delivery';
                    echo json_encode($note); 
                ?>;
                setDeliveryNote(noteText);
            }

            // Variable Product: when a variation is selected
            $form.on('found_variation', function(event, variation) {
                if (variation && variation.is_preorder) {
                    var percent = variation.deposit_percent || 50;
                    $('.single_add_to_cart_button').text('Pre-Order Now (' + percent + '% Advance)');
                    setDeliveryNote(variation.delivery_note || '15 working days delivery');
                } else {
                    $('.single_add_to_cart_button').text(defaultBtnText);
                    setDeliveryNote('');
                }
            });

            // Variable Product: when options are cleared or invalid
            $form.on('reset_data', function() {
                $('.single_add_to_cart_button').text(defaultBtnText);
                setDeliveryNote('');
            });

            // AJAX add to cart intercept -> direct redirect to checkout
            $(document.body).on('added_to_cart', function(event, fragments, cart_hash, $button) {
                if ($button && $button.text().indexOf('Pre-Order') !== -1) {
                    window.location.href = '<?php echo esc_url(wc_get_checkout_url()); ?>';
                }
            });
        });
    })(jQuery);
    </script>
    <?php
}

/* ==========================================================================
   7. SESSION STORAGE & CART CLEARING
   ========================================================================== */
add_filter('woocommerce_add_cart_item_data', 'wc_preorder_add_cart_item_data', 10, 3);
function wc_preorder_add_cart_item_data($cart_item_data, $product_id, $variation_id) {
    $target_id = $variation_id ? $variation_id : $product_id;
    $parent_id = $variation_id ? wp_get_post_parent_id($variation_id) : 0;

    $is_preorder = get_post_meta($target_id, '_is_preorder', true);
    if (empty($is_preorder) || $is_preorder === 'no') {
        if ($parent_id) {
            $is_preorder = get_post_meta($parent_id, '_is_preorder', true);
        }
    }

    if ($is_preorder === 'yes') {
        // Clear previous items from cart so only the pre-order product is processed
        WC()->cart->empty_cart();

        $deposit_percent = get_post_meta($target_id, '_deposit_percent', true);
        if (empty($deposit_percent) && $parent_id) {
            $deposit_percent = get_post_meta($parent_id, '_deposit_percent', true);
        }
        $deposit_percent = !empty($deposit_percent) ? floatval($deposit_percent) : 50;

        $delivery_note = get_post_meta($target_id, '_preorder_delivery_note', true);
        if (empty($delivery_note) && $parent_id) {
            $delivery_note = get_post_meta($parent_id, '_preorder_delivery_note', true);
        }
        if (empty($delivery_note)) {
            $delivery_note = '15 working days delivery';
        }

        // Store persistently in cart item session
        $cart_item_data['wc_is_preorder']     = true;
        $cart_item_data['wc_deposit_percent'] = $deposit_percent;
        $cart_item_data['wc_delivery_note']   = $delivery_note;
    }

    return $cart_item_data;
}

/* ==========================================================================
   8. RESTORE SESSION DATA
   ========================================================================== */
add_filter('woocommerce_get_cart_item_from_session', 'wc_preorder_get_cart_item_from_session', 10, 2);
function wc_preorder_get_cart_item_from_session($cart_item, $values) {
    if (!empty($values['wc_is_preorder'])) {
        $cart_item['wc_is_preorder']     = true;
        $cart_item['wc_deposit_percent'] = $values['wc_deposit_percent'];
        $cart_item['wc_delivery_note']   = $values['wc_delivery_note'];
    }
    return $cart_item;
}

/* ==========================================================================
   9. CALCULATE ADVANCE DEPOSIT PRICE (No compounding price bug)
   ========================================================================== */
add_action('woocommerce_before_calculate_totals', 'wc_apply_preorder_deposit_price', 10, 1);
function wc_apply_preorder_deposit_price($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;

    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        if (!empty($cart_item['wc_is_preorder'])) {
            $product = $cart_item['data'];
            
            // Base original price
            $full_price = floatval($product->get_regular_price());
            if ($product->is_on_sale()) {
                $full_price = floatval($product->get_sale_price());
            }
            if ($full_price <= 0) {
                $full_price = floatval($product->get_price());
            }

            $deposit_percent = floatval($cart_item['wc_deposit_percent']);
            $deposit_amount  = ($full_price * $deposit_percent) / 100;
            $due_amount      = $full_price - $deposit_amount;

            // Set the cart price to the advance deposit amount
            $cart_item['data']->set_price($deposit_amount);

            // Cache calculated amounts for checkout & order saving
            $cart->cart_contents[$cart_item_key]['wc_full_price']     = $full_price;
            $cart->cart_contents[$cart_item_key]['wc_deposit_amount'] = $deposit_amount;
            $cart->cart_contents[$cart_item_key]['wc_due_amount']     = $due_amount;
        }
    }
}

/* ==========================================================================
   10. INSTANT REDIRECT TO CHECKOUT
   ========================================================================== */
add_filter('woocommerce_add_to_cart_redirect', 'wc_preorder_redirect_to_checkout', 99, 1);
function wc_preorder_redirect_to_checkout($url) {
    if (!WC()->cart->is_empty()) {
        foreach (WC()->cart->get_cart() as $item) {
            if (!empty($item['wc_is_preorder'])) {
                return wc_get_checkout_url();
            }
        }
    }
    return $url;
}

/* ==========================================================================
   11. DISPLAY PRICING BREAKDOWN (Cart & Checkout Standard Meta)
   ========================================================================== */
add_filter('woocommerce_get_item_data', 'wc_display_preorder_cart_item_meta', 10, 2);
function wc_display_preorder_cart_item_meta($item_data, $cart_item) {
    if (!empty($cart_item['wc_is_preorder'])) {
        $full_price  = isset($cart_item['wc_full_price']) ? $cart_item['wc_full_price'] : 0;
        $due_amount  = isset($cart_item['wc_due_amount']) ? $cart_item['wc_due_amount'] : 0;
        $deposit_pc  = isset($cart_item['wc_deposit_percent']) ? $cart_item['wc_deposit_percent'] : 50;
        $advance_amt = isset($cart_item['wc_deposit_amount']) ? $cart_item['wc_deposit_amount'] : $cart_item['data']->get_price();

        $item_data[] = array(
            'key'     => __('Estimated Delivery', 'wc-preorder-partial-payment'),
            'value'   => esc_html($cart_item['wc_delivery_note']),
            'display' => esc_html($cart_item['wc_delivery_note']),
        );
        $item_data[] = array(
            'key'     => __('Full Product Price', 'wc-preorder-partial-payment'),
            'value'   => wc_price($full_price),
            'display' => wc_price($full_price),
        );
        $item_data[] = array(
            'key'     => sprintf(__('Advance Paid (%s%%)', 'wc-preorder-partial-payment'), $deposit_pc),
            'value'   => wc_price($advance_amt),
            'display' => wc_price($advance_amt),
        );
        $item_data[] = array(
            'key'     => __('Remaining Amount Due', 'wc-preorder-partial-payment'),
            'value'   => wc_price($due_amount),
            'display' => wc_price($due_amount),
        );
    }
    return $item_data;
}

/* ==========================================================================
   12. CHECKOUT REVIEW TABLE BREAKDOWN (Compatibility Fallback)
   ========================================================================== */
add_filter('woocommerce_checkout_cart_item_quantity', 'wc_checkout_cart_item_breakdown_html', 20, 3);
function wc_checkout_cart_item_breakdown_html($quantity_html, $cart_item, $cart_item_key) {
    if (!empty($cart_item['wc_is_preorder'])) {
        $full_price  = isset($cart_item['wc_full_price']) ? $cart_item['wc_full_price'] : 0;
        $due_amount  = isset($cart_item['wc_due_amount']) ? $cart_item['wc_due_amount'] : 0;
        $deposit_pc  = isset($cart_item['wc_deposit_percent']) ? $cart_item['wc_deposit_percent'] : 50;
        $advance_amt = isset($cart_item['wc_deposit_amount']) ? $cart_item['wc_deposit_amount'] : $cart_item['data']->get_price();

        $html  = $quantity_html;
        $html .= '<div class="wc-checkout-preorder-summary" style="font-size: 13px; line-height: 1.6; margin-top: 8px; padding: 6px 10px; background: #fafafa; border-left: 3px solid #0071e3; border-radius: 2px;">';
        $html .= '<div><span style="color:#666;">Delivery Note:</span> <strong>' . esc_html($cart_item['wc_delivery_note']) . '</strong></div>';
        $html .= '<div><span style="color:#666;">Full Price:</span> <strong>' . wc_price($full_price) . '</strong></div>';
        $html .= '<div><span style="color:#666;">Advance (' . intval($deposit_pc) . '%):</span> <strong style="color:#28a745;">' . wc_price($advance_amt) . '</strong></div>';
        $html .= '<div><span style="color:#666;">Balance Due:</span> <strong style="color:#d9534f;">' . wc_price($due_amount) . '</strong></div>';
        $html .= '</div>';
        return $html;
    }
    return $quantity_html;
}

/* ==========================================================================
   13. SAVE BREAKDOWN META TO FINAL ORDER DETAILS & EMAILS
   ========================================================================== */
add_action('woocommerce_checkout_create_order_line_item', 'wc_save_preorder_line_item_meta', 10, 4);
function wc_save_preorder_line_item_meta($item, $cart_item_key, $values, $order) {
    if (!empty($values['wc_is_preorder'])) {
        $full_price  = isset($values['wc_full_price']) ? $values['wc_full_price'] : 0;
        $due_amount  = isset($values['wc_due_amount']) ? $values['wc_due_amount'] : 0;
        $deposit_pc  = isset($values['wc_deposit_percent']) ? $values['wc_deposit_percent'] : 50;
        $advance_amt = isset($values['wc_deposit_amount']) ? $values['wc_deposit_amount'] : $item->get_total();

        $item->add_meta_data(__('Pre-Order Type', 'wc-preorder-partial-payment'), sprintf('%s%% Deposit Order', $deposit_pc), true);
        $item->add_meta_data(__('Delivery Note', 'wc-preorder-partial-payment'), $values['wc_delivery_note'], true);
        $item->add_meta_data(__('Full Product Price', 'wc-preorder-partial-payment'), wc_price($full_price), true);
        $item->add_meta_data(sprintf(__('Advance Deposit Paid (%s%%)', 'wc-preorder-partial-payment'), $deposit_pc), wc_price($advance_amt), true);
        $item->add_meta_data(__('Remaining Amount Due', 'wc-preorder-partial-payment'), wc_price($due_amount), true);
    }
}
