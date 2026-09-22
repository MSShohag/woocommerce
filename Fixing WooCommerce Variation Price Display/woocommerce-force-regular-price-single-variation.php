/**
 * Title: WooCommerce Force Strikethrough Regular Price for Single Variations
 * Description: Fixes WooCommerce suppressing the strikethrough <del> regular price
 *              and emptying the variation `price_html` container when only one 
 *              variation is priced or when a variation matches the parent product price.
 * Author: Shahrear Shohag
 * Version: 1.0.0
 * Compatibility: WooCommerce 7.0+
 */

// 1. Force the main top header price to show strike-through when only one variation is priced & on sale
add_filter( 'woocommerce_variable_price_html', function( $price, $product ) {
    $prices = $product->get_variation_prices( true );

    if ( empty( $prices['price'] ) ) {
        return $price;
    }

    $min_price     = current( $prices['price'] );
    $max_price     = end( $prices['price'] );
    $min_reg_price = current( $prices['regular_price'] );
    $max_reg_price = end( $prices['regular_price'] );

    // If only one variation is priced and it has a discount
    if ( $min_price === $max_price && $product->is_on_sale() ) {
        return '<del aria-hidden="true">' . wc_price( $max_reg_price ) . '</del> <ins>' . wc_price( $min_price ) . '</ins>';
    }

    return $price;
}, 10, 2 );

// 2. Ensure WooCommerce outputs the strike-through HTML for the selected variation
add_filter( 'woocommerce_show_variation_price', '__return_true' );

add_filter( 'woocommerce_available_variation', function( $data, $product, $variation ) {
    if ( $variation->is_on_sale() ) {
        $regular_price = wc_price( $variation->get_regular_price() );
        $sale_price    = wc_price( $variation->get_sale_price() );
        $data['price_html'] = '<span class="price"><del aria-hidden="true">' . $regular_price . '</del> <ins>' . $sale_price . '</ins></span>';
    }
    return $data;
}, 10, 3 );
