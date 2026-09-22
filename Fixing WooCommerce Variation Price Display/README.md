/**
 * Title: WooCommerce Force Strikethrough Regular Price for Single Variations
 * Description: Fixes WooCommerce suppressing the strikethrough <del> regular price
 *              and emptying the variation `price_html` container when only one 
 *              variation is priced or when a variation matches the parent product price.
 * Author: Shahrear Shohag
 * Version: 1.0.0
 * Compatibility: WooCommerce 7.0+
 */

# WooCommerce Force Strikethrough Regular Price for Single Variations

A lightweight PHP snippet for WooCommerce that ensures sale and regular strikethrough prices (`<del>Regular</del> <ins>Sale</ins>`) are properly displayed when a variable product only has one priced variation or identical variation prices.

## Problem
By default, WooCommerce suppresses the `<del>` strikethrough tag and clears `price_html` when:
- A variable product has only one priced variation (e.g., other variations are set to "Call for Price" or empty).
- The variation price is identical to the parent product's displayed price.

## Solution
This snippet:
1. Intercepts `woocommerce_variable_price_html` to format the header price with `<del>` and `<ins>` tags.
2. Enables `woocommerce_show_variation_price` so variation price wrappers remain active.
3. Injects custom `<del>` and `<ins>` markup into the `woocommerce_available_variation` data payload.

## Installation
Add the code to your child theme's `functions.php` file or use a code snippet manager plugin (e.g., Code Snippets, WPCode).

> **Note:** After applying, clear WooCommerce transients via **WooCommerce > Status > Tools > Clear transients**, as variation prices are cached in the database.

What Problem This Code Solves
In WooCommerce, when a variable product has only one active/priced variation (or all priced variations share the same price), two default behaviors cause regular price and discount strikethroughs to disappear:

Suppressed Header Discount: WooCommerce calculates the top product price as a single fixed amount ($398,500) instead of a range, often omitting the strikethrough regular price (<del>$450,000</del>).

Empty Variation Price HTML: When a customer selects that variation, WooCommerce’s frontend script (add-to-cart-variation.js) compares the variation price to the header price. Because they match, WooCommerce suppresses the variation price container ("price_html": ""), leaving the user with no visual cue of the regular price or discount.

This code hooks into WooCommerce filters to restore the <del>Regular</del> <ins>Sale</ins> layout both in the main header and inside the selected variation block.

Step-by-Step Code Explanation
1. Header Price Filter (woocommerce_variable_price_html)

get_variation_prices( true ): Fetches an array of all active variation regular and sale prices.

$min_price === $max_price: Checks if there is only a single active price point (either only one variation exists/is priced, or all priced variations are identical).

$product->is_on_sale(): Confirms the item is discounted.

Return Value: Replaces the default single price output with standard WooCommerce markup: "<del> "for the regular price and "<ins>" for the discounted sale price.

2. Always Show Variation Price (woocommerce_show_variation_price)
   Forces WooCommerce to treat variation prices as visible, bypassing the default check that hides them when all variation prices match.

3. Variation Data JSON Filter (woocommerce_available_variation)
   Injects formatted HTML ("<del> "regular + "<ins>" sale) directly into the JavaScript variation payload (data-product_variations JSON attribute).

When a user clicks variation swatches or dropdowns, the price block dynamically renders the strikethrough regular price and the active sale price.
