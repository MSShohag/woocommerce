# WC Abandoned Cart Recovery (Local Transients)

A zero-bloat, self-hosted cart recovery plugin for WooCommerce. It captures the customer's email and phone number as soon as they unfocus (`blur`) from checkout fields, stores session cart items in temporary transients, and sends a single recovery email via WP-Cron 1 hour later.

## Features
- **No Third-Party Services:** Operates entirely within your WordPress database and mailer.
- **Captures Email & Phone:** Stores phone numbers alongside email addresses for records.
- **Low Database Footprint:** Uses WordPress transients with a 3-day TTL. Cleans up automatically upon order completion.
- **No JS Frameworks:** Uses native vanilla JS `fetch()`—no jQuery or heavy libraries.

## Installation

1. Copy the `1-local-transient-version` folder into `wp-content/plugins/` (or compress `wc-abandoned-cart-local.php` into a `.zip` and upload via **Plugins > Add New > Upload Plugin**).
2. Go to **Plugins > Installed Plugins** and click **Activate** under **WC Abandoned Cart Recovery (Local Transients)**.
3. Done. The cron job will automatically schedule itself.

## How to Test
1. Open an incognito browser window and add a product to your cart.
2. Go to Checkout, type your email and phone number, then click into another field.
3. Open your database or inspect the `wp_options` table: you will see a transient named `_transient_wc_ac_<md5_hash>` containing your cart data.
4. Complete the purchase: the transient is deleted immediately upon reaching the Order Received page.

## Performance Tip (Recommended)
By default, WordPress cron runs only when a visitor visits your site. For high-traffic stores, disable web cron and enable real server cron:
1. Add to `wp-config.php`:
   ```php
   define( 'DISABLE_WP_CRON', true );

Add a crontab entry on your server running every 15 minutes:

Bash
*/15 * * * * curl -s [https://yourdomain.com/wp-cron.php?doing_wp_cron](https://yourdomain.com/wp-cron.php?doing_wp_cron) > /dev/null 2>&1

---


