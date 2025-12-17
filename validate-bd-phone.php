/**
 * Plugin Name: WooCommerce BD Phone Validation
 * Description: Ensures checkout phone numbers follow the 11-digit Bangladesh format.
 * Author: Your Name
 * Version: 1.0
 */

add_action('woocommerce_checkout_process', 'validate_bd_phone_number');
function validate_bd_phone_number() {
    $phone = $_POST['billing_phone'];
    // Regex matches 01 followed by 9 digits
    if ( !preg_match('/^(?:\+88|88)?(01[3-9]\d{8})$/', $phone) ) {
        wc_add_notice( __( '<strong>Error:</strong> Please enter a valid 11-digit BD phone number.' ), 'error' );
    }
}
