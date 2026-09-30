# WooCommerce Abandoned Cart Recovery (Bangladesh Edition)

A high-performance, zero-bloat abandoned cart recovery system designed for Bangladeshi e-commerce stores running **Cash on Delivery (COD)** and simplified checkouts (such as WooLentor or custom Elementor checkout layouts).

---

## Key Features

1. **Strict Bangladesh Number Validation:**
   - Automatically sanitizes and reformats numbers on input blur (e.g., `88017...` or `017...` become `01XXXXXXXXX`).
   - Validates Bangladesh mobile prefixes (`013`, `014`, `015`, `016`, `017`, `018`, `019`) and ensures exactly 11 digits.
2. **Exact 1-Click Cart Restoration:**
   - Encodes simple and **variable products** (along with attributes like Color, Size, etc.) into a URL-safe Base64 payload.
   - Restores the exact product variation even when opened in a new browser, session, or mobile device.
3. **Instant WhatsApp Recovery:**
   - Pre-fills a personalized Bengali recovery message:
     > *আসসালামু আলাইকুম [Name], আপনার সিলেক্ট করা প্রোডাক্টগুলো কার্টে সংরক্ষিত রাখা হয়েছে। অর্ডারটি কনফার্ম করতে নিচের লিংকে ক্লিক করুন: [Link]*
   - Automatically appends Bangladesh's country code (`88`) for WhatsApp URLs.
4. **Zero Database Bloat & Instant Auto-Cleanup:**
   - Uses temporary WordPress transients expiring after 3 days.
   - Clears cart records **the exact millisecond** an order is completed via `woocommerce_checkout_order_processed` and `woocommerce_thankyou`.

---

## Installation

### Method A: As a Standalone Plugin (Recommended)
1. Download or copy the `1-bangladesh-store` folder into your site's `wp-content/plugins/` directory.
2. Navigate to **Plugins > Installed Plugins** in the WordPress admin.
3. Click **Activate** under **WC Abandoned Cart Recovery (Bangladesh Edition)**.

### Method B: In `functions.php`
1. Open your child theme's `functions.php` file.
2. Copy the contents of `wc-abandoned-cart-bd.php` (omit the opening `<?php` tag if one already exists).
3. Save the file.

---

## How to Test

1. Visit your store in an Incognito / Private window.
2. Add a variable product (e.g., select Color or Size) to the cart.
3. On Checkout, type an 11-digit number starting with `017...` in the **মোবাইল নম্বর** field.
4. Type your name in the **নাম** field and click into another field.
5. In your WordPress admin dashboard, navigate to **WooCommerce > Abandoned Carts**.
6. Verify that the entry appears with the phone number, name, linked item title, and total.
7. Click the **WhatsApp** or **Copy URL** button to verify the 1-click cart restoration.
8. Place the order: confirm that the record is immediately removed from the Abandoned Carts list.

## Adaptive Field Detection

This engine automatically adapts to any checkout field layout:
- **Phone-Only Checkouts (e.g. COD / Quick Checkout):** Captures the phone number on blur. The absence of an email field will not trigger errors.
- **Email-Only Checkouts (e.g. Digital Goods):** Captures the email on blur.
- **Combined Checkouts (Both Phone & Email):** Captures whichever field is filled first. When the second field is filled, the background process matches the existing record and merges them into a single row without creating duplicate entries.
- **Order Cleanup:** When an order is completed, the system clears the transient if either the order phone or email matches the saved cart.
