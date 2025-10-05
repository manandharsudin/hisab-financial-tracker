<?php
/**
 * Admin Menu management class for Hisab Financial Tracker
 */

if (!defined('ABSPATH')) {
    exit;
}

class HisabAdminMenu {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    public function add_admin_menu() {
        // Main menu page
        add_menu_page(
            __('Financial Tracker', 'hisab-financial-tracker'),
            __('Financial Tracker', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-dashboard',
            array($this, 'admin_dashboard_page'),
            'dashicons-chart-line',
            30
        );
        
        // Dashboard submenu
        add_submenu_page(
            'hisab-dashboard',
            __('Dashboard', 'hisab-financial-tracker'),
            __('Dashboard', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-dashboard',
            array($this, 'admin_dashboard_page')
        );
        
        // All Transactions submenu
        add_submenu_page(
            'hisab-dashboard',
            __('All Transactions', 'hisab-financial-tracker'),
            __('All Transactions', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-transactions',
            array($this, 'admin_transactions_page')
        );
        
        // Add Transaction submenu
        add_submenu_page(
            'hisab-dashboard',
            __('Add Transaction', 'hisab-financial-tracker'),
            __('Add Transaction', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-add-transaction',
            array($this, 'admin_add_transaction_page')
        );
        
        // Bank Accounts submenu
        add_submenu_page(
            'hisab-dashboard',
            __('Bank Accounts', 'hisab-financial-tracker'),
            __('Bank Accounts', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-bank-accounts',
            array($this, 'admin_bank_accounts_page')
        );
        
        // Add Bank Account submenu
        add_submenu_page(
            'hisab-dashboard',
            __('Add Bank Account', 'hisab-financial-tracker'),
            __('Add Bank Account', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-add-bank-account',
            array($this, 'admin_add_bank_account_page')
        );
        
        // Bank Transactions submenu
        add_submenu_page(
            'hisab-dashboard',
            __('Bank Transactions', 'hisab-financial-tracker'),
            __('Bank Transactions', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-bank-transactions',
            array($this, 'admin_bank_transactions_page')
        );
        
        // Add Bank Transaction submenu
        add_submenu_page(
            'hisab-dashboard',
            __('Add Bank Transaction', 'hisab-financial-tracker'),
            __('Add Bank Transaction', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-add-bank-transaction',
            array($this, 'admin_add_bank_transaction_page')
        );
        
        // Transfer Between Accounts submenu
        add_submenu_page(
            'hisab-dashboard',
            __('Transfer Between Accounts', 'hisab-financial-tracker'),
            __('Transfer Between Accounts', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-transfer-accounts',
            array($this, 'admin_transfer_accounts_page')
        );
        
        // Analytics submenu
        add_submenu_page(
            'hisab-dashboard',
            __('Analytics', 'hisab-financial-tracker'),
            __('Analytics', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-analytics',
            array($this, 'admin_analytics_page')
        );
        
        // Projections submenu
        add_submenu_page(
            'hisab-dashboard',
            __('Projections', 'hisab-financial-tracker'),
            __('Projections', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-projections',
            array($this, 'admin_projections_page')
        );
        
        // Categories submenu
        add_submenu_page(
            'hisab-dashboard',
            __('Categories', 'hisab-financial-tracker'),
            __('Categories', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-categories',
            array($this, 'admin_categories_page')
        );
        
        // Owners submenu
        add_submenu_page(
            'hisab-dashboard',
            __('Owners', 'hisab-financial-tracker'),
            __('Owners', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-owners',
            array($this, 'admin_owners_page')
        );
        
        // Date Converter submenu
        add_submenu_page(
            'hisab-dashboard',
            __('Date Converter', 'hisab-financial-tracker'),
            __('Date Converter', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-date-converter',
            array($this, 'admin_date_converter_page')
        );
        
        // Settings submenu
        add_submenu_page(
            'hisab-dashboard',
            __('Settings', 'hisab-financial-tracker'),
            __('Settings', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-settings',
            array($this, 'admin_settings_page')
        );
    }
    
    public function admin_dashboard_page() {
        if (!class_exists('HisabAdmin')) {
            echo '<div class="wrap"><h1>Error</h1><p>Admin class not available. Please check if all plugin files are properly uploaded.</p></div>';
            return;
        }
        include HISAB_PLUGIN_PATH . 'admin/dashboard.php';
    }
    
    public function admin_transactions_page() {
        if (!class_exists('HisabAdmin')) {
            echo '<div class="wrap"><h1>Error</h1><p>Admin class not available. Please check if all plugin files are properly uploaded.</p></div>';
            return;
        }
        include HISAB_PLUGIN_PATH . 'admin/views/transactions.php';
    }
    
    public function admin_add_transaction_page() {
        if (!class_exists('HisabAdmin')) {
            echo '<div class="wrap"><h1>Error</h1><p>Admin class not available. Please check if all plugin files are properly uploaded.</p></div>';
            return;
        }
        include HISAB_PLUGIN_PATH . 'admin/add-transaction.php';
    }
    
    public function admin_bank_accounts_page() {
        if (!class_exists('HisabAdmin')) {
            echo '<div class="wrap"><h1>Error</h1><p>Admin class not available. Please check if all plugin files are properly uploaded.</p></div>';
            return;
        }
        include HISAB_PLUGIN_PATH . 'admin/views/bank-accounts.php';
    }
    
    public function admin_add_bank_account_page() {
        if (!class_exists('HisabAdmin')) {
            echo '<div class="wrap"><h1>Error</h1><p>Admin class not available. Please check if all plugin files are properly uploaded.</p></div>';
            return;
        }
        include HISAB_PLUGIN_PATH . 'admin/views/add-bank-account.php';
    }
    
    public function admin_bank_transactions_page() {
        if (!class_exists('HisabAdmin')) {
            echo '<div class="wrap"><h1>Error</h1><p>Admin class not available. Please check if all plugin files are properly uploaded.</p></div>';
            return;
        }
        include HISAB_PLUGIN_PATH . 'admin/views/bank-transactions.php';
    }
    
    public function admin_add_bank_transaction_page() {
        if (!class_exists('HisabAdmin')) {
            echo '<div class="wrap"><h1>Error</h1><p>Admin class not available. Please check if all plugin files are properly uploaded.</p></div>';
            return;
        }
        include HISAB_PLUGIN_PATH . 'admin/views/add-bank-transaction.php';
    }
    
    public function admin_transfer_accounts_page() {
        if (!class_exists('HisabAdmin')) {
            echo '<div class="wrap"><h1>Error</h1><p>Admin class not available. Please check if all plugin files are properly uploaded.</p></div>';
            return;
        }
        include HISAB_PLUGIN_PATH . 'admin/views/transfer-between-accounts.php';
    }
    
    public function admin_analytics_page() {
        if (!class_exists('HisabAdmin')) {
            echo '<div class="wrap"><h1>Error</h1><p>Admin class not available. Please check if all plugin files are properly uploaded.</p></div>';
            return;
        }
        include HISAB_PLUGIN_PATH . 'admin/analytics.php';
    }
    
    public function admin_projections_page() {
        if (!class_exists('HisabAdmin')) {
            echo '<div class="wrap"><h1>Error</h1><p>Admin class not available. Please check if all plugin files are properly uploaded.</p></div>';
            return;
        }
        include HISAB_PLUGIN_PATH . 'admin/projections.php';
    }
    
    public function admin_categories_page() {
        if (!class_exists('HisabAdmin')) {
            echo '<div class="wrap"><h1>Error</h1><p>Admin class not available. Please check if all plugin files are properly uploaded.</p></div>';
            return;
        }
        include HISAB_PLUGIN_PATH . 'admin/categories.php';
    }
    
    public function admin_owners_page() {
        if (!class_exists('HisabAdmin')) {
            echo '<div class="wrap"><h1>Error</h1><p>Admin class not available. Please check if all plugin files are properly uploaded.</p></div>';
            return;
        }
        include HISAB_PLUGIN_PATH . 'admin/owners.php';
    }
    
    public function admin_date_converter_page() {
        if (!class_exists('HisabAdmin')) {
            echo '<div class="wrap"><h1>Error</h1><p>Admin class not available. Please check if all plugin files are properly uploaded.</p></div>';
            return;
        }
        include HISAB_PLUGIN_PATH . 'admin/date-converter.php';
    }
    
    public function admin_settings_page() {
        if (!class_exists('HisabAdmin')) {
            echo '<div class="wrap"><h1>Error</h1><p>Admin class not available. Please check if all plugin files are properly uploaded.</p></div>';
            return;
        }
        include HISAB_PLUGIN_PATH . 'admin/settings.php';
    }
}
