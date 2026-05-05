WooCommerce Quick-Action Buttons
A lightweight, plugin-free PHP and CSS snippet to add a Buy Now button (with Variation support) and social contact buttons (WhatsApp & Messenger) directly to the WooCommerce product page.

Features
Direct "Buy Now": Clears the previous cart and redirects the user straight to the checkout page for the current item.

Variation Support: Dynamically updates the "Buy Now" link when a user selects different product attributes (size, color, etc.).

Social Integration: One-click contact buttons for WhatsApp and Facebook Messenger.

Pill-Shaped Design: Modern, 50px border-radius aesthetic as seen in recent UI designs.

Responsive Layout: Optimized 2x2 grid for mobile and desktop screens.

Installation
Open your child theme's functions.php file or use a plugin like Code Snippets.

Copy the code from wc-custom-product-buttons.php.

Replace the placeholder values for $phone_number and $fb_page_id with your actual credentials.

(Optional) If icons do not appear, ensure your theme loads Font Awesome 6.

Technical Details
Hook used: woocommerce_after_add_to_cart_button.

Styling: High-specificity CSS to ensure compatibility with standard WooCommerce layouts.

Height: Fixed at 42px to ensure a sleek profile and visibility of the quantity input.

Preview
The layout organizes the buttons into two logical rows:

Row 1: Quantity Selector | Add to Cart | Buy Now.

Row 2: Messenger | WhatsApp.
