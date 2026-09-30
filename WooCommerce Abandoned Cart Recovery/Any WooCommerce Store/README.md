# WooCommerce Abandoned Cart Recovery (Universal / Global)

A lightweight, zero-dependency abandoned cart capture and recovery engine compatible with any WooCommerce store worldwide. Tracks leads in real-time, generates 1-click cart restore links with variable product support, and integrates directly with WhatsApp.

---

## Key Features

1. **Universal International Number Support:**
   - Validates phone numbers using standard international E.164 lengths (7 to 15 digits).
   - Allows users to type international country prefixes (e.g., `+1`, `+44`, `+49`, `+971`).
2. **Dual Capture (Phone & Email):**
   - Captures checkout data when a shopper enters either an email or phone number.
   - Automatically unifies and deduplicates records by phone or email address.
3. **Exact 1-Click Cart Restoration:**
   - Handles simple and **variable products** (including chosen colors, sizes, attributes, and quantities).
   - Restores the cart across sessions, devices, and incognito browsers via URL-safe Base64 query parameters.
4. **WhatsApp Direct Recovery:**
   - Sends a pre-formatted message in English with the customer's name and exact cart restoration link.
5. **Zero Database Clutter:**
   - Saved as self-expiring WordPress transients (3-day lifetime).
   - Cleans up records automatically the moment a checkout order is created or reaches the thank-you page.

---

## Installation

### Method A: As a Standalone Plugin (Recommended)
1. Copy the `2-global-store` directory to your `wp-content/plugins/` directory.
2. In the WordPress Admin, go to **Plugins > Installed Plugins**.
3. Click **Activate** under **WC Abandoned Cart Recovery (Universal / Global)**.

### Method B: Via `functions.php`
1. Open your child theme's `functions.php`.
2. Copy the code from `wc-abandoned-cart-global.php` (omit `<?php` if your file already begins with it).
3. Save the file.

---

## Admin Management

- Access all tracked sessions via **WooCommerce > Abandoned Carts**.
- **WhatsApp:** Opens a direct chat with the shopper, pre-filling a personalized recovery link.
- **Copy URL:** Copies the exact 1-click cart restoration link to your clipboard.
- **Delete / Clear All:** Allows removal of individual records or batch clearing with CSRF nonce verification.
