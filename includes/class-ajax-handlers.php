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
        add_action('wp_ajax_hisab_import_data', array($this, 'ajax_import_data'));
        
        // Frontend AJAX handlers (for non-logged in users)
        add_action('wp_ajax_nopriv_hisab_get_public_data', array($this, 'ajax_get_public_data'));
        
        // Category AJAX handlers
        add_action('wp_ajax_hisab_save_category', array($this, 'ajax_save_category'));
        add_action('wp_ajax_hisab_delete_category', array($this, 'ajax_delete_category'));
        add_action('wp_ajax_hisab_get_category', array($this, 'ajax_get_category'));
        add_action('wp_ajax_hisab_insert_default_categories', array($this, 'ajax_insert_default_categories'));
        
        // Date conversion AJAX handlers
        add_action('wp_ajax_hisab_convert_bs_to_ad', array($this, 'ajax_convert_bs_to_ad'));
        add_action('wp_ajax_hisab_convert_ad_to_bs', array($this, 'ajax_convert_ad_to_bs'));
        
        // Owner AJAX handlers
        add_action('wp_ajax_hisab_save_owner', array($this, 'ajax_save_owner'));
        add_action('wp_ajax_hisab_delete_owner', array($this, 'ajax_delete_owner'));
        
        // Transaction Details AJAX handlers
        add_action('wp_ajax_hisab_get_transaction', array($this, 'ajax_get_transaction'));
        add_action('wp_ajax_hisab_get_transaction_details', array($this, 'ajax_get_transaction_details'));
        add_action('wp_ajax_hisab_save_transaction_details', array($this, 'ajax_save_transaction_details'));
        add_action('wp_ajax_hisab_delete_transaction_details', array($this, 'ajax_delete_transaction_details'));
        
        // Bank Account AJAX handlers
        add_action('wp_ajax_hisab_save_bank_account', array($this, 'ajax_save_bank_account'));
        add_action('wp_ajax_hisab_delete_bank_account', array($this, 'ajax_delete_bank_account'));
        add_action('wp_ajax_hisab_get_bank_account', array($this, 'ajax_get_bank_account'));
        add_action('wp_ajax_hisab_get_bank_accounts', array($this, 'ajax_get_bank_accounts'));
        
        // Bank Transaction AJAX handlers
        add_action('wp_ajax_hisab_save_bank_transaction', array($this, 'ajax_save_bank_transaction'));
        add_action('wp_ajax_hisab_delete_bank_transaction', array($this, 'ajax_delete_bank_transaction'));
        add_action('wp_ajax_hisab_get_bank_transaction', array($this, 'ajax_get_bank_transaction'));
        add_action('wp_ajax_hisab_get_bank_transactions', array($this, 'ajax_get_bank_transactions'));
        add_action('wp_ajax_hisab_transfer_between_accounts', array($this, 'ajax_transfer_between_accounts'));
    }
    
    // Transaction AJAX Handlers
    public function ajax_save_transaction() {
        check_ajax_referer('hisab_transaction', 'nonce');
        
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
        check_ajax_referer('hisab_transaction', 'nonce');
        
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
        check_ajax_referer('hisab_transaction', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabDatabase')) {
            wp_send_json_error(array('message' => __('Database class not available', 'hisab-financial-tracker')));
        }
        
        // Handle both parameter formats
        $transaction_id = isset($_POST['transaction_id']) ? intval($_POST['transaction_id']) : intval($_POST['id']);
        
        if (!$transaction_id) {
            wp_send_json_error(array('message' => __('Invalid transaction ID', 'hisab-financial-tracker')));
        }
        
        $database = new HisabDatabase();
        $result = $database->delete_transaction($transaction_id);
        
        if ($result) {
            wp_send_json_success(array('message' => __('Transaction deleted successfully', 'hisab-financial-tracker')));
        } else {
            wp_send_json_error(array('message' => __('Failed to delete transaction', 'hisab-financial-tracker')));
        }
    }
    
    public function ajax_update_transaction() {
        check_ajax_referer('hisab_transaction', 'nonce');
        
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
        check_ajax_referer('hisab_transaction', 'nonce');
        
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
        check_ajax_referer('hisab_transaction', 'nonce');
        
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
        check_ajax_referer('hisab_transaction', 'nonce');
        
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
        check_ajax_referer('hisab_transaction', 'nonce');
        
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
        check_ajax_referer('hisab_transaction', 'nonce');
        
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
        check_ajax_referer('hisab_transaction', 'nonce');
        
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
        // Check nonce
        if (!wp_verify_nonce($_POST['nonce'], 'hisab_transaction')) {
            wp_send_json(array('success' => false, 'message' => 'Security check failed'));
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json(array('success' => false, 'message' => 'Unauthorized'));
        }
        
        if (!class_exists('HisabImportExport')) {
            wp_send_json(array('success' => false, 'message' => 'Import/Export class not available'));
        }
        
        $export_type = isset($_POST['export_type']) ? sanitize_text_field($_POST['export_type']) : 'all';
        
        try {
            $import_export = new HisabImportExport();
            $data = $import_export->export_all_data($export_type);
            
            // Export successful
            
            wp_send_json(array('success' => true, 'data' => $data));
            
        } catch (Exception $e) {
            error_log('Export error: ' . $e->getMessage());
            wp_send_json(array('success' => false, 'message' => 'Export failed: ' . $e->getMessage()));
        }
    }
    
    public function ajax_import_data() {
        check_ajax_referer('hisab_transaction', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabImportExport')) {
            wp_send_json(array('success' => false, 'message' => 'Import/Export class not available'));
        }
        
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json(array('success' => false, 'message' => 'No file uploaded or upload error'));
        }
        
        $file = $_FILES['import_file'];
        $file_content = file_get_contents($file['tmp_name']);
        
        if (!$file_content) {
            wp_send_json(array('success' => false, 'message' => 'Could not read uploaded file'));
        }
        
        // Parse JSON data
        $json_data = json_decode($file_content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json(array('success' => false, 'message' => 'Invalid JSON file: ' . json_last_error_msg()));
        }
        
        // Get import options
        $options = array(
            'import_categories' => isset($_POST['import_categories']) ? (bool)$_POST['import_categories'] : true,
            'import_owners' => isset($_POST['import_owners']) ? (bool)$_POST['import_owners'] : true,
            'import_bank_accounts' => isset($_POST['import_bank_accounts']) ? (bool)$_POST['import_bank_accounts'] : true,
            'import_transactions' => isset($_POST['import_transactions']) ? (bool)$_POST['import_transactions'] : true,
            'import_bank_transactions' => isset($_POST['import_bank_transactions']) ? (bool)$_POST['import_bank_transactions'] : true,
            'import_transaction_details' => isset($_POST['import_transaction_details']) ? (bool)$_POST['import_transaction_details'] : true,
            'skip_duplicates' => isset($_POST['skip_duplicates']) ? (bool)$_POST['skip_duplicates'] : true,
            'update_existing' => isset($_POST['update_existing']) ? (bool)$_POST['update_existing'] : false
        );
        
        try {
            $import_export = new HisabImportExport();
            $results = $import_export->import_from_json($json_data, $options);
            wp_send_json($results);
            
        } catch (Exception $e) {
            wp_send_json(array('success' => false, 'message' => 'Import failed: ' . $e->getMessage()));
        }
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
        check_ajax_referer('hisab_transaction', 'nonce');
        
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
        check_ajax_referer('hisab_transaction', 'nonce');
        
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
        check_ajax_referer('hisab_transaction', 'nonce');
        
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
        
        check_ajax_referer('hisab_transaction', 'nonce');
        
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
        
        check_ajax_referer('hisab_transaction', 'nonce');
        
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
    
    public function ajax_insert_default_categories() {
        check_ajax_referer('hisab_transaction', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabDatabase')) {
            wp_send_json(array('success' => false, 'message' => 'Database class not available'));
        }
        
        $database = new HisabDatabase();
        $result = $database->insert_default_categories_manually();
        
        wp_send_json($result);
    }
    
    // Owner AJAX Handlers
    public function ajax_save_owner() {
        check_ajax_referer('hisab_transaction', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabDatabase')) {
            wp_send_json(array('success' => false, 'message' => 'Database class not available'));
        }
        
        $database = new HisabDatabase();
        $result = $database->save_owner($_POST);
        
        wp_send_json($result);
    }
    
    public function ajax_delete_owner() {
        check_ajax_referer('hisab_transaction', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabDatabase')) {
            wp_send_json(array('success' => false, 'message' => 'Database class not available'));
        }
        
        $database = new HisabDatabase();
        $result = $database->delete_owner($_POST['owner_id']);
        
        wp_send_json($result);
    }
    
    // Transaction Details AJAX Handlers
    public function ajax_get_transaction() {
        check_ajax_referer('hisab_transaction', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabDatabase')) {
            wp_send_json(array('success' => false, 'message' => 'Database class not available'));
        }
        
        $transaction_id = intval($_POST['transaction_id']);
        $database = new HisabDatabase();
        $transaction = $database->get_transaction($transaction_id);
        
        if ($transaction) {
            wp_send_json(array('success' => true, 'data' => $transaction));
        } else {
            wp_send_json(array('success' => false, 'message' => 'Transaction not found'));
        }
    }
    
    public function ajax_get_transaction_details() {
        check_ajax_referer('hisab_transaction', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabDatabase')) {
            wp_send_json(array('success' => false, 'message' => 'Database class not available'));
        }
        
        $transaction_id = intval($_POST['transaction_id']);
        $database = new HisabDatabase();
        $details = $database->get_transaction_details($transaction_id);
        
        wp_send_json(array('success' => true, 'data' => $details));
    }
    
    public function ajax_save_transaction_details() {
        check_ajax_referer('hisab_transaction', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabDatabase')) {
            wp_send_json(array('success' => false, 'message' => 'Database class not available'));
        }
        
        $transaction_id = intval($_POST['transaction_id']);
        $details = $_POST['details'];
        
        // Validate details format
        if (!is_array($details)) {
            wp_send_json(array('success' => false, 'message' => 'Invalid details format'));
        }
        
        $database = new HisabDatabase();
        
        // Validate details against main transaction amount
        $validation = $database->validate_transaction_details($transaction_id, $details);
        if (!$validation['success']) {
            wp_send_json($validation);
        }
        
        $result = $database->save_transaction_details($transaction_id, $details);
        wp_send_json($result);
    }
    
    public function ajax_delete_transaction_details() {
        check_ajax_referer('hisab_transaction', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabDatabase')) {
            wp_send_json(array('success' => false, 'message' => 'Database class not available'));
        }
        
        $transaction_id = intval($_POST['transaction_id']);
        $database = new HisabDatabase();
        $result = $database->delete_transaction_details($transaction_id);
        
        wp_send_json($result);
    }
    
    // Bank Account AJAX Handlers
    public function ajax_save_bank_account() {
        check_ajax_referer('hisab_bank_account', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabBankAccount')) {
            wp_send_json(array('success' => false, 'message' => 'Bank Account class not available'));
        }
        
        $bank_account = new HisabBankAccount();
        
        $data = array(
            'account_name' => sanitize_text_field($_POST['account_name']),
            'bank_name' => sanitize_text_field($_POST['bank_name']),
            'account_number' => sanitize_text_field($_POST['account_number']),
            'account_type' => sanitize_text_field($_POST['account_type']),
            'currency' => sanitize_text_field($_POST['currency']),
            'initial_balance' => floatval($_POST['initial_balance']),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        );
        
        $account_id = isset($_POST['account_id']) ? intval($_POST['account_id']) : 0;
        
        if ($account_id > 0) {
            $result = $bank_account->update_account($account_id, $data);
        } else {
            $result = $bank_account->create_account($data);
        }
        
        if (is_wp_error($result)) {
            wp_send_json(array('success' => false, 'message' => $result->get_error_message()));
        }
        
        wp_send_json(array('success' => true, 'message' => 'Bank account saved successfully', 'account_id' => $result));
    }
    
    public function ajax_delete_bank_account() {
        check_ajax_referer('hisab_bank_account', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabBankAccount')) {
            wp_send_json(array('success' => false, 'message' => 'Bank Account class not available'));
        }
        
        $account_id = intval($_POST['account_id']);
        $bank_account = new HisabBankAccount();
        
        $result = $bank_account->delete_account($account_id);
        
        if (is_wp_error($result)) {
            wp_send_json(array('success' => false, 'message' => $result->get_error_message()));
        }
        
        wp_send_json(array('success' => true, 'message' => 'Bank account deleted successfully'));
    }
    
    public function ajax_get_bank_account() {
        check_ajax_referer('hisab_bank_account', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabBankAccount')) {
            wp_send_json(array('success' => false, 'message' => 'Bank Account class not available'));
        }
        
        $account_id = intval($_POST['account_id']);
        $bank_account = new HisabBankAccount();
        
        $account = $bank_account->get_account($account_id);
        
        if (!$account) {
            wp_send_json(array('success' => false, 'message' => 'Bank account not found'));
        }
        
        wp_send_json(array('success' => true, 'data' => $account));
    }
    
    public function ajax_get_bank_accounts() {
        check_ajax_referer('hisab_bank_account', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabBankAccount')) {
            wp_send_json(array('success' => false, 'message' => 'Bank Account class not available'));
        }
        
        $filters = array();
        if (isset($_POST['currency'])) {
            $filters['currency'] = sanitize_text_field($_POST['currency']);
        }
        if (isset($_POST['is_active'])) {
            $filters['is_active'] = intval($_POST['is_active']);
        }
        
        $bank_account = new HisabBankAccount();
        $accounts = $bank_account->get_all_accounts($filters);
        
        wp_send_json(array('success' => true, 'data' => $accounts));
    }
    
    // Bank Transaction AJAX Handlers
    public function ajax_save_bank_transaction() {
        check_ajax_referer('hisab_bank_transaction', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabBankTransaction')) {
            wp_send_json(array('success' => false, 'message' => 'Bank Transaction class not available'));
        }
        
        $bank_transaction = new HisabBankTransaction();
        
        $data = array(
            'account_id' => intval($_POST['account_id']),
            'transaction_type' => sanitize_text_field($_POST['transaction_type']),
            'amount' => floatval($_POST['amount']),
            'currency' => sanitize_text_field($_POST['currency']),
            'description' => sanitize_textarea_field($_POST['description']),
            'reference_number' => sanitize_text_field($_POST['reference_number']),
            'phone_pay_reference' => sanitize_text_field($_POST['phone_pay_reference']),
            'transaction_date' => sanitize_text_field($_POST['transaction_date'])
        );
        
        $transaction_id = isset($_POST['transaction_id']) ? intval($_POST['transaction_id']) : 0;
        
        if ($transaction_id > 0) {
            $result = $bank_transaction->update_transaction($transaction_id, $data);
        } else {
            $result = $bank_transaction->create_transaction($data);
        }
        
        if (is_wp_error($result)) {
            wp_send_json(array('success' => false, 'message' => $result->get_error_message()));
        }
        
        wp_send_json(array('success' => true, 'message' => 'Bank transaction saved successfully', 'transaction_id' => $result));
    }
    
    public function ajax_delete_bank_transaction() {
        check_ajax_referer('hisab_bank_transaction', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabBankTransaction')) {
            wp_send_json(array('success' => false, 'message' => 'Bank Transaction class not available'));
        }
        
        $transaction_id = intval($_POST['transaction_id']);
        $bank_transaction = new HisabBankTransaction();
        
        $result = $bank_transaction->delete_transaction($transaction_id);
        
        if (is_wp_error($result)) {
            wp_send_json(array('success' => false, 'message' => $result->get_error_message()));
        }
        
        wp_send_json(array('success' => true, 'message' => 'Bank transaction deleted successfully'));
    }
    
    public function ajax_get_bank_transaction() {
        check_ajax_referer('hisab_bank_transaction', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabBankTransaction')) {
            wp_send_json(array('success' => false, 'message' => 'Bank Transaction class not available'));
        }
        
        $transaction_id = intval($_POST['transaction_id']);
        $bank_transaction = new HisabBankTransaction();
        
        $transaction = $bank_transaction->get_transaction($transaction_id);
        
        if (!$transaction) {
            wp_send_json(array('success' => false, 'message' => 'Bank transaction not found'));
        }
        
        wp_send_json(array('success' => true, 'data' => $transaction));
    }
    
    public function ajax_get_bank_transactions() {
        check_ajax_referer('hisab_bank_transaction', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabBankTransaction')) {
            wp_send_json(array('success' => false, 'message' => 'Bank Transaction class not available'));
        }
        
        $filters = array();
        if (isset($_POST['account_id'])) {
            $filters['account_id'] = intval($_POST['account_id']);
        }
        if (isset($_POST['transaction_type'])) {
            $filters['transaction_type'] = sanitize_text_field($_POST['transaction_type']);
        }
        if (isset($_POST['start_date'])) {
            $filters['start_date'] = sanitize_text_field($_POST['start_date']);
        }
        if (isset($_POST['end_date'])) {
            $filters['end_date'] = sanitize_text_field($_POST['end_date']);
        }
        
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 20;
        
        $bank_transaction = new HisabBankTransaction();
        $result = $bank_transaction->get_all_transactions($filters, $page, $per_page);
        
        wp_send_json(array('success' => true, 'data' => $result));
    }
    
    public function ajax_transfer_between_accounts() {
        check_ajax_referer('hisab_transfer', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabBankTransaction')) {
            wp_send_json(array('success' => false, 'message' => 'Bank Transaction class not available'));
        }
        
        $from_account_id = intval($_POST['from_account_id']);
        $to_account_id = intval($_POST['to_account_id']);
        $amount = floatval($_POST['amount']);
        $description = sanitize_textarea_field($_POST['description']);
        $transaction_date = sanitize_text_field($_POST['transaction_date']);
        
        if ($from_account_id === $to_account_id) {
            wp_send_json(array('success' => false, 'message' => 'Cannot transfer to the same account'));
        }
        
        $bank_transaction = new HisabBankTransaction();
        $result = $bank_transaction->create_transfer($from_account_id, $to_account_id, $amount, $description, $transaction_date);
        
        if (is_wp_error($result)) {
            wp_send_json(array('success' => false, 'message' => $result->get_error_message()));
        }
        
        wp_send_json(array('success' => true, 'message' => 'Transfer completed successfully', 'data' => $result));
    }
}
