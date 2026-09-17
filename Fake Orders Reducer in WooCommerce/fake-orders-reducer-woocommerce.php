<?php
/**
 * Plugin Name:       Fake Orders Reducer in WooCommerce
 * Plugin URI:        https://github.com/yourusername/fake-orders-reducer-woocommerce
 * Description:       Stops spam and duplicate orders by rate-limiting checkouts per IP address with configurable cooldown, admin bypass, and dynamic notice alerts.
 * Version:           1.0.0
 * Author:            Your Name
 * Author URI:        https://yourwebsite.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       fake-orders-reducer
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * WC requires at least: 5.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * ============================================================================
 * FEATURE 1: Client IP Address Detection
 * Description: Resolves the real customer IP via WooCommerce Geolocation or
 *              proxy/CDN headers (Cloudflare, Nginx, load balancers).
 * ============================================================================
 */
// --- START FEATURE 1: IP Detection ---
function eideshi_get_customer_ip() {
    if (class_exists('WC_Geolocation')) {
        $ip = WC_Geolocation::get_ip_address();
        if (!empty($ip)) {
            return sanitize_text_field($ip);
        }
    }

    $keys = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_REAL_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_CLIENT_IP',
        'REMOTE_ADDR',
    ];

    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            if (strpos($ip, ',') !== false) {
                $ip = explode(',', $ip)[0];
            }
            return sanitize_text_field(trim($ip));
        }
    }

    return '';
}
// --- END FEATURE 1: IP Detection ---


/**
 * ============================================================================
 * FEATURE 2: Order Rate-Limiting & Checkout Blocking
 * Description: Checks recent orders from the IP within a cooldown window,
 *              bypasses admins, and alerts user with order ID and IP info.
 * ============================================================================
 */
// --- START FEATURE 2: Order Rate Limiting ---
add_action('woocommerce_checkout_process', function () {

    // 1. Bypass check for store managers and administrators
    if (current_user_can('manage_woocommerce') || current_user_can('administrator')) {
        return;
    }

    // 2. Cooldown duration settings (defaults to 2 hours)
    $saved_hours      = get_option('eideshi_order_cooldown_hours', 2);
    $default_cooldown = absint($saved_hours) * HOUR_IN_SECONDS;
    $cooldown         = apply_filters('eideshi_checkout_cooldown_duration', $default_cooldown);

    // If cooldown is set to 0, feature is disabled
    if ($cooldown <= 0) {
        return;
    }

    $ip = eideshi_get_customer_ip();
    if (empty($ip)) {
        return;
    }

    // 3. Search for recent orders matching this IP
    $orders = wc_get_orders([
        'limit'        => 1,
        'orderby'      => 'date',
        'order'        => 'DESC',
        'status'       => ['pending', 'processing', 'completed', 'on-hold'],
        'date_created' => '>' . (time() - $cooldown),
        'meta_query'   => [
            [
                'key'   => '_customer_ip',
                'value' => $ip,
            ],
        ],
        'return'       => 'ids',
    ]);

    if (!empty($orders)) {
        $recent_order_id = $orders[0];
        $hours_display   = round($cooldown / HOUR_IN_SECONDS, 1);

        $message = sprintf(
            '⚠️ আপনি সম্প্রতি একটি অর্ডার করেছেন (অর্ডার আইডি: #%1$s, আইপি: %2$s)। অনুগ্রহ করে %3$s ঘণ্টা পরে আবার চেষ্টা করুন।',
            esc_html($recent_order_id),
            esc_html($ip),
            esc_html($hours_display)
        );

        wc_add_notice(apply_filters('eideshi_cooldown_error_message', $message, $cooldown, $recent_order_id, $ip), 'error');
    }

});
// --- END FEATURE 2: Order Rate Limiting ---


/**
 * ============================================================================
 * FEATURE 3: Store Order IP Meta
 * Description: Attaches customer IP as _customer_ip to each placed order.
 * ============================================================================
 */
// --- START FEATURE 3: Store Order IP Meta ---
add_action('woocommerce_checkout_create_order', function ($order) {
    $ip = eideshi_get_customer_ip();
    if (!empty($ip)) {
        $order->update_meta_data('_customer_ip', $ip);
    }
});
// --- END FEATURE 3: Store Order IP Meta ---


/**
 * ============================================================================
 * FEATURE 4: Admin Settings Under WooCommerce > Settings > General
 * Description: Allows store owners to set cooldown duration in WP Admin.
 * ============================================================================
 */
// --- START FEATURE 4: Settings Field ---
add_filter('woocommerce_general_settings', function ($settings) {
    $settings[] = [
        'title' => 'Fake Order Reducer Settings',
        'type'  => 'title',
        'desc'  => 'Configure rate-limiting duration to stop duplicate and spam checkouts.',
        'id'    => 'eideshi_order_limit_options',
    ];

    $settings[] = [
        'title'    => 'IP Cooldown (Hours)',
        'desc'     => 'Hours a customer must wait before placing another order from the same IP (set 0 to disable).',
        'id'       => 'eideshi_order_cooldown_hours',
        'type'     => 'number',
        'default'  => '2',
        'css'      => 'width: 80px;',
        'desc_tip' => true,
    ];

    $settings[] = [
        'type' => 'sectionend',
        'id'   => 'eideshi_order_limit_options',
    ];

    return $settings;
});
// --- END FEATURE 4: Settings Field ---
