add_action('woocommerce_checkout_process', 'normalize_and_validate_bd_phone_number');

function normalize_and_validate_bd_phone_number() {
    if ( isset($_POST['billing_phone']) ) {
        $phone = $_POST['billing_phone'];

        // 1. Sanitize: Keep only numbers (Removes +, -, spaces, etc.)
        // Input "+8801901-367984" becomes "8801901367984"
        // Input "01901-367984" becomes "01901367984"
        $clean_phone = preg_replace('/[^0-9]/', '', $phone);

        // 2. Normalize: If it starts with '88', remove it to get the 11-digit format
        if ( substr($clean_phone, 0, 2) === '88' ) {
            $clean_phone = substr($clean_phone, 2);
        }

        // 3. Update the Data: Overwrite the POST variable 
        // This ensures the CLEAN version is what gets saved to the WooCommerce Order
        $_POST['billing_phone'] = $clean_phone;

        // 4. Validate: Check if it is exactly 11 digits and starts with 013-019
        if ( ! preg_match('/^01[3-9]\d{8}$/', $clean_phone) ) {
             wc_add_notice( __( 'Please enter a valid **Bangladesh phone number** (e.g., 01712345678).' ), 'error' );
        }
    }
}
