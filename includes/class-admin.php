<?php
/**
 * Admin interface class for Hisab Financial Tracker
 */

if (!defined('ABSPATH')) {
    exit;
}

class HisabAdmin {
    
    private $database;
    
    public function __construct() {
        $this->database = new HisabDatabase();
    }
    
    public function render_dashboard() {
        $current_month = date('n');
        $current_year = date('Y');
        
        // Get current month summary
        $monthly_summary = $this->database->get_monthly_summary($current_year, $current_month);
        
        // Get recent transactions (only 5 for dashboard)
        $recent_transactions = $this->database->get_recent_transactions(5);
        
        // Get trend data for charts
        $income_trend = $this->database->get_trend_data('income', 6);
        $expense_trend = $this->database->get_trend_data('expense', 6);
        
        include HISAB_PLUGIN_PATH . 'admin/views/dashboard.php';
    }
    
    public function render_add_transaction($edit_transaction = null) {
        $categories = $this->database->get_categories();
        $income_categories = array_values(array_filter($categories, function($cat) {
            return $cat->type === 'income';
        }));
        $expense_categories = array_values(array_filter($categories, function($cat) {
            return $cat->type === 'expense';
        }));
        
        $owners = $this->database->get_owners();
        
        include HISAB_PLUGIN_PATH . 'admin/views/add-transaction.php';
    }
    
    public function render_analytics() {
        $current_year = date('Y');
        $yearly_data = $this->database->get_yearly_summary($current_year);
        
        // Get category breakdowns for the entire year
        $income_categories = $this->database->get_category_summary('income', $current_year, null);
        $expense_categories = $this->database->get_category_summary('expense', $current_year, null);
        
        include HISAB_PLUGIN_PATH . 'admin/views/analytics.php';
    }
    
    public function render_projections() {
        $projection = new HisabProjection();
        $projections = $projection->get_future_projections(12); // 12 months ahead
        
        include HISAB_PLUGIN_PATH . 'admin/views/projections.php';
    }
    
    public function render_settings() {
        // Process form submission first
        if (isset($_POST['save_settings'])) {
            $this->save_settings();
        }
        
        // Show success message if settings were just saved
        if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true') {
            echo '<div class="notice notice-success is-dismissible"><p><strong>Settings saved successfully!</strong></p></div>';
        }
        
        // Load settings after processing (to show updated values)
        $default_calendar = get_option('hisab_default_calendar', 'ad');
        $show_dual_dates = get_option('hisab_show_dual_dates', 1);
        $enable_logging = get_option('hisab_enable_logging', false);
        $log_actions = get_option('hisab_log_actions', array(
            'TRANSACTION_CREATE', 'TRANSACTION_UPDATE', 'TRANSACTION_DELETE',
            'BANK_TRANSACTION_CREATE', 'BANK_TRANSACTION_UPDATE', 'BANK_TRANSACTION_DELETE',
            'BANK_ACCOUNT_CREATE', 'BANK_ACCOUNT_UPDATE', 'BANK_ACCOUNT_DELETE',
            'CATEGORY_CREATE', 'CATEGORY_UPDATE', 'CATEGORY_DELETE',
            'OWNER_CREATE', 'OWNER_UPDATE', 'OWNER_DELETE',
            'IMPORT', 'EXPORT', 'SETTINGS_UPDATE',
            'TRANSACTION_DETAILS_SAVE', 'TRANSACTION_DETAILS_DELETE'
        ));
        $log_retention_days = get_option('hisab_log_retention_days', 30);
        
