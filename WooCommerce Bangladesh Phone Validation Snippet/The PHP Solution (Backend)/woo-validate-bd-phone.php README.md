The PHP Solution (Backend)
This script runs when the user clicks "Place Order". It silently strips hyphens, spaces, and country codes (+88 or 88) before validating.

How it works:
preg_replace('/[^0-9]/', ...): This regex strips out anything that isn't a number. So +, -, and ( ) are removed instantly.

substr check: We check if the remaining number starts with 88. If it does, we strip it off. This standardizes +88017... and 017... into the same format.

$_POST['billing_phone'] = $clean_phone: This is the crucial step. By updating the global $_POST variable, WooCommerce will save your formatted version (01901367984) into the database instead of the raw input the user typed.
