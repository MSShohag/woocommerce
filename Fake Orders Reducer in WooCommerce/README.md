# Fake Orders Reducer in WooCommerce

A lightweight, zero-dependency WordPress plugin to mitigate spam, bot, and accidental duplicate orders by enforcing an IP-based cooldown period during checkout.

## Features
* **Accurate IP Detection:** Works out of the box with Cloudflare, reverse proxies, and Nginx setups.
* **Configurable Cooldown:** Adjust waiting time directly from **WooCommerce > Settings > General** (set `0` to disable).
* **Detailed User Feedback:** Shows previous Order ID, visitor IP, and remaining cooldown period in checkout notices.
* **Admin & Staff Bypass:** Store managers and administrators can test checkouts without triggers.
* **Performance Focused:** Uses native WooCommerce hooks without heavy external dependencies.

## Installation
1. Download this repository as a `.zip` archive or clone it into your WordPress `/wp-content/plugins/` directory.
2. Go to **WP Admin > Plugins** and click **Activate**.
3. Navigate to **WooCommerce > Settings > General** and scroll to **Fake Order Reducer Settings** to adjust your cooldown window.

## License
This project is licensed under the GPL-2.0 or later license.
