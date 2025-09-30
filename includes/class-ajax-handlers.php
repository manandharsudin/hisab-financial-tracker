<?php
/**
 * AJAX handlers class for Hisab Financial Tracker
 */

if (!defined('ABSPATH')) {
    exit;
}

class HisabAjaxHandlers {
    
    public function __construct() {
        // Transaction AJAX handlers
        add_action('wp_ajax_hisab_save_transaction', array($this, 'ajax_save_transaction'));
        add_action('wp_ajax_hisab_get_data', array($this, 'ajax_get_data'));
        add_action('wp_ajax_hisab_delete_transaction', array($this, 'ajax_delete_transaction'));
        add_action('wp_ajax_hisab_update_transaction', array($this, 'ajax_update_transaction'));
        
        // Analytics AJAX handlers
        add_action('wp_ajax_hisab_get_analytics_data', array($this, 'ajax_get_analytics_data'));
        add_action('wp_ajax_hisab_get_trend_data', array($this, 'ajax_get_trend_data'));
        add_action('wp_ajax_hisab_get_category_data', array($this, 'ajax_get_category_data'));
        
        // Projection AJAX handlers
        add_action('wp_ajax_hisab_calculate_savings', array($this, 'ajax_calculate_savings'));
        add_action('wp_ajax_hisab_get_projections', array($this, 'ajax_get_projections'));
        
        // Dashboard AJAX handlers
        add_action('wp_ajax_hisab_get_dashboard_data', array($this, 'ajax_get_dashboard_data'));
        add_action('wp_ajax_hisab_export_data', array($this, 'ajax_export_data'));
        
        // Frontend AJAX handlers (for non-logged in users)
        add_action('wp_ajax_nopriv_hisab_get_public_data', array($this, 'ajax_get_public_data'));
        
        // Category AJAX handlers
        add_action('wp_ajax_hisab_save_category', array($this, 'ajax_save_category'));
        add_action('wp_ajax_hisab_delete_category', array($this, 'ajax_delete_category'));
        add_action('wp_ajax_hisab_get_category', array($this, 'ajax_get_category'));
        
        // Date conversion AJAX handlers
        add_action('wp_ajax_hisab_convert_bs_to_ad', array($this, 'ajax_convert_bs_to_ad'));
        add_action('wp_ajax_hisab_convert_ad_to_bs', array($this, 'ajax_convert_ad_to_bs'));
    }
    
    // Transaction AJAX Handlers
    public function ajax_save_transaction() {
        check_ajax_referer('hisab_transaction', 'hisab_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabDatabase')) {
            wp_send_json(array('success' => false, 'message' => 'Database class not available'));
        }
        
        $database = new HisabDatabase();
        $result = $database->save_transaction($_POST);
        
