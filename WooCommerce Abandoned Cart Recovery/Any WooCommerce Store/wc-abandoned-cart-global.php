<?php
/**
 * Plugin Name: WC Abandoned Cart Recovery (Universal / Global)
 * Description: Adaptive abandoned cart tracker for any WooCommerce store worldwide. Works with Phone-only, Email-only, or Both. Supports variable product 1-click restore and WhatsApp.
 * Version: 1.2.0
 * Author: Your Name
 * License: GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =========================================================================
// 1. NON-BLOCKING INTERNATIONAL PHONE VALIDATION
// =========================================================================
add_action( 'woocommerce_checkout_process', 'wcglob_validate_checkout_fields' );
function wcglob_validate_checkout_fields() {
    // Only validate phone if present and filled
    if ( ! empty( $_POST['billing_phone'] ) ) {
        $phone = sanitize_text_field( wp_unslash( $_POST['billing_phone'] ) );
        $clean_digits = preg_replace( '/[^0-9]/', '', $phone );

        if ( strlen( $clean_digits ) < 7 || strlen( $clean_digits ) > 15 ) {
            wc_add_notice( __( 'Please enter a valid phone number (7 to 15 digits).' ), 'error' );
        }
    }
}

// =========================================================================
// 2. 1-CLICK UNIVERSAL CART RESTORER (SIMPLE & VARIABLE PRODUCTS)
// =========================================================================
add_action( 'template_redirect', 'wcglob_restore_cart_from_url' );
function wcglob_restore_cart_from_url() {
    if ( ! isset( $_GET['restore_cart'] ) || ! function_exists( 'WC' ) || ! WC()->cart ) {
        return;
    }

    $raw_token = sanitize_text_field( wp_unslash( $_GET['restore_cart'] ) );
    if ( empty( $raw_token ) ) {
        return;
    }

    $items_to_restore = array();
    $decoded_json     = base64_decode( strtr( $raw_token, '-_', '+/' ), true );

    if ( $decoded_json ) {
        $parsed = json_decode( $decoded_json, true );
        if ( is_array( $parsed ) ) {
            $items_to_restore = $parsed;
        }
    }

    if ( ! empty( $items_to_restore ) ) {
        WC()->cart->empty_cart();

        foreach ( $items_to_restore as $item ) {
            $product_id   = isset( $item['p'] ) ? intval( $item['p'] ) : 0;
            $quantity     = isset( $item['q'] ) ? intval( $item['q'] ) : 1;
            $variation_id = isset( $item['v'] ) ? intval( $item['v'] ) : 0;
            $variation    = isset( $item['a'] ) && is_array( $item['a'] ) ? $item['a'] : array();

            if ( $product_id > 0 && $quantity > 0 ) {
                WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation );
            }
        }

        wp_safe_redirect( wc_get_checkout_url() );
        exit;
    }
}

// =========================================================================
// 3. FRONTEND SCRIPT (ADAPTIVE CHECKOUT DETECTOR)
// =========================================================================
add_action( 'wp_footer', 'wcglob_inject_checkout_capture_js' );
function wcglob_inject_checkout_capture_js() {
    if ( ! is_checkout() || is_order_received_page() ) {
        return;
    }
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function ($) {
        var lastSnapshot = '';

        function triggerCartCapture() {
            var $phoneField = $('#billing_phone, input[type="tel"]');
            var $emailField = $('#billing_email, input[type="email"]');
            var $nameField  = $('#billing_first_name');
            var $addrField  = $('#billing_address_1');

            var phone = $phoneField.length ? $phoneField.val().trim() : '';
            var email = $emailField.length ? $emailField.val().trim() : '';
            var name  = $nameField.length ? $nameField.val().trim() : '';
            var addr  = $addrField.length ? $addrField.val().trim() : '';

            var digitsOnly = phone.replace(/\D/g, '');
            var hasValidPhone = (digitsOnly.length >= 7 && digitsOnly.length <= 15);
            var hasValidEmail = (email.indexOf('@') > 0 && email.indexOf('.') > 0);

            // Require at least one valid identifier
            if ( ! hasValidPhone && ! hasValidEmail ) {
                return;
            }

            var snapshot = phone + '|' + email + '|' + name + '|' + addr;
            if ( snapshot === lastSnapshot ) {
                return;
            }
            lastSnapshot = snapshot;

            $.ajax({
                url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
                type: 'POST',
                data: {
                    action: 'wcglob_save_adaptive_cart',
                    phone: hasValidPhone ? phone : '',
                    email: hasValidEmail ? email : '',
                    name: name,
                    address: addr
                }
            });
        }

        $(document).on('blur', '#billing_phone, #billing_email, #billing_first_name, #billing_address_1, input[type="tel"], input[type="email"]', triggerCartCapture);
    });
    </script>
    <?php
}

// =========================================================================
// 4. BACKEND AJAX (INTELLIGENT DEDUPLICATION & MERGE)
// =========================================================================
add_action( 'wp_ajax_wcglob_save_adaptive_cart', 'wcglob_handle_save_adaptive_cart' );
add_action( 'wp_ajax_nopriv_wcglob_save_adaptive_cart', 'wcglob_handle_save_adaptive_cart' );

function wcglob_handle_save_adaptive_cart() {
    if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
        wp_die();
    }

    $raw_phone = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
    $email     = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
    $name      = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
    $address   = isset( $_POST['address'] ) ? sanitize_text_field( wp_unslash( $_POST['address'] ) ) : '';

    $phone_digits = preg_replace( '/[^0-9]/', '', $raw_phone );

    if ( empty( $phone_digits ) && empty( $email ) ) {
        wp_die();
    }

    $index = get_option( 'wcglob_abandoned_cart_index', array() );
    if ( ! is_array( $index ) ) {
        $index = array();
    }

    // Check if an existing transient matches this phone OR email
    $target_key = '';
    $existing   = false;

    foreach ( $index as $key ) {
        $data = get_transient( $key );
        if ( ! $data ) continue;

        $existing_digits = ! empty( $data['phone'] ) ? preg_replace( '/[^0-9]/', '', $data['phone'] ) : '';
        $match_phone     = ( ! empty( $phone_digits ) && ! empty( $existing_digits ) && $existing_digits === $phone_digits );
        $match_email     = ( ! empty( $email ) && ! empty( $data['email'] ) && strtolower( $data['email'] ) === strtolower( $email ) );

        if ( $match_phone || $match_email ) {
            $target_key = $key;
            $existing   = $data;
            break;
        }
    }

    // If no match found, create a new unique transient key
    if ( empty( $target_key ) ) {
        $seed = ! empty( $phone_digits ) ? 'p_' . $phone_digits : 'e_' . strtolower( $email );
        $target_key = 'wc_ac_' . md5( $seed );
    }

    $items          = array();
    $restore_tokens = array();

    foreach ( WC()->cart->get_cart() as $values ) {
        $product      = $values['data'];
        $qty          = $values['quantity'];
        $product_id   = ! empty( $values['product_id'] ) ? $values['product_id'] : $product->get_id();
        $variation_id = ! empty( $values['variation_id'] ) ? $values['variation_id'] : 0;
        $variation    = ! empty( $values['variation'] ) ? $values['variation'] : array();

        $items[] = array(
            'name' => $product->get_name(),
            'qty'  => $qty,
            'url'  => $product->get_permalink(),
        );

        $restore_tokens[] = array(
            'p' => $product_id,
            'v' => $variation_id,
            'q' => $qty,
            'a' => $variation,
        );
    }

    $json_token     = wp_json_encode( $restore_tokens );
    $safe_token     = rtrim( strtr( base64_encode( $json_token ), '+/', '-_' ), '=' );
    $exact_cart_url = add_query_arg( 'restore_cart', $safe_token, wc_get_checkout_url() );

    $saved_data = array(
        'phone'          => $raw_phone ?: ( $existing && ! empty( $existing['phone'] ) ? $existing['phone'] : '' ),
        'email'          => $email ?: ( $existing && ! empty( $existing['email'] ) ? $existing['email'] : '' ),
        'name'           => $name ?: ( $existing && ! empty( $existing['name'] ) ? $existing['name'] : '—' ),
        'address'        => $address ?: ( $existing && ! empty( $existing['address'] ) ? $existing['address'] : '—' ),
        'items'          => $items,
        'exact_cart_url' => $exact_cart_url,
        'total'          => WC()->cart->get_cart_total(),
        'updated_at'     => time(),
    );

    set_transient( $target_key, $saved_data, 3 * DAY_IN_SECONDS );

    if ( ! in_array( $target_key, $index, true ) ) {
        $index[] = $target_key;
        update_option( 'wcglob_abandoned_cart_index', array_values( array_unique( $index ) ), false );
    }

    wp_die();
}

// =========================================================================
// 5. PURGE RECORD IMMEDIATELY ON SUCCESSFUL ORDER PLACEMENT
// =========================================================================
function wcglob_cleanup_order( $order_id ) {
    $order = wc_get_order( $order_id );
    if ( ! $order ) return;

    $phone_digits = preg_replace( '/[^0-9]/', '', $order->get_billing_phone() );
    $email        = strtolower( trim( $order->get_billing_email() ) );

    $index = get_option( 'wcglob_abandoned_cart_index', array() );
    if ( ! is_array( $index ) || empty( $index ) ) return;

    $updated_index = array();
    foreach ( $index as $key ) {
        $data = get_transient( $key );
        if ( ! $data ) continue;

        $existing_digits = ! empty( $data['phone'] ) ? preg_replace( '/[^0-9]/', '', $data['phone'] ) : '';
        $match_phone     = ( ! empty( $phone_digits ) && ! empty( $existing_digits ) && $existing_digits === $phone_digits );
        $match_email     = ( ! empty( $email ) && ! empty( $data['email'] ) && strtolower( $data['email'] ) === $email );

        if ( $match_phone || $match_email ) {
            delete_transient( $key );
        } else {
            $updated_index[] = $key;
        }
    }

    update_option( 'wcglob_abandoned_cart_index', array_values( $updated_index ), false );
}

add_action( 'woocommerce_checkout_order_processed', 'wcglob_cleanup_order', 10, 1 );
add_action( 'woocommerce_thankyou', 'wcglob_cleanup_order', 10, 1 );

// =========================================================================
// 6. ADMIN DASHBOARD VIEW UNDER WOOCOMMERCE > ABANDONED CARTS
// =========================================================================
add_action( 'admin_menu', 'wcglob_register_admin_page' );
function wcglob_register_admin_page() {
    add_submenu_page(
        'woocommerce',
        'Abandoned Carts',
        'Abandoned Carts',
        'manage_woocommerce',
        'wc-global-abandoned-carts',
        'wcglob_render_admin_page'
    );
}

add_action( 'admin_init', 'wcglob_handle_admin_actions' );
function wcglob_handle_admin_actions() {
    if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'wc-global-abandoned-carts' ) {
        return;
    }

    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        return;
    }

    if ( isset( $_GET['action'], $_GET['key'], $_GET['_wpnonce'] ) && $_GET['action'] === 'delete' ) {
        $key = sanitize_text_field( wp_unslash( $_GET['key'] ) );
        if ( wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'wcglob_delete_' . $key ) ) {
            delete_transient( $key );

            $index = get_option( 'wcglob_abandoned_cart_index', array() );
            if ( is_array( $index ) && in_array( $key, $index, true ) ) {
                $index = array_diff( $index, array( $key ) );
                update_option( 'wcglob_abandoned_cart_index', array_values( $index ), false );
            }

            wp_safe_redirect( admin_url( 'admin.php?page=wc-global-abandoned-carts' ) );
            exit;
        }
    }

    if ( isset( $_GET['action'], $_GET['_wpnonce'] ) && $_GET['action'] === 'clear_all' ) {
        if ( wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'wcglob_clear_all' ) ) {
            $index = get_option( 'wcglob_abandoned_cart_index', array() );
            if ( is_array( $index ) ) {
                foreach ( $index as $key ) {
                    delete_transient( $key );
                }
            }
            delete_option( 'wcglob_abandoned_cart_index' );

            wp_safe_redirect( admin_url( 'admin.php?page=wc-global-abandoned-carts' ) );
            exit;
        }
    }
}

function wcglob_render_admin_page() {
    $index = get_option( 'wcglob_abandoned_cart_index', array() );
    $clear_all_url = wp_nonce_url( admin_url( 'admin.php?page=wc-global-abandoned-carts&action=clear_all' ), 'wcglob_clear_all' );
    ?>
    <div class="wrap">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
            <h1 style="margin: 0;">Captured Abandoned Carts</h1>
            <?php if ( ! empty( $index ) ) : ?>
                <a href="<?php echo esc_url( $clear_all_url ); ?>" 
                   onclick="return confirm('Are you sure you want to delete all captured carts?');" 
                   class="button button-secondary" 
                   style="color: #b32d2e; border-color: #b32d2e;">
                   Clear All Carts
                </a>
            <?php endif; ?>
        </div>

        <?php if ( empty( $index ) ) : ?>
            <p>No abandoned carts captured yet.</p>
        <?php else : ?>
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 150px;">Contact</th>
                        <th style="width: 120px;">Name</th>
                        <th>Address</th>
                        <th>Cart Items</th>
                        <th style="width: 90px;">Total</th>
                        <th style="width: 100px;">Time</th>
                        <th style="width: 210px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ( $index as $key ) : 
                        $cart = get_transient( $key );
                        if ( ! $cart ) continue;

                        $raw_digits = ! empty( $cart['phone'] ) ? preg_replace( '/[^0-9]/', '', $cart['phone'] ) : '';
                        $exact_url  = ! empty( $cart['exact_cart_url'] ) ? $cart['exact_cart_url'] : wc_get_checkout_url();

                        $greeting = ( $cart['name'] !== '—' ) ? ' ' . $cart['name'] : '';
                        $wa_message = rawurlencode(
                            "Hi" . $greeting . ",\n" .
                            "We saved the items from your cart! Complete your purchase anytime here:\n" .
                            $exact_url
                        );

                        $delete_url = wp_nonce_url(
                            admin_url( 'admin.php?page=wc-global-abandoned-carts&action=delete&key=' . urlencode( $key ) ),
                            'wcglob_delete_' . $key
                        );
                    ?>
                        <tr>
                            <td>
                                <?php if ( ! empty( $cart['phone'] ) ) : ?>
                                    <strong><?php echo esc_html( $cart['phone'] ); ?></strong><br>
                                <?php endif; ?>
                                <?php if ( ! empty( $cart['email'] ) ) : ?>
                                    <small style="color: #555;"><?php echo esc_html( $cart['email'] ); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html( $cart['name'] ); ?></td>
                            <td><?php echo esc_html( $cart['address'] ); ?></td>
                            <td>
                                <?php 
                                if ( is_array( $cart['items'] ) ) {
                                    $item_links = array();
                                    foreach ( $cart['items'] as $item ) {
                                        $name = ! empty( $item['name'] ) ? esc_html( $item['name'] ) : 'Product';
                                        $qty  = ! empty( $item['qty'] ) ? ' &times; ' . intval( $item['qty'] ) : '';
                                        $url  = ! empty( $item['url'] ) ? esc_url( $item['url'] ) : '';

                                        if ( $url ) {
                                            $item_links[] = sprintf(
                                                '<a href="%s" target="_blank" rel="noopener noreferrer" style="font-weight: 500; text-decoration: underline;">%s</a><span style="color:#666;">%s</span>',
                                                $url, $name, $qty
                                            );
                                        } else {
                                            $item_links[] = $name . '<span style="color:#666;">' . $qty . '</span>';
                                        }
                                    }
                                    echo implode( '<br>', $item_links );
                                } else {
                                    echo esc_html( $cart['items'] );
                                }
                                ?>
                            </td>
                            <td><?php echo wp_kses_post( $cart['total'] ); ?></td>
                            <td><?php echo esc_html( human_time_diff( $cart['updated_at'], time() ) . ' ago' ); ?></td>
                            <td>
                                <?php if ( ! empty( $raw_digits ) ) : ?>
                                    <a href="https://wa.me/<?php echo esc_attr( $raw_digits ); ?>?text=<?php echo $wa_message; ?>" 
                                       target="_blank" 
                                       class="button button-small" 
                                       style="background:#25D366; color:#fff; border-color:#25D366; margin-right: 4px;">
                                       WhatsApp
                                    </a>
                                <?php endif; ?>
                                <button type="button" 
                                        onclick="navigator.clipboard.writeText('<?php echo esc_js( $exact_url ); ?>'); alert('Cart URL copied to clipboard!');" 
                                        class="button button-small" 
                                        title="Copy 1-Click Cart URL"
                                        style="margin-right: 4px;">
                                   Copy URL
                                </button>
                                <a href="<?php echo esc_url( $delete_url ); ?>" 
                                   onclick="return confirm('Delete this cart entry?');" 
                                   class="button button-small button-link-delete" 
                                   style="color: #a00;">
                                   Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php
}
