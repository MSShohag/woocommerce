# WooCommerce Zero-Bloat Abandoned Cart Recovery

High-performance, lightweight abandoned cart capture engines built specifically for the native WooCommerce checkout. Operates without funnel plugins, SaaS subscriptions, or front-end JavaScript bloat, capturing both **email addresses** and **phone numbers** as soon as shoppers type them.

---

## Architecture Comparison

| Feature | 1. Local Transient Version | 2. Google Sheets Version |
| :--- | :--- | :--- |
| **Storage Destination** | WordPress Transients (`wp_options`) | Google Sheets via Apps Script Webhook |
| **Recovery Channel** | Automated native `wp_mail()` cron | Manual, Zapier, Make, or SMS/WhatsApp |
| **Database Overhead** | Minimal (auto-expiring temporary rows) | **Zero database writes** |
| **Server CPU Load** | Low (hourly batch query) | **Near-zero** (Non-blocking async HTTP POST) |
| **Third-Party Dependency** | None | Google Apps Script (Free) |
| **Best For** | Stores wanting fully automated email recovery inside WP | High-traffic sites, WhatsApp/SMS outreach, shared hosting |

---

## Repository Structure

- `1-local-transient-version/`: Self-hosted system using WordPress transients, native hooks, and a background WP-Cron scheduler.
- `2-google-sheets-version/`: Zero-DB approach sending live leads to Google Sheets via non-blocking asynchronous requests.

See the `README.md` inside each subfolder for detailed installation steps.