        wp_send_json($result);
    }
    
    public function ajax_get_data() {
        check_ajax_referer('hisab_transaction', 'hisab_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabDatabase')) {
            wp_send_json(array('success' => false, 'message' => 'Database class not available'));
        }
        
        $database = new HisabDatabase();
        $data = $database->get_transactions($_POST);
        
        wp_send_json($data);
    }
    
    public function ajax_delete_transaction() {
        check_ajax_referer('hisab_transaction', 'hisab_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabDatabase')) {
            wp_send_json(array('success' => false, 'message' => 'Database class not available'));
        }
        
        $database = new HisabDatabase();
        $result = $database->delete_transaction($_POST['id']);
        
        wp_send_json($result);
    }
    
    public function ajax_update_transaction() {
        check_ajax_referer('hisab_transaction', 'hisab_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabDatabase')) {
            wp_send_json(array('success' => false, 'message' => 'Database class not available'));
        }
        
        $database = new HisabDatabase();
        $result = $database->update_transaction($_POST);
        
        wp_send_json($result);
    }
    
    // Analytics AJAX Handlers
    public function ajax_get_analytics_data() {
        check_ajax_referer('hisab_transaction', 'hisab_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabAnalytics')) {
            wp_send_json(array('success' => false, 'message' => 'Analytics class not available'));
        }
        
        $analytics = new HisabAnalytics();
        $year = isset($_POST['year']) ? intval($_POST['year']) : date('Y');
        $data = $analytics->get_yearly_analysis($year);
        
        wp_send_json(array('success' => true, 'data' => $data));
    }
    
    public function ajax_get_trend_data() {
        check_ajax_referer('hisab_transaction', 'hisab_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabAnalytics')) {
            wp_send_json(array('success' => false, 'message' => 'Analytics class not available'));
        }
        
        $analytics = new HisabAnalytics();
        $months = isset($_POST['months']) ? intval($_POST['months']) : 12;
        $data = $analytics->get_monthly_trends($months);
        
        wp_send_json(array('success' => true, 'data' => $data));
    }
    
    public function ajax_get_category_data() {
        check_ajax_referer('hisab_transaction', 'hisab_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabDatabase')) {
            wp_send_json(array('success' => false, 'message' => 'Database class not available'));
        }
        
        $database = new HisabDatabase();
        $year = isset($_POST['year']) ? intval($_POST['year']) : date('Y');
        $month = isset($_POST['month']) ? intval($_POST['month']) : date('n');
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'expense';
        
        $data = $database->get_category_summary($type, $year, $month);
        
        wp_send_json(array('success' => true, 'data' => $data));
    }
    
    // Projection AJAX Handlers
    public function ajax_calculate_savings() {
        check_ajax_referer('hisab_transaction', 'hisab_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabProjection')) {
            wp_send_json(array('success' => false, 'message' => 'Projection class not available'));
        }
        
        $target_amount = floatval($_POST['target_amount']);
        $months_to_target = intval($_POST['months_to_target']);
        
        $projection = new HisabProjection();
        $result = $projection->get_savings_projection($target_amount, $months_to_target);
        
        wp_send_json(array('success' => true, 'data' => $result));
    }
    
    public function ajax_get_projections() {
        check_ajax_referer('hisab_transaction', 'hisab_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabProjection')) {
            wp_send_json(array('success' => false, 'message' => 'Projection class not available'));
        }
        
        $months_ahead = isset($_POST['months_ahead']) ? intval($_POST['months_ahead']) : 12;
        
        $projection = new HisabProjection();
        $result = $projection->get_future_projections($months_ahead);
        
        wp_send_json(array('success' => true, 'data' => $result));
    }
    
    // Dashboard AJAX Handlers
    public function ajax_get_dashboard_data() {
        check_ajax_referer('hisab_transaction', 'hisab_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabDatabase')) {
            wp_send_json(array('success' => false, 'message' => 'Database class not available'));
        }
        
        $database = new HisabDatabase();
        $current_month = date('n');
        $current_year = date('Y');
        
        $monthly_summary = $database->get_monthly_summary($current_year, $current_month);
        $recent_transactions = $database->get_transactions(array('limit' => 10));
        
        wp_send_json(array('success' => true, 'data' => array(
            'monthly_summary' => $monthly_summary,
            'recent_transactions' => $recent_transactions
        )));
    }
    
    public function ajax_export_data() {
        check_ajax_referer('hisab_transaction', 'hisab_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $format = sanitize_text_field($_POST['format']);
        $type = sanitize_text_field($_POST['type']);
        
        // This would be implemented based on export requirements
        wp_send_json(array('success' => false, 'message' => 'Export functionality not yet implemented'));
    }
    
    // Public AJAX Handlers (for frontend)
    public function ajax_get_public_data() {
        // This can be used for public-facing shortcodes that don't require authentication
        if (!class_exists('HisabDatabase')) {
            wp_send_json(array('success' => false, 'message' => 'Database class not available'));
        }
        
        $database = new HisabDatabase();
        $current_month = date('n');
        $current_year = date('Y');
        
        $monthly_summary = $database->get_monthly_summary($current_year, $current_month);
        
        wp_send_json(array('success' => true, 'data' => array(
            'monthly_summary' => $monthly_summary
        )));
    }
    
    // Category AJAX Handlers
    public function ajax_save_category() {
        check_ajax_referer('hisab_transaction', 'hisab_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabDatabase')) {
            wp_send_json(array('success' => false, 'message' => 'Database class not available'));
        }
        
        $database = new HisabDatabase();
        $result = $database->save_category($_POST);
        
        wp_send_json($result);
    }
    
    public function ajax_delete_category() {
        check_ajax_referer('hisab_transaction', 'hisab_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabDatabase')) {
            wp_send_json(array('success' => false, 'message' => 'Database class not available'));
        }
        
        $database = new HisabDatabase();
        $result = $database->delete_category($_POST['id']);
        
        wp_send_json($result);
    }
    
    public function ajax_get_category() {
        check_ajax_referer('hisab_transaction', 'hisab_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabDatabase')) {
            wp_send_json(array('success' => false, 'message' => 'Database class not available'));
        }
        
        $database = new HisabDatabase();
        $category = $database->get_category($_POST['id']);
        
        if ($category) {
            wp_send_json(array('success' => true, 'data' => $category));
        } else {
            wp_send_json(array('success' => false, 'message' => 'Category not found'));
        }
    }
    
    // Date Conversion AJAX Handlers
    public function ajax_convert_bs_to_ad() {
        error_log('AJAX convert_bs_to_ad called');
        
        check_ajax_referer('hisab_transaction', 'hisab_nonce');
        
        if (!current_user_can('manage_options')) {
            error_log('Unauthorized user trying to convert date');
            wp_die('Unauthorized');
        }
        
        
        $bs_year = intval($_POST['bs_year']);
        $bs_month = intval($_POST['bs_month']);
        $bs_day = intval($_POST['bs_day']);
        
        $ad_date = HisabNepaliDate::bs_to_ad($bs_year, $bs_month, $bs_day);
        
        if ($ad_date) {
            $ad_date_string = sprintf('%04d-%02d-%02d', $ad_date['year'], $ad_date['month'], $ad_date['day']);
            wp_send_json(array('success' => true, 'data' => array('ad_date' => $ad_date_string)));
        } else {
            wp_send_json(array('success' => false, 'message' => 'Invalid BS date or conversion failed'));
        }
    }
    
    public function ajax_convert_ad_to_bs() {
        error_log('AJAX convert_ad_to_bs called');
        
        check_ajax_referer('hisab_transaction', 'hisab_nonce');
        
        if (!current_user_can('manage_options')) {
            error_log('Unauthorized user trying to convert date');
            wp_die('Unauthorized');
        }
        
        
        $ad_date = sanitize_text_field($_POST['ad_date']);
        $ad_parts = explode('-', $ad_date);
        
        if (count($ad_parts) === 3) {
            $bs_date = HisabNepaliDate::ad_to_bs($ad_parts[0], $ad_parts[1], $ad_parts[2]);
            
            if ($bs_date) {
                wp_send_json(array('success' => true, 'data' => $bs_date));
            } else {
                wp_send_json(array('success' => false, 'message' => 'Invalid AD date or conversion failed'));
            }
        } else {
            wp_send_json(array('success' => false, 'message' => 'Invalid date format'));
        }
    }
}
