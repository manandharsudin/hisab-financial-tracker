# CLAUDE.md - Hisab Financial Tracker Plugin

## Overview
**Hisab Financial Tracker** (`hisab-financial-tracker`) is a full-featured WordPress plugin for personal and organizational financial management. It provides income and expense tracking, multi-account banking management, itemized transaction line items, predictive analytics (12-month projections), automated logging, JSON import/export, and native dual calendar support with Gregorian (AD) and Bikram Sambat (BS) Nepali calendar systems.

- **Author**: Sudin Manandhar
- **Text Domain**: `hisab-financial-tracker`
- **Main Plugin File**: `hisab-financial-tracker.php`
- **Repository**: `https://github.com/manandharsudin/hisab-financial-tracker.git`
- **License**: GPL v3 or later

---

## Technical Stack & Requirements
- **PHP**: >= 7.4
- **WordPress**: >= 5.0
- **MySQL / MariaDB**: >= 5.6
- **Frontend / Libraries**:
  - jQuery (WordPress core)
  - Chart.js 3.9.1 (loaded via CDN)
  - WordPress Media Uploader (`wp_enqueue_media()`)
- **Composer Dependencies**:
  - `milantarami/nepali-calendar` (^1.4) for BS <-> AD date conversions

---

## Architecture & Directory Structure

```
hisab-financial-tracker/
├── hisab-financial-tracker.php      # Main bootstrap file: constants, lifecycle hooks, enqueues, localized strings
├── composer.json                    # Composer dependencies (milantarami/nepali-calendar)
├── includes/                         # Core business logic classes
│   ├── class-database.php           # Table creation, CRUD queries, aggregations, prepared statements
│   ├── class-admin-menu.php         # Admin menu registration & navigation dispatching
│   ├── class-admin.php              # Controller for rendering admin views
│   ├── class-ajax-handlers.php      # Central AJAX dispatcher with nonce verification
│   ├── class-bank-account.php       # Bank account CRUD and balance management
│   ├── class-bank-transaction.php   # Bank transaction operations and balance mutations
│   ├── class-bank-analytics.php     # Bank trend data, transaction breakdowns, account balances
│   ├── class-analytics.php          # Financial summaries, income/expense trend calculations
│   ├── class-projection.php         # 12-month predictive forecast with growth rate & seasonal models
│   ├── class-nepali-date.php        # Wrapper around NepaliCalendar library with validation
│   ├── class-import-export.php      # JSON export and import with conflict/duplicate resolution
│   ├── class-logger.php             # File-based event logger with automated retention cleanup
│   ├── class-frontend.php           # Frontend rendering logic
│   └── class-shortcodes.php         # Shortcode registrations ([hisab_dashboard], charts, summaries)
├── admin/                           # Admin entry endpoints
│   ├── dashboard.php, add-transaction.php, analytics.php, categories.php, etc.
│   └── views/                       # View templates rendered by HisabAdmin
│       ├── dashboard.php
│       ├── transactions.php
│       ├── add-transaction.php
│       ├── bank-accounts.php
│       ├── add-bank-account.php
│       ├── bank-transactions.php
│       ├── add-bank-transaction.php
│       ├── transfer-between-accounts.php
│       ├── bank-analytics.php
│       ├── analytics.php
│       ├── projections.php
│       ├── categories.php
│       ├── owners.php
│       ├── date-converter.php
│       ├── import-export.php
│       └── settings.php
├── assets/
│   ├── css/
│   │   ├── admin.css                # Admin UI styling & responsive layouts
│   │   └── frontend.css             # Public shortcode styles
│   └── js/
│       ├── admin.js                 # Unified admin JS: AJAX caller, line items, modals, Chart.js
│       └── frontend.js              # Frontend interactive charts and widgets
└── vendor/                          # Composer autoloader and vendor packages
```

---

## Database Architecture

The plugin manages 6 custom tables via `HisabDatabase::create_tables()`:

1. **`wp_hisab_transactions`**:
   - Primary table for income and expense transactions.
   - Fields: `id`, `type` (`income`|`expense`), `amount`, `description`, `category_id`, `owner_id`, `payment_method`, `bank_account_id`, `phone_pay_reference`, `bill_image_id`, `transaction_tax`, `transaction_discount`, `transaction_date` (AD `date`), `bs_year`, `bs_month`, `bs_day`, `created_at`, `updated_at`, `user_id`.
   - Indexed on `type`, `transaction_date`, composite `bs_date (bs_year, bs_month, bs_day)`, `category_id`, `owner_id`, `payment_method`, `user_id`.

