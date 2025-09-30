# Hisab Financial Tracker - Installation Guide

## Prerequisites

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Composer (for Nepali date conversion features)

## Installation Steps

### 1. Install the Plugin

1. Upload the plugin files to `/wp-content/plugins/hisab-financial-tracker/`
2. Activate the plugin through the 'Plugins' menu in WordPress

### 2. Install Dependencies (Required for Nepali Date Support)

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

### 3. Configure Settings

1. Go to **Financial Tracker → Settings**
2. Configure your preferred settings:
   - Currency
   - Date format
   - Default calendar (AD/BS)
   - Show dual dates

### 4. Start Using the Plugin

1. **Add Transactions**: Go to **Financial Tracker → Add Transaction**
2. **View Dashboard**: Go to **Financial Tracker → Dashboard**
3. **View Analytics**: Go to **Financial Tracker → Analytics**
4. **View Projections**: Go to **Financial Tracker → Projections**
5. **Manage Categories**: Go to **Financial Tracker → Categories**
6. **Convert Dates**: Go to **Financial Tracker → Date Converter**
7. **Configure Settings**: Go to **Financial Tracker → Settings**

## Features

### Core Features
- ✅ Monthly income and expense tracking
- ✅ Financial trend analysis
- ✅ Future financial projections
- ✅ Category management

### Nepali Date Features
- ✅ Dual calendar support (AD + BS)
- ✅ Accurate date conversion using milantarami/nepali-calendar
- ✅ Date converter tool
- ✅ BS date input in transaction forms
- ✅ Dual date display in dashboard

## Troubleshooting

### Composer Not Found Error

If you see "Composer not found" error:

1. **Install Composer**: Download from [getcomposer.org](https://getcomposer.org/download/)
2. **Add to PATH**: Make sure Composer is in your system PATH
3. **Verify Installation**: Run `composer --version` to confirm it's working

### Date Conversion Not Working

1. **Check Dependencies**: Ensure Composer dependencies are installed
2. **Check Library**: Go to Date Converter page and test the library
3. **Check Logs**: Look for errors in WordPress debug log
4. **Verify Installation**: Make sure `vendor/` directory exists in plugin folder

### Permission Issues

If you get permission errors during installation:

1. **Check File Permissions**: Ensure the plugin directory is writable
2. **Run as Admin**: Try running Composer commands as administrator
3. **Check Composer**: Verify Composer is properly installed and accessible

## Support

For issues and support:
1. Check the WordPress debug log
2. Test the Date Converter page
3. Verify Composer installation
4. Check file permissions
5. Ensure `vendor/` directory exists after running `composer install`

## Changelog

### Version 1.0.0
- Initial release with basic financial tracking
- Added Nepali date support using milantarami/nepali-calendar
- Implemented dual calendar system
- Added date converter tool
- Enhanced category management
