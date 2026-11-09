# Hisab Financial Tracker

A comprehensive WordPress plugin for managing monthly income and expenses with trend analysis, future projections, and Nepali date support.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
- [Category Management](#category-management)
- [Nepali Date Support](#nepali-date-support)
- [Import & Export](#import--export)
- [Frontend Shortcodes](#frontend-shortcodes)
- [Database Structure](#database-structure)
- [Technical Details](#technical-details)
- [Architecture & Code Organization](#architecture--code-organization)
- [Troubleshooting](#troubleshooting)
- [Changelog](#changelog)

---

## Features

### Core Features
- ✅ **Income & Expense Tracking**: Easily add and manage monthly income and expense transactions
- ✅ **Bank Account Management**: Track multiple bank accounts with balances and transactions
- ✅ **Category Management**: Customizable income and expense categories with color coding
- ✅ **Owner/User Tracking**: Assign transactions to different family members or users
- ✅ **Transaction Details**: Add line items with rates, quantities, and totals
- ✅ **Dual Calendar Support**: Both Gregorian (AD) and Nepali (BS) date systems
- ✅ **Import/Export**: JSON-based data portability for backups and migrations

### Analytics & Insights
- 📊 **Visual Charts**: Interactive charts showing income vs expense trends over time
- 📈 **Monthly Summaries**: Quick overview of monthly financial performance
- 🎯 **Category Breakdown**: Detailed analysis of spending patterns by category
- 📉 **Growth Rate Calculation**: Automatic calculation of growth rates for income and expenses

### Projections & Planning
- 🔮 **12-Month Projections**: AI-powered predictions for future income and expenses
- 💰 **Savings Calculator**: Calculate required monthly savings to reach financial goals
- 📅 **Seasonal Adjustments**: Automatic seasonal adjustments based on historical data
- 🎯 **Goal Tracking**: Set and track progress towards financial goals

### Nepali Date Features
- 🇳🇵 **Dual Calendar Display**: View dates in both AD and BS formats
- 🔄 **Accurate Date Conversion**: Using milantarami/nepali-calendar library
- 🛠️ **Date Converter Tool**: Built-in tool for converting between AD and BS dates
- 📝 **BS Date Input**: Enter dates in Nepali calendar format in transaction forms

---

## Installation

### Prerequisites

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- Composer (for Nepali date conversion features)

### Step 1: Install the Plugin

1. Upload the plugin files to `/wp-content/plugins/hisab-financial-tracker/`
2. Activate the plugin through the 'Plugins' menu in WordPress

### Step 2: Install Dependencies (Required for Nepali Date Support)

The plugin uses the [milantarami/nepali-calendar](https://github.com/milantarami/nepali-calendar) library for accurate Nepali date conversion.

#### Manual Installation

1. Open terminal/command prompt
2. Navigate to the plugin directory:
   ```bash
   cd wp-content/plugins/hisab-financial-tracker/
   ```
3. Run Composer install:
   ```bash
   composer install
   ```

### Step 3: Configure Settings

1. Go to **Financial Tracker → Settings**
2. Configure your preferred settings:
   - Currency (NPR/USD)
   - Date format
   - Default calendar (AD/BS)
   - Show dual dates
   - Dashboard preferences

### Step 4: Start Using the Plugin

Access these pages from the WordPress admin menu:

1. **Dashboard**: Overview of current financial status
2. **Add Transaction**: Add new income or expense transactions
3. **Transactions**: View and manage all transactions
4. **Bank Accounts**: Manage bank accounts and balances
5. **Bank Transactions**: Track bank-specific transactions
6. **Analytics**: View detailed financial analysis
7. **Projections**: See future financial projections
8. **Categories**: Manage income and expense categories
9. **Owners**: Manage transaction owners/users
10. **Transfer**: Transfer money between accounts
11. **Tools**: Access Date Converter and other tools
12. **Settings**: Configure plugin settings

---

## Usage

### Managing Transactions

#### Add a Transaction
1. Go to **Financial Tracker → Add Transaction**
2. Fill in the transaction details:
   - Type (Income/Expense)
   - Amount
   - Description
   - Category
   - Owner
   - Payment Method
   - Date (AD or BS format)
   - Transaction Details (optional line items)
3. Click **Save Transaction**

#### View Transactions
1. Go to **Financial Tracker → Transactions**
2. Filter by:
   - Date range
   - Type (Income/Expense)
   - Category
   - Owner
   - Payment method
3. Edit or delete transactions as needed

### Managing Bank Accounts

#### Add a Bank Account
1. Go to **Financial Tracker → Bank Accounts**
2. Click **Add New Bank Account**
3. Enter:
   - Account Name
   - Bank Name
   - Account Number
   - Account Type (Savings/Current/Credit Card/Fixed Deposit/Loan)
   - Currency (NPR/USD)
   - Initial Balance
4. Click **Save Bank Account**

#### Manage Bank Transactions
1. Go to **Financial Tracker → Bank Transactions**
2. Select an account from the dropdown
3. Add deposits, withdrawals, or transfers
4. View transaction history and current balance

### Transfer Between Accounts
1. Go to **Financial Tracker → Transfer Between Accounts**
2. Select source and destination accounts
3. Enter amount and description
4. Choose transfer date
5. Click **Transfer**

---

## Category Management

### Overview
The plugin includes a comprehensive category management system for complete customization of income and expense categories.

### Features

#### ✅ Add Categories
- Create new income or expense categories
- Set custom names and colors
- Real-time validation and feedback

#### ✅ Edit Categories
- Modify existing category names and colors
- Inline editing with form pre-population
- Cancel edit functionality

#### ✅ Delete Categories
- Remove unused categories
- Safety check prevents deletion of categories in use
- Confirmation dialog for safety

### How to Use Categories

1. **Access**: Go to **Financial Tracker → Categories**
2. **Add New**:
   - Fill in name, type (Income/Expense), and color
   - Click **Save Category**
3. **Edit Existing**:
   - Click **Edit** button next to any category
   - Modify details and click **Update Category**
4. **Delete**:
   - Click **Delete** button (only if category is not in use)
   - Confirm deletion

### Default Categories

#### Income Categories
- Salary (#28a745)
- Freelance (#17a2b8)
- Investment (#6f42c1)
- Business (#fd7e14)
- Rental (#f9f10b)
- Other Income (#20c997)

#### Expense Categories
- Food & Dining (#dc3545)
- Transportation (#ffc107)
- Housing (#6c757d)
- Utilities (#007bff)
- Healthcare (#e83e8c)
- Entertainment (#fd7e14)
- Shopping (#20c997)
- Education (#6f42c1)
- Other Expense (#6c757d)

---

## Nepali Date Support

### Features
- Dual calendar system (AD + BS)
- Accurate conversion using milantarami/nepali-calendar library
- Date converter tool
- BS date input in forms
- Automatic date synchronization

### Date Converter Tool

1. Go to **Financial Tracker → Tools → Date Converter**
2. Enter a date in either AD or BS format
3. Click **Convert to BS** or **Convert to AD**
4. View the converted date

### Using Nepali Dates in Transactions

When adding or editing transactions:
1. Enable **Show Dual Dates** in settings
2. Enter date in your preferred format (AD or BS)
3. The system automatically converts and stores both formats
4. View transactions in either calendar format

---

## Import & Export

### Export Data

1. Go to **Financial Tracker → Tools → Import/Export**
2. Select what to export:
   - All Data
   - Categories
   - Owners
   - Bank Accounts
   - Transactions
   - Bank Transactions
3. Click **Export JSON**
4. Save the downloaded JSON file

### Import Data

1. Go to **Financial Tracker → Tools → Import/Export**
2. Click **Choose File** and select your JSON export file
3. Select what to import (categories, owners, accounts, etc.)
4. Choose duplicate handling option:
   - Skip duplicates
   - Update existing records
5. Click **Import Data**
6. Review import results

### Import Features
- Automatic duplicate detection
- Option to update existing records
- Detailed import statistics
- Error reporting for failed imports
- Preserves relationships between data

---

## Frontend Shortcodes

Display financial data on your website using these shortcodes:

### Dashboard
```
[hisab_dashboard]
[hisab_dashboard months="12" show_charts="true" show_recent="true"]
```

### Income Chart
```
[hisab_income_chart months="6" height="400"]
[hisab_income_chart months="12" height="300" show_legend="true"]
```

### Expense Chart
```
[hisab_expense_chart months="6" height="400"]
[hisab_expense_chart months="12" height="300" show_legend="true"]
```

### Monthly Summary
```
[hisab_monthly_summary]
[hisab_monthly_summary year="2024" month="12" show_net="true"]
```

### Shortcode Parameters

- `months`: Number of months to display (default: 6)
- `height`: Chart height in pixels (default: 300)
- `show_charts`: Show/hide charts (true/false)
- `show_recent`: Show/hide recent transactions (true/false)
- `show_legend`: Show/hide chart legend (true/false)
- `year`: Specific year for monthly summary
- `month`: Specific month for monthly summary
- `show_net`: Show net amount in summary (true/false)

---

## Database Structure

### Tables

#### `wp_hisab_transactions`
Stores all income and expense transactions.

**Fields:**
- `id` - Primary key
- `type` - Income or expense
- `amount` - Transaction amount
- `description` - Transaction description
- `category_id` - Foreign key to categories
- `owner_id` - Foreign key to owners
- `payment_method` - Payment method used
- `bank_account_id` - Foreign key to bank accounts
- `phone_pay_reference` - Phone pay reference number
- `bill_image_id` - Attachment ID for bill image
- `transaction_tax` - Tax amount
- `transaction_discount` - Discount amount
- `transaction_date` - Date of transaction
- `bs_year`, `bs_month`, `bs_day` - Nepali date components
- `created_at`, `updated_at` - Timestamps
- `user_id` - WordPress user ID

#### `wp_hisab_categories`
Stores income and expense categories.

**Fields:**
- `id` - Primary key
- `name` - Category name
- `type` - Income or expense
- `color` - Hex color code
- `created_at` - Timestamp

#### `wp_hisab_owners`
Stores transaction owners/users.

**Fields:**
- `id` - Primary key
- `name` - Owner name
- `color` - Hex color code
- `created_at` - Timestamp

#### `wp_hisab_bank_accounts`
Stores bank account information.

**Fields:**
- `id` - Primary key
- `account_name` - Account name
- `bank_name` - Bank name
- `account_number` - Account number
- `account_type` - Account type (savings, current, credit_card, fixed_deposit, loan)
- `currency` - NPR or USD
- `initial_balance` - Starting balance
- `current_balance` - Current balance
- `is_active` - Active status
- `created_at`, `updated_at` - Timestamps
- `user_id` - WordPress user ID

#### `wp_hisab_bank_transactions`
Stores bank-specific transactions.

**Fields:**
- `id` - Primary key
- `account_id` - Foreign key to bank accounts
- `transaction_type` - Deposit, withdrawal, transfer, etc.
- `amount` - Transaction amount
- `currency` - NPR or USD
- `description` - Transaction description
- `reference_number` - Bank reference number
- `phone_pay_reference` - Phone pay reference
- `transaction_date` - Date of transaction
- `bs_year`, `bs_month`, `bs_day` - Nepali date components
- `created_at`, `updated_at` - Timestamps
- `created_by` - WordPress user ID

#### `wp_hisab_transaction_details`
Stores line items for transactions.

**Fields:**
- `id` - Primary key
- `transaction_id` - Foreign key to transactions
- `item_name` - Item name
- `rate` - Unit price
- `quantity` - Quantity
- `item_total` - Total amount
- `created_at` - Timestamp

---

## Technical Details

### Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- Chart.js library (loaded via CDN)
- Composer (for Nepali date features)

### Security Features

- ✅ Nonce verification for all AJAX requests
- ✅ Capability checks for admin functions
- ✅ Data sanitization and validation
- ✅ SQL injection prevention through prepared statements
- ✅ XSS protection
- ✅ CSRF protection

### Performance

- Optimized database queries
- Lazy loading of charts and data
- AJAX-based operations for better UX
- Efficient date conversion caching
- Indexed database columns for fast queries

### Customization

#### Styling
Customize the appearance through CSS files:
- `assets/css/admin.css` - Admin interface styles
- `assets/css/frontend.css` - Frontend display styles

#### Hooks and Filters
```php
// Modify transaction data before saving
add_filter('hisab_before_save_transaction', 'my_custom_transaction_handler');

// Modify chart data before rendering
add_filter('hisab_chart_data', 'my_custom_chart_data');

// Add custom validation
add_filter('hisab_validate_transaction', 'my_custom_validation');
```

---

## Architecture & Code Organization

### File Structure

```
wp-content/plugins/hisab-financial-tracker/
├── hisab-financial-tracker.php       # Main plugin file
├── includes/                          # Core classes
│   ├── class-database.php            # Database operations
│   ├── class-admin.php               # Admin interface logic
│   ├── class-frontend.php            # Frontend display
│   ├── class-analytics.php           # Analytics calculations
│   ├── class-projection.php          # Future projections
│   ├── class-admin-menu.php          # Admin menu management
│   ├── class-shortcodes.php          # Shortcode handlers
│   ├── class-ajax-handlers.php       # AJAX request handlers
│   ├── class-bank-account.php        # Bank account management
│   ├── class-bank-transaction.php    # Bank transactions
│   ├── class-import-export.php       # Import/export functionality
│   └── class-nepali-date.php         # Nepali date conversion
├── admin/                             # Admin pages
│   ├── dashboard.php
│   ├── add-transaction.php
│   ├── transactions.php
│   ├── add-bank-account.php
│   ├── add-bank-transaction.php
│   ├── analytics.php
│   ├── projections.php
│   ├── categories.php
│   ├── owners.php
│   ├── transfer-between-accounts.php
│   ├── date-converter.php
│   ├── import-export.php
│   ├── settings.php
│   └── views/                         # Admin view templates
├── assets/                            # Static assets
│   ├── css/
│   │   ├── admin.css
│   │   └── frontend.css
│   └── js/
│       └── admin.js
├── vendor/                            # Composer dependencies
└── README.md                          # This file
```

### Class Responsibilities

#### Core Classes

**`HisabDatabase`**
- Database table creation and management
- CRUD operations for all entities
- Data retrieval and filtering
- Foreign key relationships

**`HisabAdmin`**
- Admin interface rendering
- Form handling
- Data validation

**`HisabFrontend`**
- Frontend display logic
- Shortcode rendering
- Public-facing features

**`HisabAnalytics`**
- Financial data analysis
- Trend calculations
- Category breakdowns
- Growth rate calculations

**`HisabProjection`**
- Future income/expense predictions
- Seasonal adjustments
- Goal tracking
- Savings calculations

#### Supporting Classes

**`HisabAdminMenu`**
- Admin menu registration
- Page callbacks
- Menu organization

**`HisabShortcodes`**
- Shortcode registration
- Parameter handling
- Frontend rendering

**`HisabAjaxHandlers`**
- AJAX request routing
- Response formatting
- Error handling
- Security checks

**`HisabBankAccount`**
- Bank account CRUD operations
- Balance management
- Account validation

**`HisabBankTransaction`**
- Bank transaction operations
- Balance updates
- Transaction validation

**`HisabImportExport`**
- JSON export generation
- JSON import processing
- Duplicate detection
- Data mapping

**`HisabNepaliDate`**
- AD to BS conversion
- BS to AD conversion
- Date validation
- Calendar utilities

### Benefits of This Architecture

1. **Separation of Concerns**: Each class has a single, well-defined responsibility
2. **Maintainability**: Easy to locate and modify specific functionality
3. **Extensibility**: Simple to add new features without affecting existing code
4. **Testability**: Classes can be tested independently
5. **Reusability**: Common functionality is centralized and reusable

---

## Troubleshooting

### Composer Not Found Error

If you see "Composer not found" error:

1. **Install Composer**: Download from [getcomposer.org](https://getcomposer.org/download/)
2. **Add to PATH**: Make sure Composer is in your system PATH
3. **Verify Installation**: Run `composer --version` to confirm

### Date Conversion Not Working

1. **Check Dependencies**: Ensure `composer install` has been run
2. **Verify Vendor Directory**: Check that `vendor/` directory exists
3. **Test Library**: Use the Date Converter tool to test the library
4. **Check Logs**: Look for errors in WordPress debug log
5. **File Permissions**: Ensure plugin directory is readable

### Import Not Working

1. **Check File Format**: Ensure JSON file is valid
2. **Check File Size**: Large files may exceed PHP limits
3. **View Debug Log**: Check `wp-content/debug.log` for errors
4. **Test Small Import**: Try importing a smaller dataset first

### Permission Issues

1. **Check File Permissions**: Ensure plugin directory is writable (755)
2. **Check User Capabilities**: Ensure user has `manage_options` capability
3. **Run as Admin**: Try running Composer as administrator

### Chart Not Displaying

1. **Check Browser Console**: Look for JavaScript errors
2. **Verify Chart.js**: Ensure Chart.js CDN is accessible
3. **Check Data**: Ensure there is data to display
4. **Clear Cache**: Clear browser and WordPress cache

### Balance Not Updating

1. **Check Transaction Status**: Ensure transaction was saved successfully
2. **Verify Bank Account**: Ensure bank account is active
3. **Check Triggers**: Database triggers should update balances automatically
4. **Manual Recalculation**: Use the recalculate balance tool if available

### Support Resources

For additional support:
1. Check WordPress debug log (`wp-content/debug.log`)
2. Enable `WP_DEBUG` in `wp-config.php`
3. Test individual features in Date Converter and Import/Export pages
4. Verify database tables were created correctly
5. Check file permissions and directory structure

---

## Changelog

### Version 1.3.0 (Remediation & Hardening Milestone)
- 🔒 **Security Hardening**:
  - Protected `wp-content/uploads/hisab-logs/` with `.htaccess` (`Deny from all`) and empty `index.php`.
  - Added action-specific nonce verification and `current_user_can('manage_options')` checks across bank transactions, account management, form submissions, and settings.
  - Restricted unauthenticated access to financial metrics via AJAX.
  - Enforced file upload validation (10MB limit, `.json` extension, MIME validation).
- 🗄️ **Database Layer & Data Integrity**:
  - Fixed persistence of `bank_account_id` and `phone_pay_reference` in `save_transaction()` and `update_transaction()`.
  - Replaced single-record limit in JSON import with a robust 4-tier line-item mapping engine.
  - Automated bank account balance recalculation post-import.
  - Converted bank account form submissions to standard AJAX handlers.
- ⚡ **Performance Optimizations**:
  - Eliminated N+1 query loops in transactions list table using SQL existence subqueries (`has_details`).
  - Optimized bank balance trends from $O(\text{accounts} \times \text{months})$ queries into a single monthly aggregation query.
  - Deduplicated redundant date conversion routines across admin views via centralized `HisabDateConverter`.
- 📊 **Bank Analytics & UI/UX Polish**:
  - Implemented dual-calendar (AD/BS) filtering with native Bikram Sambat months (Baisakh through Chaitra).
  - Harmonized multi-currency handling with segregated NPR (`₹`) and USD (`$`) metrics.
  - Resilient Chart.js visualizations with formatted tooltips and unified dataset generation.
- 🧪 **Automated Verification**:
  - Comprehensive verification suite passing across all 39 plugin PHP files and end-to-end CRUD/transfer workflows.

### Version 1.2.0
- ✅ Added Import/Export functionality (JSON format)
- ✅ Enhanced bank account and transaction management
- ✅ Improved projection algorithms with seasonal adjustments
- ✅ Added transaction detail line items
- ✅ Centralized JavaScript for better maintainability
- ✅ Added owner/user management
- ✅ Implemented transfer between accounts
- ✅ Enhanced date filtering and search
- ✅ Improved UI/UX across all pages

### Version 1.1.0
- ✅ Added Nepali date support (milantarami/nepali-calendar)
- ✅ Implemented dual calendar system (AD + BS)
- ✅ Added date converter tool
- ✅ Enhanced category management with full CRUD
- ✅ Added bank account management
- ✅ Improved analytics and projections
- ✅ Code refactoring for better organization

### Version 1.0.0
- Initial release
- Basic income/expense tracking
- Trend analysis with charts
- Future projections
- Frontend shortcodes
- Admin interface
- Default category management

---

## License

This plugin is licensed under the GPL v2 or later.

---

## Credits

- **Chart.js** - Beautiful, responsive charts
- **milantarami/nepali-calendar** - Accurate Nepali date conversion
- **WordPress** - Amazing platform
- **Community Contributors** - Feedback and suggestions

---

## Future Enhancements

- [ ] Mobile app integration
- [ ] REST API endpoints
- [ ] Multi-currency support enhancements
- [ ] Budget planning features
- [ ] Bill reminders and notifications
- [ ] Receipt OCR scanning
- [ ] Tax calculation and reporting
- [ ] Multi-user support with roles
- [ ] Advanced reporting and PDF exports
- [ ] Integration with banking APIs
