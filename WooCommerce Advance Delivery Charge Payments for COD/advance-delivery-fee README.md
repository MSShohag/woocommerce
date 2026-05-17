# WooCommerce Advance Delivery Fee for COD

A lightweight, no-plugin PHP snippet for WooCommerce that allows customers to choose between paying the full order amount upfront or paying *only* the delivery fee in advance, with the remaining balance collected as Cash on Delivery (COD). 

This solution is highly effective for minimizing fake orders and return logistics costs for high-value merchandise. 

## ✨ Features

* **Custom Checkout UI:** Replaces standard WooCommerce radio buttons with modern, sleek toggle buttons using inline CSS.
* **Seamless AJAX Updates:** Automatically recalculates the cart total instantly when the user switches between payment preferences without reloading the page.
* **Dynamic Fee Adjustment:** Applies a custom fee calculation that deducts the product subtotal, leaving only the WooCommerce shipping charge to be processed by the payment gateway.
* **Clean Math Display:** Intercepts WooCommerce's default negative fee behavior to remove the confusing minus (`-`) sign from the checkout summary.
* **Order Meta Tracking:** Saves the exact pending balance as a custom meta field (`_cod_due_amount`) attached to the order.
* **Thank You Page Integration:** Displays a highly visible, styled summary box on the "Order Received" page reminding the customer of the exact amount due to the delivery rider.

## 🛠️ Compatibility
* **WooCommerce:** Fully compatible with standard WooCommerce checkout flows.
* **Theme & Builders:** Tested and works seamlessly within the Elementor and Hello Elementor Theme ecosystem. 
* **Local Gateways:** Ideal for use alongside local digital payment gateways (like bKash, Nagad, etc.) where partial payments need to be captured upfront.

## 🚀 Installation & Usage

Because this is a standalone code snippet, you do not need to install it as a plugin. 

1. Copy the code from `advance-delivery-fee.php`.
2. Navigate to your WordPress dashboard.
3. Paste the code into one of the following locations:
   * Your active child theme's `functions.php` file (Recommended: Hello Elementor Child).
   * A code snippet manager like **WPCode** or **Code Snippets** (Set to run everywhere).
4. Ensure you have standard WooCommerce Shipping Zones configured (e.g., Inside City, Outside City) so the script has a delivery fee to isolate.

## ⚠️ Important Accounting Note

By using this custom code to adjust the cart total dynamically, WooCommerce's native analytics and financial dashboard will record the order total as the *advance paid amount* (the shipping fee), not the full product value. This snippet is best used if you manage your primary business accounting and revenue tracking outside of the default WooCommerce reporting dashboard.
