/**
 * Shortcode to display "Save Amount (Percentage)" badge in WooCommerce/Elementor Loop.
 * Supports Simple and Variable products.
 */
add_shortcode('save_amount_badge', function() {
    // Get the product object for the current loop item
    $product = wc_get_product( get_the_ID() );

    if ( ! $product ) return '';

    $regular_price = 0;
    $sale_price = 0;

    // Handle Variable Products
    if ( $product->is_type( 'variable' ) ) {
        $prices = $product->get_variation_prices( true );
        
        if ( ! empty( $prices['sale_price'] ) ) {
            // Find the key for the minimum sale price to match it with its specific regular price
            $min_price_key = array_keys( $prices['sale_price'], min( $prices['sale_price'] ) )[0];
            $regular_price = $prices['regular_price'][$min_price_key];
            $sale_price = $prices['sale_price'][$min_price_key];
        }
    } else {
        // Handle Simple Products
        $regular_price = $product->get_regular_price();
        $sale_price = $product->get_sale_price();
    }

    // Only show if there is an actual discount and prices are valid
    if ( $sale_price > 0 && $sale_price < $regular_price ) {
        $save_amount = $regular_price - $sale_price;
        $save_percent = round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
        
        // Output matches the design requirement: Save 281৳ (-25%)
        return '<div class="elementor-woo-save-badge" style="color: #ed1c24; font-size: 14px; font-weight: 600; margin-bottom: 4px;">' . 
               'Save ' . number_format($save_amount, 0) . '৳ (-' . $save_percent . '%)' . 
               '</div>';
    }

    return '';
});
