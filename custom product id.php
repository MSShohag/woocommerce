/**
 * UNIFIED SNIPPET: Shared GTIN/ID for Simple & Variable Products
 * Allows multiple products to share the same identifier value.
 */

// 1. ADD FIELD TO SIMPLE PRODUCTS (General Tab)
add_action( 'woocommerce_product_options_general_product_data', 'mss_shared_id_simple_field' );
function mss_shared_id_simple_field() {
    echo '<div class="options_group">';
    woocommerce_wp_text_input( array(
        'id'          => '_shared_gtin',
        'label'       => __( 'Shared ID / GTIN', 'woocommerce' ),
        'placeholder' => 'Enter number here',
        'desc_tip'    => 'true',
        'description' => __( 'This number can be reused across other products/variations.', 'woocommerce' ),
    ) );
    echo '</div>';
}

// 2. ADD FIELD TO VARIATIONS (Inside each variation)
add_action( 'woocommerce_product_after_variable_attributes', 'mss_shared_id_variable_field', 10, 3 );
function mss_shared_id_variable_field( $loop, $variation_data, $variation ) {
    woocommerce_wp_text_input( array(
        'id'            => '_shared_gtin[' . $loop . ']',
        'label'         => __( 'Variation Shared ID', 'woocommerce' ),
        'value'         => get_post_meta( $variation->ID, '_shared_gtin', true ),
        'wrapper_class' => 'form-row form-row-full',
    ) );
}

// 3. SAVE DATA FOR BOTH PRODUCT TYPES
add_action( 'woocommerce_process_product_meta', 'mss_save_shared_id' ); 
add_action( 'woocommerce_save_product_variation', 'mss_save_shared_id_variation', 10, 2 );

function mss_save_shared_id( $post_id ) {
    $val = isset( $_POST['_shared_gtin'] ) ? sanitize_text_field( $_POST['_shared_gtin'] ) : '';
    if ( ! is_array( $val ) ) { 
        update_post_meta( $post_id, '_shared_gtin', $val );
    }
}

function mss_save_shared_id_variation( $variation_id, $i ) {
    $val = isset( $_POST['_shared_gtin'][$i] ) ? sanitize_text_field( $_POST['_shared_gtin'][$i] ) : '';
    update_post_meta( $variation_id, '_shared_gtin', $val );
}

// 4. FRONT-END DISPLAY (Single Product Page)
add_action( 'woocommerce_single_product_summary', 'mss_display_shared_id_frontend', 35 );
function mss_display_shared_id_frontend() {
    global $product;

    // Check if product is variable
    if ( $product->is_type( 'variable' ) ) {
        // For variable products, we add a placeholder that JS will fill
        echo '<div id="mss-shared-id-display" class="shared-id-meta" style="margin-top:10px; font-weight:bold; display:none;">';
        echo esc_html__( 'Product ID:', 'woocommerce' ) . ' <span class="value"></span>';
        echo '</div>';

        // Inline JS to update the ID when a variation is selected
        ?>
        <script type="text/javascript">
            jQuery(function($) {
                $('form.variations_form').on('show_variation', function(event, variation) {
                    // Check if the variation has our custom meta
                    // Note: We need to pass the meta to the variation data (Step 5 below)
                    if ( variation.shared_id ) {
                        $('#mss-shared-id-display').show().find('.value').text(variation.shared_id);
                    } else {
                        $('#mss-shared-id-display').hide();
                    }
                }).on('hide_variation', function() {
                    $('#mss-shared-id-display').hide();
                });
            });
        </script>
        <?php
    } else {
        // For simple products
        $val = get_post_meta( $product->get_id(), '_shared_gtin', true );
        if ( ! empty( $val ) ) {
            echo '<div class="shared-id-meta" style="margin-top:10px; font-weight:bold;">';
            echo esc_html__( 'Product ID:', 'woocommerce' ) . ' ' . esc_html( $val );
            echo '</div>';
        }
    }
}

// 5. PASS META DATA TO VARIATION JSON (Needed for the JS above to work)
add_filter( 'woocommerce_available_variation', 'mss_add_shared_id_to_variation_data' );
function mss_add_shared_id_to_variation_data( $variations ) {
    $variations['shared_id'] = get_post_meta( $variations['variation_id'], '_shared_gtin', true );
    return $variations;
}
