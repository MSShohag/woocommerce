# WC Abandoned Cart to Google Sheets

A zero-database abandoned cart logging solution. Captures customer email addresses and phone numbers on checkout field blur and forwards them immediately to Google Sheets via Google Apps Script.

## Features
- **Zero Database Load:** Absolutely nothing is written to `wp_options`, `wp_posts`, or transients.
- **Non-Blocking Execution:** Uses `wp_remote_post(..., array('blocking' => false))`. WordPress drops the packet and continues without waiting for Google's HTTP response.
- **Captures Both Channels:** Collects customer email addresses and phone numbers for email marketing, SMS, or WhatsApp follow-up.

---

## Setup Instructions

### 1. Configure the Google Sheet & Apps Script
1. Open [Google Sheets](https://sheets.new) and create a new sheet.
2. In the top menu, go to **Extensions > Apps Script**.
3. Replace all contents in the code editor with the code from `Code.gs`.
4. In the function dropdown at the top, select `setupSheetHeaders` and click **Run**. Grant any permissions requested. (This formats Row 1 with clean column titles).
5. Click **Deploy > New deployment**.
6. Click the gear icon next to "Select type" and choose **Web app**.
7. Configure the settings:
   - **Description:** `WooCommerce Cart Webhook`
   - **Execute as:** `Me`
   - **Who has access:** `Anyone` *(Note: Must be "Anyone" so your website can post data without complex Google OAuth tokens)*.
8. Click **Deploy** and copy the **Web app URL** (`https://script.google.com/macros/s/.../exec`).

### 2. Configure the WordPress Plugin
1. Open `wc-abandoned-cart-sheets.php`.
2. Locate line 19:
   ```php
   define( 'WC_SHEETS_WEBHOOK_URL', 'YOUR_GOOGLE_APPS_SCRIPT_WEB_APP_URL_HERE' );
Replace YOUR_GOOGLE_APPS_SCRIPT_WEB_APP_URL_HERE with your copied Web App URL.
3. Save the file.

3. Install & Activate
Compress wc-abandoned-cart-sheets.php into a .zip archive or copy the folder into wp-content/plugins/.

Activate the plugin via Plugins > Installed Plugins in your WordPress dashboard.
