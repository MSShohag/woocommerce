# WooCommerce Pre-Order with Partial Payment & Delivery Note

A lightweight, zero-dependency WordPress / WooCommerce solution that enables **pre-orders with partial deposit payments** (e.g., 50% advance) and **custom estimated delivery notes** for both Simple and Variable products.

---

## Features

- ⚙️ **Simple & Variable Product Support**: Enable pre-orders globally or per variation.
- 💳 **Customizable Deposit %**: Charge an advance percentage at checkout (e.g., 50%, 30%, etc.).
- 📦 **Estimated Delivery Notes**: Display custom notices (e.g., `15 working days delivery`) on product pages, checkout review tables, and order emails.
- 🔄 **Live Swatch Switching**: Dynamic button text and delivery notes update smoothly when selecting variations without reloading the page.
- 🛒 **Single-Item Cart Enforcement**: Empties the cart when a pre-order is added to prevent mixed checkout complications.
- ⚡ **Direct Checkout Redirect**: Routes users straight to the checkout page upon clicking the Pre-Order button.
- 📊 **Detailed Order Breakdown**: Explicitly calculates and records:
  - **Full Product Price**
  - **Advance Deposit Paid (%)**
  - **Remaining Balance Due**
  - **Delivery Note**
- 🛡️ **No Third-Party Paid Plugins**: Free from heavy monthly subscriptions or plugin conflicts.

---

## Installation

### Option 1: As a Standalone WordPress Plugin (Recommended)
1. Download this repository as a `.zip` file or clone it into `/wp-content/plugins/woocommerce-preorder-partial-payment`.
2. In your WordPress Admin, navigate to **Plugins > Installed Plugins**.
3. Locate **WooCommerce Pre-Order with Partial Payment & Delivery Note** and click **Activate**.

### Option 2: Via `functions.php` or Code Snippets
1. Open `woocommerce-preorder-partial-payment.php`.
2. Copy the PHP code (excluding `<?php defined('ABSPATH') || exit;`).
3. Paste it into your active child theme's `functions.php` or add it as a new PHP snippet in the **WPCode** / **Code Snippets** plugin.

---

## How to Configure

### For Simple Products
1. Go to **Products > All Products** and edit a product.
2. In the **Product Data** box under the **General** tab:
   - Check **Enable Pre-Order**.
   - Set **Deposit Advance (%)** (e.g., `50`).
   - Enter **Delivery Note** (e.g., `15 working days delivery`).
3. Click **Update**.

### For Variable Products
1. Go to the **Variations** tab under **Product Data**.
2. Expand the specific variation you want to offer as a pre-order.
3. Check **Enable Pre-Order for this variation**.
4. Set the **Deposit Advance (%)** and **Delivery Note**.
5. Click **Save Changes** on the variation and **Update** the product.

---

## License

This project is licensed under the [GPL-2.0-or-later](LICENSE).
