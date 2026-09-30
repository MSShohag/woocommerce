<?php
/**
 * Plugin Name: WC Abandoned Cart Recovery (Local Transients)
 * Description: Captures guest email and phone on checkout and schedules recovery emails via native WP-Cron without database bloat.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPLv2 or later
 * Text Domain: wc-abandoned-cart-local
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1. Inject minimal vanilla JS on checkout page to capture email & phone on blur
 */
add_action( 'wp_footer', 'wclocal_inject_checkout_capture_js' );
function wclocal_inject_checkout_capture_js() {
    if ( ! is_checkout() || is_order_received_page() ) {
        return;
    }
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const emailField = document.getElementById('billing_email');
        const phoneField = document.getElementById('billing_phone');

        let lastEmail = '';
        let lastPhone = '';

        function captureCartData() {
            const email = emailField ? emailField.value.trim() : '';
            const phone = phoneField ? phoneField.value.trim() : '';

            // Need at least a valid email structure to log
            if ( ! email || ! email.includes('@') || ! email.includes('.') ) {
                return;
            }

            // Prevent redundant requests if values haven't changed
            if ( email === lastEmail && phone === lastPhone ) {
                return;
            }

            lastEmail = email;
            lastPhone = phone;

            const payload = new URLSearchParams();
            payload.append( 'action', 'wclocal_save_cart' );
            payload.append( 'email', email );
            payload.append( 'phone', phone );

            fetch( '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: payload
            }).catch(function (err) {
                // Fail silently to avoid breaking checkout UX
            });
        }

        if ( emailField ) {
            emailField.addEventListener( 'blur', captureCartData );
        }
        if ( phoneField ) {
            phoneField.addEventListener( 'blur', captureCartData );
        }
    });
    </script>
    <?php
}

/**
 * 2. AJAX endpoint to store cart in a temporary WordPress transient
 */
add_action( 'wp_ajax_wclocal_save_cart', 'wclocal_handle_save_cart' );
add_action( 'wp_ajax_nopriv_wclocal_save_cart', 'wclocal_handle_save_cart' );

function wclocal_handle_save_cart() {
    if ( empty( $_POST['email'] ) || ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
        wp_die();
    }

    $email = sanitize_email( wp_unslash( $_POST['email'] ) );
    $phone = isset( $_POST['phone'] ) ? wc_clean( wp_unslash( $_POST['phone'] ) ) : '';

    if ( ! is_email( $email ) ) {
        wp_die();
    }

    $cart_contents = WC()->cart->get_cart_for_session();
    if ( empty( $cart_contents ) ) {
        wp_die();
    }

    $transient_key = 'wc_ac_' . md5( strtolower( $email ) );

    // Check if this cart is already tracked
    $existing = get_transient( $transient_key );

    $data = array(
        'email'        => $email,
        'phone'        => $phone,
        'cart'         => $cart_contents,
        'total'        => WC()->cart->get_total( 'edit' ),
        'created_at'   => $existing ? $existing['created_at'] : time(),
        'updated_at'   => time(),
        'email_sent'   => $existing ? ( ! empty( $existing['email_sent'] ) ) : false,
    );

    // Save transient for 3 days
    set_transient( $transient_key, $data, 3 * DAY_IN_SECONDS );

    // Maintain a lightweight index of active transient keys
    $index = get_option( 'wclocal_abandoned_index', array() );
    if ( ! in_array( $transient_key, $index, true ) ) {
        $index[] = $transient_key;
        update_option( 'wclocal_abandoned_index', $index, false );
    }

    wp_die();
}

/**
 * 3. Schedule hourly cron event on plugin activation
 */
register_activation_hook( __FILE__, 'wclocal_schedule_cron' );
function wclocal_schedule_cron() {
    if ( ! wp_next_scheduled( 'wclocal_hourly_recovery_cron' ) ) {
        wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', 'wclocal_hourly_recovery_cron' );
    }
}

register_deactivation_hook( __FILE__, 'wclocal_clear_cron' );
function wclocal_clear_cron() {
    $timestamp = wp_next_scheduled( 'wclocal_hourly_recovery_cron' );
    if ( $timestamp ) {
        wp_unschedule_event( $timestamp, 'wclocal_hourly_recovery_cron' );
    }
}

/**
 * 4. Cron job: Scan active transients and send recovery emails
 */
