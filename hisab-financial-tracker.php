<?php
/**
 * Plugin Name: Hisab Financial Tracker
 * Description: A comprehensive financial tracking plugin for managing monthly income and expenses with trend analysis and future projections.
 * Version: 1.0.0
 * Author: Sudin Manandhar
 * License: GPL v3 or later
 * Text Domain: hisab-financial-tracker
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('HISAB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('HISAB_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('HISAB_VERSION', '1.0.0');

// Define currency and date format constants
define('HISAB_CURRENCY_SYMBOL', '₹');
define('HISAB_DATE_FORMAT', 'M j, Y');

class HisabFinancialTracker {
    
    public function __construct() {
        // Include required files immediately
        $this->include_files();
        
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Load text domain
        load_plugin_textdomain('hisab-financial-tracker', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Initialize components
        $this->init_components();
        
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
    }
    
    private function include_files() {
        require_once HISAB_PLUGIN_PATH . 'includes/class-database.php';
        require_once HISAB_PLUGIN_PATH . 'includes/class-admin.php';
        require_once HISAB_PLUGIN_PATH . 'includes/class-frontend.php';
        require_once HISAB_PLUGIN_PATH . 'includes/class-analytics.php';
        require_once HISAB_PLUGIN_PATH . 'includes/class-projection.php';
        require_once HISAB_PLUGIN_PATH . 'includes/class-admin-menu.php';
        require_once HISAB_PLUGIN_PATH . 'includes/class-shortcodes.php';
        require_once HISAB_PLUGIN_PATH . 'includes/class-ajax-handlers.php';
        require_once HISAB_PLUGIN_PATH . 'includes/class-nepali-date.php';
        require_once HISAB_PLUGIN_PATH . 'includes/class-bank-account.php';
        require_once HISAB_PLUGIN_PATH . 'includes/class-bank-transaction.php';
    }
    
    private function init_components() {
        if (class_exists('HisabDatabase')) {
            new HisabDatabase();
        }
        if (class_exists('HisabAdmin')) {
            new HisabAdmin();
        }
        if (class_exists('HisabFrontend')) {
            new HisabFrontend();
        }
        if (class_exists('HisabAnalytics')) {
            new HisabAnalytics();
        }
        if (class_exists('HisabProjection')) {
            new HisabProjection();
        }
        if (class_exists('HisabAdminMenu')) {
            new HisabAdminMenu();
        }
        if (class_exists('HisabShortcodes')) {
            new HisabShortcodes();
        }
        if (class_exists('HisabAjaxHandlers')) {
            new HisabAjaxHandlers();
        }
    }
    
    public function activate() {
        // Create database tables
        $database = new HisabDatabase();
        $database->create_tables();
        
        // Set default options
        add_option('hisab_version', HISAB_VERSION);
    }
    
    public function deactivate() {
        // Clean up if needed
    }
    
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'hisab') === false && strpos($hook, 'date-converter') === false) {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.9.1', true);
        wp_enqueue_media(); // WordPress Media Uploader
        wp_enqueue_script('hisab-admin', HISAB_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'chart-js'), HISAB_VERSION, true);
        wp_enqueue_style('hisab-admin', HISAB_PLUGIN_URL . 'assets/css/admin.css', array(), HISAB_VERSION);
        
        wp_localize_script('hisab-admin', 'hisab_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hisab_transaction'),
            'currency' => HISAB_CURRENCY_SYMBOL,
            'insufficient_balance' => __('Insufficient balance for this transaction.', 'hisab-financial-tracker'),
            'amount_required' => __('Amount must be greater than zero.', 'hisab-financial-tracker'),
            'select_category' => __('Please select a category.', 'hisab-financial-tracker'),
            'saving_transaction' => __('Saving transaction...', 'hisab-financial-tracker'),
            'select_bs_date' => __('Please select BS year, month, and day', 'hisab-financial-tracker'),
            'date_conversion_failed' => __('Date conversion failed. Please try again.', 'hisab-financial-tracker'),
            'transaction_updated' => __('Transaction updated successfully!', 'hisab-financial-tracker'),
            'error_saving' => __('An error occurred while saving the transaction.', 'hisab-financial-tracker'),
            'add_itemized_details' => __('Add Itemized Details', 'hisab-financial-tracker'),
            'select_owner_optional' => __('Select Owner (Optional)', 'hisab-financial-tracker'),
            'select_category_placeholder' => __('Select Category', 'hisab-financial-tracker'),
            'select_bill_image' => __('Select Bill Image', 'hisab-financial-tracker'),
            'use_this_image' => __('Use This Image', 'hisab-financial-tracker'),
            'add_at_least_one_item' => __('Please add at least one item.', 'hisab-financial-tracker'),
            'error_saving_details' => __('An error occurred while saving details.', 'hisab-financial-tracker'),
            'income' => __('Income', 'hisab-financial-tracker'),
            'expenses' => __('Expenses', 'hisab-financial-tracker'),
            'saving' => __('Saving...', 'hisab-financial-tracker'),
            'save_category' => __('Save Category', 'hisab-financial-tracker'),
            'update_category' => __('Update Category', 'hisab-financial-tracker'),
            'error_saving_category' => __('An error occurred while saving the category.', 'hisab-financial-tracker'),
            'confirm_delete_category' => __('Are you sure you want to delete this category?', 'hisab-financial-tracker'),
            'select_ad_date' => __('Please select an AD date', 'hisab-financial-tracker'),
            'select_bs_date_components' => __('Please select BS year, month, and day', 'hisab-financial-tracker'),
            'conversion_failed' => __('Conversion failed', 'hisab-financial-tracker'),
            'error_converting_date' => __('Error converting date', 'hisab-financial-tracker'),
            'confirm_clear_history' => __('Are you sure you want to clear the conversion history?', 'hisab-financial-tracker'),
            'no_conversion_history' => __('No conversion history yet', 'hisab-financial-tracker'),
            'save_owner' => __('Save Owner', 'hisab-financial-tracker'),
            'update_owner' => __('Update Owner', 'hisab-financial-tracker'),
            'error_saving_owner' => __('An error occurred while saving the owner.', 'hisab-financial-tracker'),
            'confirm_delete_owner' => __('Are you sure you want to delete this owner?', 'hisab-financial-tracker'),
            'deleting' => __('Deleting...', 'hisab-financial-tracker'),
            'error_deleting_owner' => __('An error occurred while deleting the owner.', 'hisab-financial-tracker'),
            'projected_income' => __('Projected Income', 'hisab-financial-tracker'),
            'projected_expenses' => __('Projected Expenses', 'hisab-financial-tracker'),
            'projected_net' => __('Projected Net', 'hisab-financial-tracker'),
            'enter_target_and_months' => __('Please enter both target amount and months to target.', 'hisab-financial-tracker'),
            'goal_achievable' => __('Goal is achievable!', 'hisab-financial-tracker'),
            'required_monthly_savings' => __('Required monthly savings:', 'hisab-financial-tracker'),
            'current_monthly_savings' => __('Current monthly savings:', 'hisab-financial-tracker'),
            'goal_difficult' => __('Goal may be difficult to achieve.', 'hisab-financial-tracker'),
            'increase_savings_by' => __('You need to increase savings by:', 'hisab-financial-tracker'),
            'error_calculating_savings' => __('An error occurred while calculating savings.', 'hisab-financial-tracker')
        ));
    }
    
    public function enqueue_frontend_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.9.1', true);
        wp_enqueue_script('hisab-frontend', HISAB_PLUGIN_URL . 'assets/js/frontend.js', array('jquery', 'chart-js'), HISAB_VERSION, true);
        wp_enqueue_style('hisab-frontend', HISAB_PLUGIN_URL . 'assets/css/frontend.css', array(), HISAB_VERSION);
        
        wp_localize_script('hisab-frontend', 'hisab_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hisab_transaction'),
            'currency' => HISAB_CURRENCY_SYMBOL
        ));
    }
    
}

// Initialize the plugin
new HisabFinancialTracker();