2. **`wp_hisab_transaction_details`**:
   - Itemized line items linked to transactions.
   - Fields: `id`, `transaction_id`, `item_name`, `rate`, `quantity`, `item_total`, `created_at`.

3. **`wp_hisab_categories`**:
   - Income/expense categories with custom hex color codes.
   - Fields: `id`, `name`, `type` (`income`|`expense`), `color`, `created_at`.

4. **`wp_hisab_owners`**:
   - Transaction owners / family members / departments.
   - Fields: `id`, `name` (unique), `color`, `created_at`.

5. **`wp_hisab_bank_accounts`**:
   - Multi-account banking support.
   - Fields: `id`, `account_name`, `bank_name`, `account_number`, `account_type` (`savings`, `current`, `credit_card`, `fixed_deposit`, `loan`), `currency` (`NPR`, `USD`), `initial_balance`, `current_balance`, `is_active`, `created_at`, `updated_at`, `user_id`.

6. **`wp_hisab_bank_transactions`**:
   - Bank-specific transactions and transfers.
   - Fields: `id`, `account_id`, `transaction_type` (`deposit`, `withdrawal`, `transfer_in`, `transfer_out`, `phone_pay`), `amount`, `currency`, `description`, `reference_number`, `phone_pay_reference`, `transaction_date`, `bs_year`, `bs_month`, `bs_day`, `created_at`, `updated_at`, `created_by`.

---

## Core Conventions & Patterns

### 1. Constants
- Defined in `hisab-financial-tracker.php`:
  - `HISAB_VERSION`: Current version string (`1.0.0`)
  - `HISAB_CURRENCY_SYMBOL`: Default currency symbol (`₹`)
  - `HISAB_DATE_FORMAT`: Default date format (`M j, Y`)
  - `HISAB_BS_START_YEAR`: 2062 (BS year range start)
  - `HISAB_BS_END_YEAR`: 2085 (BS year range end)
  *Always use `HISAB_BS_START_YEAR` and `HISAB_BS_END_YEAR` constants instead of hardcoded numbers.*

### 2. Dual Calendar Synchronization
- Every transaction record stores both Gregorian `transaction_date` (YYYY-MM-DD) and Nepali calendar date components (`bs_year`, `bs_month`, `bs_day`).
- Handled via `HisabNepaliDate::ad_to_bs()` and `HisabNepaliDate::bs_to_ad()`.
- Month names in BS: Baisakh (1) through Chaitra (12).

### 3. AJAX Architecture
- All AJAX actions are registered in `HisabAjaxHandlers`.
- Nonce verification:
  ```php
  check_ajax_referer('hisab_transaction', 'nonce');
  ```
- Permission check:
  ```php
  if (!current_user_can('manage_options')) {
      wp_send_json_error(array('message' => __('Unauthorized', 'hisab-financial-tracker')));
  }
  ```
- JS AJAX Dispatcher in `admin.js`:
  ```javascript
  makeAjaxCall(action, data, successCallback, errorCallback);
  ```
- Strings are localized in `HisabFinancialTracker::get_localized_strings()` and passed via `hisab_ajax`.

### 4. Logging & Audit Trail
- Managed by `HisabLogger`.
- Stored under `wp-content/uploads/hisab-logs/hisab-YYYY-MM-DD.log`.
- Configurable per action type in Settings (`TRANSACTION_CREATE`, `BANK_TRANSACTION_CREATE`, `SETTINGS_UPDATE`, etc.).
- Automated cleanup runs daily based on `hisab_log_retention_days` option (default: 30 days).

### 5. Shortcodes
- `[hisab_dashboard months="6" show_charts="true" show_recent="true"]`
- `[hisab_income_chart months="6" height="300"]`
- `[hisab_expense_chart months="6" height="300"]`
- `[hisab_monthly_summary year="2024" month="12" show_net="true"]`

---

## Verification & Development Rules
- **Syntax Check**: Run `php -l <file>` on any modified or newly added PHP file.
- **Dependencies**: Run `composer install` inside `wp-content/plugins/hisab-financial-tracker` if vendor dependencies are updated.
- **Git Safety**:
  - Never run `git commit`, `git push`, or `git merge` without explicit user instruction.
  - Do not append `Co-Authored-By` or AI attribution trailers to commit messages.
- **Current Status / In Progress**:
  - Bank Analytics (`admin/views/bank-analytics.php` and `includes/class-bank-analytics.php`) is actively being worked on (refactoring filters, trend charts, and CSS consolidation).
