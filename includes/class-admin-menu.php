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
        add_action('admin_init', array($this, 'handle_form_submissions'));
    }
    
    public function add_admin_menu() {
        // Bank Management main menu
        add_menu_page(
            __('Bank Management', 'hisab-financial-tracker'),
            __('Bank Management', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-bank-accounts',
            array($this, 'admin_bank_accounts_page'),
            'dashicons-bank',
            30
        );
        
        // Bank Accounts submenu
        add_submenu_page(
            'hisab-bank-accounts',
            __('Bank Accounts', 'hisab-financial-tracker'),
            __('Bank Accounts', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-bank-accounts',
            array($this, 'admin_bank_accounts_page')
        );
        
        // Add Bank Account submenu
        add_submenu_page(
            'hisab-bank-accounts',
            __('Add Bank Account', 'hisab-financial-tracker'),
            __('Add Bank Account', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-add-bank-account',
            array($this, 'admin_add_bank_account_page')
        );
        
        // Bank Transactions submenu
        add_submenu_page(
            'hisab-bank-accounts',
            __('Bank Transactions', 'hisab-financial-tracker'),
            __('Bank Transactions', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-bank-transactions',
            array($this, 'admin_bank_transactions_page')
        );
        
        // Add Bank Transaction submenu
        add_submenu_page(
            'hisab-bank-accounts',
            __('Add Bank Transaction', 'hisab-financial-tracker'),
            __('Add Bank Transaction', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-add-bank-transaction',
            array($this, 'admin_add_bank_transaction_page')
        );
        
        // Transfer Between Accounts submenu
        add_submenu_page(
            'hisab-bank-accounts',
            __('Transfer Between Accounts', 'hisab-financial-tracker'),
            __('Transfer Between Accounts', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-transfer-accounts',
            array($this, 'admin_transfer_accounts_page')
        );        
        
        // Transactions main menu
        add_menu_page(
            __('Transactions', 'hisab-financial-tracker'),
            __('Transactions', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-transactions',
            array($this, 'admin_transactions_page'),
            'dashicons-money-alt',
            31
        );
        
        // All Transactions submenu
        add_submenu_page(
            'hisab-transactions',
            __('All Transactions', 'hisab-financial-tracker'),
            __('All Transactions', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-transactions',
            array($this, 'admin_transactions_page')
        );
        
        // Add Transaction submenu
        add_submenu_page(
            'hisab-transactions',
            __('Add Transaction', 'hisab-financial-tracker'),
            __('Add Transaction', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-add-transaction',
            array($this, 'admin_add_transaction_page')
        );
        
        // Categories submenu
        add_submenu_page(
            'hisab-transactions',
            __('Categories', 'hisab-financial-tracker'),
            __('Categories', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-categories',
            array($this, 'admin_categories_page')
        );
        
        // Owners submenu
        add_submenu_page(
            'hisab-transactions',
            __('Owners', 'hisab-financial-tracker'),
            __('Owners', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-owners',
            array($this, 'admin_owners_page')
        );

        // Main Dashboard menu
        add_menu_page(
            __('Financial Tracker', 'hisab-financial-tracker'),
            __('Financial Tracker', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-dashboard',
            array($this, 'admin_dashboard_page'),
            'dashicons-chart-line',
            32
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
        
        // Tools main menu
        add_menu_page(
            __('Hisab Tools', 'hisab-financial-tracker'),
            __('Hisab Tools', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-date-converter',
            array($this, 'admin_date_converter_page'),
            'dashicons-admin-tools',
            33
        );
        
        // Date Converter submenu
        add_submenu_page(
            'hisab-date-converter',
            __('Date Converter', 'hisab-financial-tracker'),
            __('Date Converter', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-date-converter',
            array($this, 'admin_date_converter_page')
        );
        
        // Settings submenu
        add_submenu_page(
            'hisab-date-converter',
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
    
    /**
     * Handle form submissions before any output
     */
    public function handle_form_submissions() {
        // Only process on our admin pages
        if (!isset($_GET['page']) || strpos($_GET['page'], 'hisab-') !== 0) {
            return;
        }
        
        // Handle bank account form submission
        if (isset($_POST['submit_bank_account']) && $_GET['page'] === 'hisab-add-bank-account') {
            $this->handle_bank_account_submission();
        }
        
        // Handle bank transaction form submission
        if (isset($_POST['submit_bank_transaction']) && $_GET['page'] === 'hisab-add-bank-transaction') {
            $this->handle_bank_transaction_submission();
        }
    }
    
    /**
     * Handle bank account form submission
     */
    private function handle_bank_account_submission() {
        if (!class_exists('HisabBankAccount')) {
            return;
        }
        
        $bank_account = new HisabBankAccount();
        
        $nonce = sanitize_text_field($_POST['_wpnonce']);
        if (!wp_verify_nonce($nonce, 'hisab_bank_account')) {
            wp_redirect(admin_url('admin.php?page=hisab-add-bank-account&error=security'));
            exit;
        }
        
        $data = array(
            'account_name' => sanitize_text_field($_POST['account_name']),
            'bank_name' => sanitize_text_field($_POST['bank_name']),
            'account_number' => sanitize_text_field($_POST['account_number']),
            'account_type' => sanitize_text_field($_POST['account_type']),
            'currency' => sanitize_text_field($_POST['currency']),
            'initial_balance' => floatval($_POST['initial_balance']),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        );
        
        // Check if editing
        $is_edit = isset($_GET['edit']) && !empty($_GET['edit']);
        
        if ($is_edit) {
            $account_id = intval($_GET['edit']);
            $result = $bank_account->update_account($account_id, $data);
            if (is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=hisab-add-bank-account&edit=' . $account_id . '&error=' . urlencode($result->get_error_message())));
            } else {
                wp_redirect(admin_url('admin.php?page=hisab-bank-accounts&updated=1'));
            }
        } else {
            $result = $bank_account->create_account($data);
            if (is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=hisab-add-bank-account&error=' . urlencode($result->get_error_message())));
            } else {
                wp_redirect(admin_url('admin.php?page=hisab-bank-accounts&created=1'));
            }
        }
        exit;
    }
    
    /**
     * Handle bank transaction form submission
     */
    private function handle_bank_transaction_submission() {
        if (!class_exists('HisabBankTransaction') || !class_exists('HisabBankAccount')) {
            return;
        }
        
        $bank_transaction = new HisabBankTransaction();
        $bank_account = new HisabBankAccount();
        
        $nonce = sanitize_text_field($_POST['_wpnonce']);
        if (!wp_verify_nonce($nonce, 'hisab_bank_transaction')) {
            wp_redirect(admin_url('admin.php?page=hisab-add-bank-transaction&error=security'));
            exit;
        }
        
        // Get account ID from form
        $account_id = isset($_POST['account_id']) ? intval($_POST['account_id']) : 0;
        if ($account_id <= 0) {
            wp_redirect(admin_url('admin.php?page=hisab-add-bank-transaction&error=' . urlencode('Please select a bank account.')));
            exit;
        }
        
        // Verify account exists
        $account = $bank_account->get_account($account_id);
        if (!$account) {
            wp_redirect(admin_url('admin.php?page=hisab-add-bank-transaction&error=' . urlencode('Selected bank account not found.')));
            exit;
        }
        
        $data = array(
            'account_id' => $account_id,
            'transaction_type' => sanitize_text_field($_POST['transaction_type']),
            'amount' => floatval($_POST['amount']),
            'currency' => $account->currency, // Use account currency
            'description' => sanitize_textarea_field($_POST['description']),
            'reference_number' => sanitize_text_field($_POST['reference_number']),
            'phone_pay_reference' => sanitize_text_field($_POST['phone_pay_reference']),
            'transaction_date' => sanitize_text_field($_POST['transaction_date'])
        );
        
        // Check if editing
        $is_edit = isset($_GET['edit']) && !empty($_GET['edit']);
        
        if ($is_edit) {
            $transaction_id = intval($_GET['edit']);
            $result = $bank_transaction->update_transaction($transaction_id, $data);
            if (is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=hisab-add-bank-transaction&edit=' . $transaction_id . '&error=' . urlencode($result->get_error_message())));
            } else {
                wp_redirect(admin_url('admin.php?page=hisab-bank-transactions&account=' . $account_id . '&updated=1'));
            }
        } else {
            $result = $bank_transaction->create_transaction($data);
            if (is_wp_error($result)) {
                wp_redirect(admin_url('admin.php?page=hisab-add-bank-transaction&error=' . urlencode($result->get_error_message())));
            } else {
                wp_redirect(admin_url('admin.php?page=hisab-bank-transactions&account=' . $account_id . '&created=1'));
            }
        }
        exit;
    }
}