        include HISAB_PLUGIN_PATH . 'admin/views/settings.php';
    }
    
    private function save_settings() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'hisab_settings')) {
            wp_die('Security check failed');
        }
        
        // Save basic settings
        update_option('hisab_default_calendar', sanitize_text_field($_POST['default_calendar']));
        update_option('hisab_show_dual_dates', isset($_POST['show_dual_dates']) ? 1 : 0);
        
        // Save logging settings
        update_option('hisab_enable_logging', isset($_POST['enable_logging']) ? 1 : 0);
        update_option('hisab_log_actions', isset($_POST['log_actions']) ? array_map('sanitize_text_field', $_POST['log_actions']) : array());
        update_option('hisab_log_retention_days', intval($_POST['log_retention_days']));
        
        // Log settings update if logging is enabled
        if (class_exists('HisabLogger')) {
            $logger = new HisabLogger();
            $logger->info(HisabLogger::ACTION_SETTINGS_UPDATE, 'Settings updated', array(
                'default_calendar' => sanitize_text_field($_POST['default_calendar']),
                'show_dual_dates' => isset($_POST['show_dual_dates']) ? 1 : 0,
                'enable_logging' => isset($_POST['enable_logging']) ? 1 : 0,
                'log_actions' => isset($_POST['log_actions']) ? $_POST['log_actions'] : array(),
                'log_retention_days' => intval($_POST['log_retention_days'])
            ));
        }
        
        // Set a flag to show success message
        $_GET['settings-updated'] = 'true';
    }
    
    public function render_categories() {
        $categories = $this->database->get_categories();
        $income_categories = array_values(array_filter($categories, function($cat) {
            return $cat->type === 'income';
        }));
        $expense_categories = array_values(array_filter($categories, function($cat) {
            return $cat->type === 'expense';
        }));
        
        include HISAB_PLUGIN_PATH . 'admin/views/categories.php';
    }
    
    public function render_owners() {
        include HISAB_PLUGIN_PATH . 'admin/views/owners.php';
    }
    
    public function render_bank_analytics() {
        // Initialize bank analytics
        if (!class_exists('HisabBankAnalytics')) {
            echo '<div class="wrap"><h1>Error</h1><p>Bank Analytics class not available.</p></div>';
            return;
        }
        
        $bank_analytics = new HisabBankAnalytics();
        
        // Get filter parameters
        $selected_year = isset($_GET['year']) ? intval($_GET['year']) : null;
        $selected_month = isset($_GET['month']) ? intval($_GET['month']) : null;
        
        // Get available years and months for filters
        $available_years = $bank_analytics->get_available_years();
        if (!$available_years) {
            $available_years = array();
        }
        
        $available_months = $selected_year ? $bank_analytics->get_available_months($selected_year) : array();
        if (!$available_months) {
            $available_months = array();
        }
        
        $date_range = $bank_analytics->get_transaction_date_range();
        if (!$date_range) {
            $date_range = array('start_date' => null, 'end_date' => null);
        }
        
        // Get analytics data - all data by default, or filtered by year/month
        if ($selected_year && $selected_month) {
            // Specific month
            $start_date = sprintf('%04d-%02d-01', $selected_year, $selected_month);
            $end_date = date('Y-m-t', strtotime($start_date));
            $trend_data = $bank_analytics->get_bank_trend_data(null, $start_date, $end_date);
            $balance_trends = $bank_analytics->get_account_balance_trends(null, $start_date, $end_date);
            $transaction_summary = $bank_analytics->get_bank_transaction_summary($selected_year, $selected_month);
            $account_summary = $bank_analytics->get_account_transaction_summary($selected_year, $selected_month);
            $monthly_summary = $bank_analytics->get_monthly_summary($selected_year, $selected_month);
        } elseif ($selected_year) {
            // Specific year
            $start_date = sprintf('%04d-01-01', $selected_year);
            $end_date = sprintf('%04d-12-31', $selected_year);
            $trend_data = $bank_analytics->get_bank_trend_data(null, $start_date, $end_date);
            $balance_trends = $bank_analytics->get_account_balance_trends(null, $start_date, $end_date);
            $transaction_summary = $bank_analytics->get_bank_transaction_summary($selected_year);
            $account_summary = $bank_analytics->get_account_transaction_summary($selected_year);
            $monthly_summary = $bank_analytics->get_monthly_summary($selected_year, date('n'));
        } else {
            // All data from beginning to current
            $trend_data = $bank_analytics->get_bank_trend_data();
            $balance_trends = $bank_analytics->get_account_balance_trends();
            $transaction_summary = $bank_analytics->get_bank_transaction_summary();
            $account_summary = $bank_analytics->get_account_transaction_summary();
            $monthly_summary = $bank_analytics->get_monthly_summary();
        }
        
        // Ensure all variables are properly initialized
        if (!isset($trend_data) || !$trend_data) {
            $trend_data = array('months' => array(), 'data' => array());
        }
        if (!isset($transaction_summary) || !$transaction_summary) {
            $transaction_summary = array();
        }
        if (!isset($account_summary) || !$account_summary) {
            $account_summary = array();
        }
        if (!isset($balance_trends) || !$balance_trends) {
            $balance_trends = array('months' => array(), 'accounts' => array());
        }
        if (!isset($monthly_summary) || !$monthly_summary) {
            $monthly_summary = array(
                'deposits' => 0, 'withdrawals' => 0, 'phone_pay' => 0,
                'transfers_in' => 0, 'transfers_out' => 0, 'net_flow' => 0, 'total_transactions' => 0
            );
        }
        
        include HISAB_PLUGIN_PATH . 'admin/views/bank-analytics.php';
    }
}
