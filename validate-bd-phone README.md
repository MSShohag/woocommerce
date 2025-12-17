# WooCommerce Bangladesh Phone Validation Snippet

A lightweight PHP snippet to enforce valid Bangladesh mobile number formats on the WooCommerce checkout page.

## 🚀 Features
* **Format Enforcement:** Ensures the number starts with `01`.
* **Length Check:** Validates for exactly 11 digits (standard BD mobile length).
* **International Support:** Handles formats with or without the `+88` or `88` prefix.
* **Error Messaging:** Displays a clear notice to the customer if the input is invalid.

## 🛠️ How to Install

### Option A: Using functions.php (Direct)
1. Log in to your WordPress Dashboard.
2. Navigate to **Appearance > Theme File Editor**.
3. Open the `functions.php` file of your **child theme**.
4. Paste the code from `validate-bd-phone.php` at the bottom of the file.
5. Click **Update File**.

### Option B: Using Code Snippets Plugin (Recommended)
1. Install the [Code Snippets](https://wordpress.org/plugins/code-snippets/) plugin.
2. Click **Add New**.
3. Title it: "BD Phone Validation".
4. Paste the code and select **Run snippet everywhere**.
5. Save and Activate.

## 📝 Validation Logic
The code uses a Regular Expression (Regex) to check for:
- Optional `+88` or `88`.
- Required `01`.
- A third digit between `3-9` (covering Grameenphone, Robi, Banglalink, Airtel, and Teletalk).
- Exactly 8 more digits following.

## 📄 License
This snippet is open-source and free to use for personal or commercial projects.