add_action( 'wclocal_hourly_recovery_cron', 'wclocal_process_abandoned_carts' );
function wclocal_process_abandoned_carts() {
    $index = get_option( 'wclocal_abandoned_index', array() );
    if ( empty( $index ) || ! is_array( $index ) ) {
        return;
    }

    $current_time = time();
    $updated_index = array();

    foreach ( $index as $transient_key ) {
        $cart_data = get_transient( $transient_key );

        // If expired or missing, omit from index
        if ( ! $cart_data ) {
            continue;
        }

        $elapsed = $current_time - $cart_data['updated_at'];

        // If order was already sent or abandoned for > 48 hours, cleanup
        if ( ! empty( $cart_data['email_sent'] ) || $elapsed > ( 48 * HOUR_IN_SECONDS ) ) {
            delete_transient( $transient_key );
            continue;
        }

        // Trigger email if abandoned between 1 hour and 24 hours ago
        if ( $elapsed >= HOUR_IN_SECONDS && $elapsed <= ( 24 * HOUR_IN_SECONDS ) ) {
            $email = $cart_data['email'];

            // Double check customer didn't complete an order in the meantime
            if ( ! wclocal_has_recent_order( $email, $cart_data['created_at'] ) ) {
                wclocal_send_recovery_email( $cart_data );
                $cart_data['email_sent'] = true;
                set_transient( $transient_key, $cart_data, 2 * DAY_IN_SECONDS );
            } else {
                delete_transient( $transient_key );
                continue;
            }
        }

        $updated_index[] = $transient_key;
    }

    update_option( 'wclocal_abandoned_index', $updated_index, false );
}

/**
 * Check if the email completed an order after cart capture
 */
function wclocal_has_recent_order( $email, $since_timestamp ) {
    $orders = wc_get_orders( array(
        'billing_email' => $email,
        'date_created'  => '>=' . ( $since_timestamp - 300 ),
        'status'        => array( 'wc-processing', 'wc-completed', 'wc-on-hold' ),
        'limit'         => 1,
    ) );
    return ! empty( $orders );
}

/**
 * Dispatch recovery email
 */
function wclocal_send_recovery_email( $cart_data ) {
    $to           = $cart_data['email'];
    $checkout_url = wc_get_checkout_url();
    $site_name    = get_bloginfo( 'name' );

    $subject = sprintf( 'Did you leave something behind at %s?', $site_name );

    $message  = "Hello,\n\n";
    $message .= "We noticed you left items in your shopping cart. You can complete your purchase anytime using the link below:\n\n";
    $message .= esc_url( $checkout_url ) . "\n\n";
    $message .= "If you have any questions or ran into issues during checkout, simply reply to this email.\n\n";
    $message .= "Best regards,\n" . $site_name;

    $headers = array( 'Content-Type: text/plain; charset=UTF-8' );

    wp_mail( $to, $subject, $message, $headers );
}

/**
 * 5. Cleanup transient immediately when an order completes
 */
add_action( 'woocommerce_thankyou', 'wclocal_clear_transient_on_purchase', 10, 1 );
function wclocal_clear_transient_on_purchase( $order_id ) {
    if ( ! $order_id ) {
        return;
    }
    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        return;
    }

    $email = strtolower( trim( $order->get_billing_email() ) );
    if ( $email ) {
        $transient_key = 'wc_ac_' . md5( $email );
        delete_transient( $transient_key );

        $index = get_option( 'wclocal_abandoned_index', array() );
        if ( is_array( $index ) && in_array( $transient_key, $index, true ) ) {
            $index = array_diff( $index, array( $transient_key ) );
            update_option( 'wclocal_abandoned_index', array_values( $index ), false );
        }
    }
}
// Add submenu under WooCommerce menu
add_action( 'admin_menu', 'wclocal_register_admin_page' );
function wclocal_register_admin_page() {
    add_submenu_page(
        'woocommerce',
        'Abandoned Carts',
        'Abandoned Carts',
        'manage_woocommerce',
        'wc-local-abandoned-carts',
        'wclocal_render_admin_page'
    );
}

function wclocal_render_admin_page() {
    $index = get_option( 'wclocal_abandoned_index', array() );
    ?>
    <div class="wrap">
        <h1 style="margin-bottom: 20px;">Captured Abandoned Carts (Local Transients)</h1>

        <?php if ( empty( $index ) ) : ?>
            <p>No active abandoned carts found.</p>
        <?php else : ?>
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Cart Total</th>
                        <th>Items Count</th>
                        <th>Captured Time</th>
                        <th>Email Sent?</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ( $index as $key ) : 
                        $cart = get_transient( $key );
                        if ( ! $cart ) continue;
                    ?>
                        <tr>
                            <td><strong><?php echo esc_html( $cart['email'] ); ?></strong></td>
                            <td><?php echo esc_html( ! empty( $cart['phone'] ) ? $cart['phone'] : '—' ); ?></td>
                            <td><?php echo wc_price( $cart['total'] ); ?></td>
                            <td><?php echo esc_html( count( $cart['cart'] ) ); ?> item(s)</td>
                            <td><?php echo esc_html( human_time_diff( $cart['updated_at'], time() ) . ' ago' ); ?></td>
                            <td>
                                <?php if ( ! empty( $cart['email_sent'] ) ) : ?>
                                    <span style="color: green; font-weight: bold;">Yes</span>
                                <?php else : ?>
                                    <span style="color: #ca3535; font-weight: bold;">Pending (Scheduled)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php
}
