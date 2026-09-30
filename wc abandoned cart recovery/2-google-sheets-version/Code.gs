---

### Folder 2: `2-google-sheets-version/`

#### `2-google-sheets-version/Code.gs`

```javascript
/**
 * Google Apps Script: WooCommerce Abandoned Cart Webhook Receiver
 *
 * Receives JSON payloads from WooCommerce checkout and appends
 * timestamp, email, phone number, cart total, items, and recovery link.
 */

function doPost(e) {
  var lock = LockService.getScriptLock();
  // Wait up to 10 seconds for concurrent requests to prevent overwrite
  lock.tryLock(10000);

  try {
    var sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();

    // Parse the incoming JSON body
    var data = JSON.parse(e.postData.contents);

    var timestamp    = new Date().toISOString();
    var email        = data.email || '';
    var phone        = data.phone || '';
    var items        = data.items || '';
    var total        = data.total || '';
    var recoveryUrl  = data.recovery_url || '';

    // Append to sheet
    sheet.appendRow([
      timestamp,
      email,
      phone,
      total,
      items,
      recoveryUrl
    ]);

    return ContentService.createTextOutput(
      JSON.stringify({ status: "success", message: "Row added" })
    ).setMimeType(ContentService.MimeType.JSON);

  } catch (error) {
    return ContentService.createTextOutput(
      JSON.stringify({ status: "error", message: error.toString() })
    ).setMimeType(ContentService.MimeType.JSON);

  } finally {
    lock.releaseLock();
  }
}

/**
 * Run this function once manually inside the Apps Script editor
 * to initialize header columns in Row 1.
 */
function setupSheetHeaders() {
  var sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
  var headers = ["Timestamp (UTC)", "Email", "Phone", "Cart Total", "Items", "Recovery URL"];
  sheet.getRange(1, 1, 1, headers.length).setValues([headers]);
  sheet.setFrozenRows(1);
}
