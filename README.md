# Hisab Financial Tracker

A comprehensive WordPress plugin for managing monthly income and expenses with trend analysis and future projections.

## Features

### 1. Data Entry
- **Income & Expense Tracking**: Easily add and manage monthly income and expense transactions
- **Category Management**: Pre-defined categories for both income and expenses with color coding
- **Transaction Details**: Add descriptions, amounts, dates, and categories for each transaction
- **Bulk Operations**: Support for adding multiple transactions quickly

### 2. Trend Analysis
- **Visual Charts**: Interactive charts showing income vs expense trends over time
- **Monthly Summaries**: Quick overview of monthly financial performance
- **Category Breakdown**: Detailed analysis of spending patterns by category
- **Growth Rate Calculation**: Automatic calculation of growth rates for income and expenses

### 3. Future Projections
- **12-Month Projections**: AI-powered predictions for future income and expenses
- **Savings Calculator**: Calculate required monthly savings to reach financial goals
- **Seasonal Adjustments**: Automatic seasonal adjustments based on historical data
- **Goal Tracking**: Set and track progress towards financial goals

## Installation

1. Upload the plugin folder to `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Financial Tracker' in the admin menu to start using the plugin

## Usage

### Admin Interface

The plugin adds a new menu item "Financial Tracker" to your WordPress admin with the following pages:

- **Dashboard**: Overview of current month's financial status and recent transactions
- **Add Transaction**: Form to add new income or expense transactions
- **Analytics**: Detailed analysis with charts and category breakdowns
- **Projections**: Future financial projections and savings calculator
- **Settings**: Configure currency, date format, and other options

### Frontend Shortcodes

Use these shortcodes to display financial data on your website:

```
[hisab_dashboard]
```
Displays a summary dashboard with current month's data and recent transactions.

```
[hisab_income_chart months="6" height="300"]
```
Shows an income trend chart for the specified number of months.

```
[hisab_expense_chart months="6" height="300"]
```
Shows an expense trend chart for the specified number of months.

### Shortcode Parameters

- `months`: Number of months to display (default: 6)
- `height`: Chart height in pixels (default: 300)
- `show_charts`: Show/hide charts (true/false)
- `show_recent`: Show/hide recent transactions (true/false)

## Database Structure

The plugin creates two main tables:

### `wp_hisab_transactions`
- Stores all income and expense transactions
- Fields: id, type, amount, description, category_id, transaction_date, created_at, updated_at, user_id

### `wp_hisab_categories`
- Stores income and expense categories
- Fields: id, name, type, color, created_at

## Default Categories

### Income Categories
- Salary
- Freelance
- Investment
- Business
- Other Income

### Expense Categories
- Food & Dining
- Transportation
- Housing
- Utilities
- Healthcare
- Entertainment
- Shopping
- Education
- Other Expense

## Technical Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- Chart.js library (loaded via CDN)

## Security Features

- Nonce verification for all AJAX requests
- Capability checks for admin functions
- Data sanitization and validation
- SQL injection prevention through prepared statements

## Customization

### Styling
The plugin includes CSS files that can be customized:
- `assets/css/admin.css` - Admin interface styles
- `assets/css/frontend.css` - Frontend display styles

### Hooks and Filters
The plugin provides several hooks for customization:

```php
// Modify transaction data before saving
add_filter('hisab_before_save_transaction', 'my_custom_transaction_handler');

// Modify chart data before rendering
add_filter('hisab_chart_data', 'my_custom_chart_data');

// Add custom validation
add_filter('hisab_validate_transaction', 'my_custom_validation');
```

## Support

For support and feature requests, please contact the plugin developer or create an issue in the plugin repository.

## Changelog

### Version 1.0.0
- Initial release
- Basic income/expense tracking
- Trend analysis with charts
- Future projections
- Frontend shortcodes
- Admin interface
- Category management

## License

This plugin is licensed under the GPL v2 or later.

## Credits

- Chart.js for beautiful charts
- WordPress for the amazing platform
- Community contributors for feedback and suggestions
