/**
 * ---------------------------------------------------------------------------------
 * Custom Checkout Options: Full Payment vs. Delivery Fee Advance
 * ---------------------------------------------------------------------------------
 */

// 1. Add custom payment preference radio buttons on checkout
add_action( 'woocommerce_review_order_before_payment', 'mc_custom_payment_options_field' );
function mc_custom_payment_options_field() {
    $selected = WC()->session->get( 'custom_payment_preference', 'full' );
    
    echo '<div id="custom_payment_preference_field" style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border-radius: 8px; border: 1px solid #ddd;">';
    echo '<h3 style="font-size: 16px; margin-bottom: 10px;">Payment Preference</h3>';
    
    woocommerce_form_field( 'custom_payment_preference', array(
        'type'     => 'radio',
        'class'    => array('form-row-wide'),
        'options'  => array(
            'full'     => 'Pay Full Amount',
            'delivery' => 'Pay Delivery Fee Only (Rest on COD)',
        ),
        'default'  => $selected,
        'required' => true, // Hides the "(optional)" text
    ), $selected );
    
    echo '</div>';
}

// 2. Inject CSS to style the radio buttons like modern toggle buttons
add_action( 'wp_head', 'mc_custom_payment_options_css' );
function mc_custom_payment_options_css() {
    if ( is_checkout() && ! is_wc_endpoint_url() ) {
        echo '<style>
            #custom_payment_preference_field .woocommerce-input-wrapper { display: flex; gap: 15px; margin-top: 10px; flex-wrap: wrap; }
            #custom_payment_preference_field input[type="radio"] { position: absolute; opacity: 0; width: 0; height: 0; }
            #custom_payment_preference_field label.radio { flex: 1; text-align: center; padding: 15px 20px; background: #ffffff; border: 2px solid #e5e5e5; border-radius: 6px; cursor: pointer; font-weight: 600; color: #555; transition: all 0.2s ease-in-out; margin: 0; display: flex; align-items: center; justify-content: center; }
            #custom_payment_preference_field label.radio:hover { border-color: #111; background: #f9f9f9; }
            #custom_payment_preference_field input[type="radio"]:checked + label.radio { background: #111; border-color: #111; color: #ffffff; }
        </style>';
    }
}

// 3. Trigger checkout cart update via AJAX when the selection changes
add_action( 'wp_footer', 'mc_checkout_radio_ajax_script' );
function mc_checkout_radio_ajax_script() {
    if ( is_checkout() && ! is_wc_endpoint_url() ) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $(document).on('change', 'input[name="custom_payment_preference"]', function() {
                    var preference = $(this).val();
                    $.ajax({
                        type: 'POST',
                        url: wc_checkout_params.ajax_url,
                        data: {
                            action: 'update_custom_payment_preference',
                            custom_payment_preference: preference
                        },
                        success: function() {
                            $('body').trigger('update_checkout');
                        }
                    });
                });
            });
        </script>
        <?php
    }
}

// 4. Handle the AJAX request and save the selection to the WooCommerce session
add_action( 'wp_ajax_update_custom_payment_preference', 'mc_update_custom_payment_preference_session' );
add_action( 'wp_ajax_nopriv_update_custom_payment_preference', 'mc_update_custom_payment_preference_session' );
function mc_update_custom_payment_preference_session() {
    if ( isset($_POST['custom_payment_preference']) ) {
        WC()->session->set( 'custom_payment_preference', sanitize_text_field( $_POST['custom_payment_preference'] ) );
    }
    wp_die();
}

// 5. Apply a negative fee to deduct the product cost, leaving only the shipping fee
add_action( 'woocommerce_cart_calculate_fees', 'mc_apply_delivery_only_negative_fee' );
function mc_apply_delivery_only_negative_fee( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;

    $preference = WC()->session->get( 'custom_payment_preference', 'full' );

    if ( 'delivery' === $preference ) {
        $subtotal = $cart->get_subtotal();
        // Updated the label text here
        $cart->add_fee( 'Due Amount - Pay on Delivery', -$subtotal, false );
    }
}

// 6. NEW: Remove the minus sign from the fee display on the checkout page
add_filter( 'woocommerce_cart_totals_fee_html', 'mc_remove_minus_from_due_fee', 10, 2 );
function mc_remove_minus_from_due_fee( $html, $fee ) {
    // Check if it's our specific fee
    if ( 'Due Amount - Pay on Delivery' === $fee->name ) {
        // Output the absolute (positive) amount of the fee to hide the "-" sign
        return wc_price( abs( $fee->amount ) ); 
    }
    return $html;
}

// 7. Save the due amount as order metadata when the order is placed
add_action( 'woocommerce_checkout_update_order_meta', 'mc_save_due_amount_to_order_meta' );
function mc_save_due_amount_to_order_meta( $order_id ) {
    $preference = WC()->session->get( 'custom_payment_preference', 'full' );
    
    if ( 'delivery' === $preference ) {
        $order = wc_get_order( $order_id );
        $order->update_meta_data( '_cod_due_amount', $order->get_subtotal() );
        $order->save();
        WC()->session->__unset( 'custom_payment_preference' );
    }
}

// 8. Display the pending due amount on the "Thank You" order received page
add_action( 'woocommerce_thankyou', 'mc_display_due_amount_on_thankyou_page', 10, 1 );
function mc_display_due_amount_on_thankyou_page( $order_id ) {
    $order = wc_get_order( $order_id );
    $due = $order->get_meta( '_cod_due_amount' );

    if ( $due ) {
        echo '<section class="woocommerce-order-details" style="margin-top: 2em; background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; border-radius: 4px;">';
        echo '<h2 class="woocommerce-order-details__title" style="margin-top: 0;">Pending Cash on Delivery</h2>';
        echo '<table class="woocommerce-table woocommerce-table--order-details shop_table order_details" style="margin-bottom: 0; border: none;">';
        // Updated the label text here
        echo '<tbody><tr><th scope="row" style="text-align: left; border: none;">Due Amount - Pay on Delivery:</th><td style="text-align: right; border: none;"><strong style="font-size: 1.2em; color: #d9534f;">' . wc_price( $due ) . '</strong></td></tr></tbody>';
        echo '</table>';
        echo '</section>';
    }
}
