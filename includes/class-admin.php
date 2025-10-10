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
            'IMPORT', 'EXPORT', 'SETTINGS_UPDATE'
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
}
