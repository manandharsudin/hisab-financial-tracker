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
        
        // Get recent transactions
        $recent_transactions = $this->database->get_transactions(array('limit' => 10));
        
        // Get trend data for charts
        $income_trend = $this->database->get_trend_data('income', 6);
        $expense_trend = $this->database->get_trend_data('expense', 6);
        
        include HISAB_PLUGIN_PATH . 'admin/views/dashboard.php';
    }
    
    public function render_add_transaction() {
        $categories = $this->database->get_categories();
        $income_categories = array_values(array_filter($categories, function($cat) {
            return $cat->type === 'income';
        }));
        $expense_categories = array_values(array_filter($categories, function($cat) {
            return $cat->type === 'expense';
        }));
        
        
        include HISAB_PLUGIN_PATH . 'admin/views/add-transaction.php';
    }
    
    public function render_analytics() {
        $current_year = date('Y');
        $yearly_data = $this->database->get_yearly_summary($current_year);
        
        // Get category breakdowns
        $income_categories = $this->database->get_category_summary('income', $current_year, date('n'));
        $expense_categories = $this->database->get_category_summary('expense', $current_year, date('n'));
        
        include HISAB_PLUGIN_PATH . 'admin/views/analytics.php';
    }
    
    public function render_projections() {
        $projection = new HisabProjection();
        $projections = $projection->get_future_projections(12); // 12 months ahead
        
        include HISAB_PLUGIN_PATH . 'admin/views/projections.php';
    }
    
    public function render_settings() {
        $currency = get_option('hisab_currency', 'USD');
        $date_format = get_option('hisab_date_format', 'Y-m-d');
        
        if (isset($_POST['save_settings'])) {
            $this->save_settings();
        }
        
        include HISAB_PLUGIN_PATH . 'admin/views/settings.php';
    }
    
    private function save_settings() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'hisab_settings')) {
            wp_die('Security check failed');
        }
        
        update_option('hisab_currency', sanitize_text_field($_POST['currency']));
        update_option('hisab_date_format', sanitize_text_field($_POST['date_format']));
        
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
        });
    }
}
