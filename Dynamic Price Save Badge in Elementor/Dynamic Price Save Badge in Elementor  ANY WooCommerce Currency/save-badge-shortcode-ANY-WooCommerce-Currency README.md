# WooCommerce Dynamic Savings Badge for Elementor Loop Builder

A professional, lightweight PHP shortcode designed for the **Elementor Loop Builder**. This snippet displays a dynamic "Save Amount" and "Percentage" badge for products on sale. It is fully optimized for **Simple** and **Variable** products and automatically adapts to any currency used in your WooCommerce settings.

## Features
*   **Universal Currency Support:** Uses `wc_price()` to automatically display the correct currency symbol (৳, $, €, etc.) and formatting based on your store's settings.
*   **Variable Product Logic:** Smartly calculates the discount based on the variation with the lowest sale price.
*   **Dynamic Percentage:** Automatically calculates and rounds the discount percentage.
*   **Elementor Ready:** Specifically built for use within Loop Templates via the Shortcode widget.
*   **Clean Rendering:** The badge only appears if the product is actually on sale.

## Installation

1.  **Add the Code:** Copy the code from `save-badge-shortcode.php` and paste it into your theme's `functions.php` file (preferably a child theme).
2.  **Edit Loop Template:** Open your Elementor Loop Item template.
3.  **Insert Shortcode:** Drag a **Shortcode Widget** to the desired location (e.g., above the price).
4.  **Enter Tag:** Use the shortcode `[save_amount_badge]`.

## Customization
The output is wrapped in a `div` with a custom class `elementor-woo-save-badge`. You can change the red color (`#ed1c24`) or font styling directly in the PHP snippet to match your brand's aesthetic.

## Technical Details
*   **Shortcode:** `[save_amount_badge]`
*   **Compatibility:** WooCommerce 3.0+, Elementor Pro (Loop Builder).
*   **Calculation:** `(Regular Price - Sale Price) = Savings`.
