// Add Global Unique ID to WooCommerce product pages
function display_global_unique_id() {
    global $product;
    $global_unique_id = $product->get_meta( '_global_unique_id' );
    if ( ! empty( $global_unique_id ) ) {
        echo '<div class="product-global-unique-id"><strong>ID:</strong> ' . esc_html( $global_unique_id ) . '</div>';
    }
}
add_action( 'woocommerce_single_product_summary', 'display_global_unique_id', 25 );
