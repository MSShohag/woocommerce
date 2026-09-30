<?php
/**
 * Plugin Name: WC Abandoned Cart to Google Sheets
 * Description: Streams abandoned checkout leads (Email and Phone) to Google Sheets via non-blocking asynchronous POST requests. Zero DB writes.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPLv2 or later
 * Text Domain: wc-abandoned-cart-sheets
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =========================================================================
// CONFIGURATION: Paste your Google Apps Script Web App URL below
// =========================================================================
if ( ! defined( 'WC_SHEETS_WEBHOOK_URL' ) ) {
    define( 'WC_SHEETS_WEBHOOK_URL', 'YOUR_GOOGLE_APPS_SCRIPT_WEB_APP_URL_HERE' );
}

/**
 * 1. Inject minimal vanilla JS on checkout page
 */
add_action( 'wp_footer', 'wcsheets_inject_checkout_capture_js' );
function wcsheets_inject_checkout_capture_js() {
    if ( ! is_checkout() || is_order_received_page() ) {
        return;
    }
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const emailField = document.getElementById('billing_email');
        const phoneField = document.getElementById('billing_phone');

        let lastCapturedEmail = '';
        let lastCapturedPhone = '';

        function sendCartToSheets() {
            const email = emailField ? emailField.value.trim() : '';
            const phone = phoneField ? phoneField.value.trim() : '';

            // Need at least a valid email format to trigger
            if ( ! email || ! email.includes('@') || ! email.includes('.') ) {
                return;
            }

            // Do not fire redundant pings if fields have not changed
            if ( email === lastCapturedEmail && phone === lastCapturedPhone ) {
                return;
            }

            lastCapturedEmail = email;
            lastCapturedPhone = phone;

            const payload = new URLSearchParams();
            payload.append( 'action', 'wcsheets_stream_cart' );
            payload.append( 'email', email );
            payload.append( 'phone', phone );

            fetch( '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: payload
            }).catch(function (err) {
                // Non-blocking failover
            });
        }

        if ( emailField ) {
            emailField.addEventListener( 'blur', sendCartToSheets );
        }
        if ( phoneField ) {
            phoneField.addEventListener( 'blur', sendCartToSheets );
        }
    });
    </script>
    <?php
}

/**
 * 2. Handle AJAX and forward payload to Google Sheets asynchronously
 */
add_action( 'wp_ajax_wcsheets_stream_cart', 'wcsheets_handle_stream_cart' );
add_action( 'wp_ajax_nopriv_wcsheets_stream_cart', 'wcsheets_handle_stream_cart' );

function wcsheets_handle_stream_cart() {
    if ( empty( $_POST['email'] ) || ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
        wp_die();
    }

    $email = sanitize_email( wp_unslash( $_POST['email'] ) );
    $phone = isset( $_POST['phone'] ) ? wc_clean( wp_unslash( $_POST['phone'] ) ) : '';

    if ( ! is_email( $email ) ) {
        wp_die();
    }

    // Prepare item summaries
    $items = array();
    foreach ( WC()->cart->get_cart() as $values ) {
        $product = $values['data'];
        $qty     = $values['quantity'];
        $items[] = $product->get_name() . ' (x' . $qty . ')';
    }

    $payload = wp_json_encode( array(
        'email'        => $email,
        'phone'        => $phone,
        'items'        => implode( ', ', $items ),
        'total'        => html_entity_decode( strip_tags( WC()->cart->get_cart_total() ) ),
        'recovery_url' => wc_get_checkout_url(),
    ) );

    $webhook_url = WC_SHEETS_WEBHOOK_URL;

    if ( empty( $webhook_url ) || strpos( $webhook_url, 'YOUR_GOOGLE_APPS_SCRIPT' ) !== false ) {
        wp_die();
    }

    /**
     * Non-blocking HTTP POST:
     * Releases PHP execution thread immediately without waiting for Google to respond.
     */
    wp_remote_post( $webhook_url, array(
        'method'      => 'POST',
        'timeout'     => 1,
        'blocking'    => false, // Essential: prevents slow response times from blocking checkout
        'headers'     => array( 'Content-Type' => 'application/json' ),
        'body'        => $payload,
        'data_format' => 'body',
    ) );

    wp_die();
}
