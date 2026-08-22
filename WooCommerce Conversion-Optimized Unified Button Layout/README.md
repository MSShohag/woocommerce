# WooCommerce Conversion-Optimized Unified Button Layout

A lightweight, plug-and-play PHP snippet to restructure the WooCommerce single product page button layout without overriding theme template files. Designed to increase conversion rates with dedicated secondary action buttons.

## Features
* **Custom Flexbox Layout:** Reorders the default WooCommerce buttons into a clean, two-row structure using CSS `order` to ensure no DOM conflicts with parent themes.
* **Row 1:** Features a custom-styled `[ - ] 1 [ + ]` quantity selector (styled to bypass native theme formatting) and a full-width "Buy Now" button complete with a CSS pulse animation.
* **Row 2:** Integrates third-party contact actions (Messenger & WhatsApp) alongside the default "Add to Cart" button.
* **Variable Product Support:** Includes JavaScript to ensure the "Buy Now" URL parameters update dynamically when a user selects product variations.
* **Responsive Design:** Maintains strict heights and flexbox proportions to prevent layout breakage on mobile devices.

## Installation & Usage
1. Copy the code from the `woocommerce-unified-buttons.php` file.
2. Add the snippet via the [Code Snippets](https://wordpress.org/plugins/code-snippets/) plugin or drop it directly into your active theme's `functions.php`.
3. Locate the `Configuration` section at the top of the function `custom_woo_unified_product_buttons()`.
4. Update the following variables with your specific project details:
    * `$phone_number = '1234567890';` (WhatsApp Number)
    * `$fb_page_id = 'your_page_id';` (Messenger ID)

## Compatibility
Tested and styled to override aggressive WooCommerce theme defaults using strict `!important` declarations, ensuring uniformity across different page builders and themes.
