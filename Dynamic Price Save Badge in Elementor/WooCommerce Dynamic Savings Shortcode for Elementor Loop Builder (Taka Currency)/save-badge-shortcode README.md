# WooCommerce Dynamic Savings Badge for Elementor Loop Builder

A lightweight PHP shortcode designed for Elementor Loop Builder to display a dynamic "Save Amount" and "Percentage" badge. It works for both **Simple** and **Variable** products, automatically calculating the discount based on the lowest variation price.

## Features
*   **Simple & Variable Support:** Correcty handles variation price ranges by targeting the minimum sale price.
*   **Dynamic Calculation:** Shows the exact Taka amount and the percentage saved.
*   **Elementor Ready:** Use it anywhere in a Loop Template via a Shortcode widget.
*   **Conditional Rendering:** Only displays if the product is actually on sale.

## Installation

1.  Open your WordPress theme's `functions.php` file (preferably in a Child Theme).
2.  Paste the code from `save-badge-shortcode.php` at the end of the file.
3.  In the Elementor Loop Builder, add a **Shortcode Widget**.
4.  Enter the following shortcode: `[save_amount_badge]`

## Styling
The output is wrapped in a `<span>` or `<div>` with inline styles. You can modify the hex color (`#ed1c24`) or font size directly in the PHP code to match your brand.
