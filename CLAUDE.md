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
  - Phase 1 (Critical Security Hardening & Bug Fixes) COMPLETED.
  - Phase 2 (Database Layer & Data Integrity Consolidation) COMPLETED.
  - Phase 3 (Performance Optimization & Query Consolidation) COMPLETED.
  - Next: Phase 4 (Complete Bank Analytics & Polish UI/UX).

---

## Codebase Audit & Architectural Review (Nov 2026)

### 1. Critical Security Vulnerabilities Identified
1. **Public Audit Log Exposure**: [FIXED in Phase 1] Added `.htaccess` (`Deny from all`) and empty `index.php` protection in `HisabLogger` and plugin activation.
2. **CSRF in Bank Operations**: [FIXED in Phase 1] Added action-specific nonce verification and `current_user_can('manage_options')` checks to bank transactions/accounts deletion and status toggles.
3. **Missing Capability Checks**: [FIXED in Phase 1] Added `current_user_can('manage_options')` checks to `HisabAdminMenu::handle_bank_account_submission()`, `handle_bank_transaction_submission()`, and `HisabAdmin::save_settings()`.
4. **Unauthenticated Financial Metrics Leak**: [FIXED in Phase 1] Restricted `ajax_get_public_data` to authenticated administrators with valid nonce.
5. **Unvalidated File Upload in JSON Import**: [FIXED in Phase 1] Added upload source check, 10MB size limit, `.json` extension check, and MIME type validation in `ajax_import_data()`.

### 2. Bugs & Data Integrity Flaws
1. **Frontend Chart Crash (Duplicate `uniqid()`)**: [FIXED in Phase 1] Unified canvas element ID and JavaScript variable in `HisabFrontend::render_income_chart()` and `render_expense_chart()`.
2. **Corrupted Transaction Detail Mapping in JSON Import**: [FIXED in Phase 2] Multi-tiered mapping strategy (session ID map, signature map, date-verified ID map) replacing `LIMIT 1` query in `HisabImportExport::find_transaction_id()`.
3. **Stale Bank Balances Post-Import**: [FIXED in Phase 2] Automatic bank balance recalculation in `HisabImportExport::import_bank_transactions()` and `import_from_json()`.
4. **Field Omission in `save_transaction()`**: [FIXED in Phase 2] `HisabDatabase::save_transaction()` and `update_transaction()` now persist `bank_account_id` and `phone_pay_reference`.

### 3. Performance Bottlenecks
1. **N+1 Queries in Transactions List**: `admin/views/transactions.php` queries details individually for every transaction row inside the loop (`new HisabDatabase()->get_transaction_details($t->id)`).
2. **Nested Loop Query Explosion in Bank Balance Trends**: `HisabBankAnalytics::get_account_balance_trends()` runs `O(accounts * months)` individual SQL queries.
3. **Monolithic Asset**: `assets/js/admin.js` is 117KB (2,808 lines) containing redundant date conversion routines and event handlers.

---

## Phased Remediation Roadmap

When resuming work, follow these sequential phases:

* **Phase 1: Critical Security Hardening & Bug Fixes [COMPLETED]**
  - [x] Add `.htaccess` (`Deny from all`) and empty `index.php` to `wp-content/uploads/hisab-logs/`.
  - [x] Add nonces and capability checks to `bank-transactions.php`, `bank-accounts.php`, `handle_bank_account_submission()`, and `save_settings()`.
  - [x] Fix frontend chart canvas IDs in `includes/class-frontend.php`.
  - [x] Restrict `wp_ajax_nopriv_hisab_get_public_data`.
  - [x] Add file upload MIME/size validation to JSON import.

* **Phase 2: Database Layer & Data Integrity Consolidation [COMPLETED]**
  - [x] Fix `HisabDatabase::save_transaction()` and `update_transaction()` to accept and persist `bank_account_id` and `phone_pay_reference`.
  - [x] Fix `HisabImportExport::find_transaction_id()` line-item mapping logic.
  - [x] Automatically recalculate bank account balances after JSON import.
  - [x] Retire synchronous form processing in `class-admin-menu.php` in favor of standard AJAX handlers (`hisab_save_bank_account`).

* **Phase 3: Performance Optimization & Query Consolidation [COMPLETED]**
  - [x] Eliminate the N+1 query loop in `admin/views/transactions.php` (e.g. `has_details` flag in paginated query).
  - [x] Flatten nested query loop in `HisabBankAnalytics::get_account_balance_trends()` into a single monthly aggregation query.
  - [x] Deduplicate redundant conversion routines in `assets/js/admin.js` via `HisabDateConverter`.

* **Phase 4: Complete Bank Analytics & Polish UI/UX**
  - Finish filter controls and dual-calendar (AD/BS) switching in `admin/views/bank-analytics.php`.
  - Verify and polish Chart.js balance and flow trends.
  - Harmonize multi-currency display (NPR / USD).

* **Phase 5: Automated Verification & Documentation**
  - Run `php -l` on all PHP files.
  - Conduct end-to-end browser verification of forms, transfers, and charts.
  - Update `README.md` and user documentation.
