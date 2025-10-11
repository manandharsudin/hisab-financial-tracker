<?php
/**
 * Bank Account Management Class
 * 
 * Handles all bank account related operations including CRUD operations,
 * balance calculations, and account management.
 */

if (!defined('ABSPATH')) {
    exit;
}

class HisabBankAccount {
    
    private $table_bank_accounts;
    private $table_bank_transactions;
    
    public function __construct() {
        global $wpdb;
        $this->table_bank_accounts = $wpdb->prefix . 'hisab_bank_accounts';
        $this->table_bank_transactions = $wpdb->prefix . 'hisab_bank_transactions';
    }
    
    /**
     * Create a new bank account
     */
    public function create_account($data) {
        global $wpdb;
        
        $defaults = array(
            'account_name' => '',
            'bank_name' => '',
            'account_number' => '',
            'account_type' => 'savings',
            'currency' => 'NPR',
            'initial_balance' => 0.00,
            'current_balance' => 0.00,
            'is_active' => 1,
            'user_id' => get_current_user_id()
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Validate required fields
        if (empty($data['account_name']) || empty($data['bank_name'])) {
            return new WP_Error('missing_fields', __('Account name and bank name are required.', 'hisab-financial-tracker'));
        }
        
        // Validate currency
        if (!in_array($data['currency'], ['NPR', 'USD'])) {
            return new WP_Error('invalid_currency', __('Invalid currency. Must be NPR or USD.', 'hisab-financial-tracker'));
        }
        
        // Validate account type
        if (!in_array($data['account_type'], ['savings', 'current', 'credit_card', 'fixed_deposit', 'loan'])) {
            return new WP_Error('invalid_account_type', __('Invalid account type.', 'hisab-financial-tracker'));
        }
        
        // Set current balance to initial balance
        $data['current_balance'] = $data['initial_balance'];
        
        $result = $wpdb->insert(
            $this->table_bank_accounts,
            $data,
            array(
                '%s', // account_name
                '%s', // bank_name
                '%s', // account_number
                '%s', // account_type
                '%s', // currency
                '%f', // initial_balance
                '%f', // current_balance
                '%d', // is_active
                '%d'  // user_id
            )
        );
        
        if ($result === false) {
            return new WP_Error('db_error', __('Failed to create bank account.', 'hisab-financial-tracker'));
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get bank account by ID
     */
    public function get_account($id) {
        global $wpdb;
        
        $account = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_bank_accounts} WHERE id = %d",
            $id
        ));
        
        return $account;
    }
    
    /**
     * Get all bank accounts
     */
    public function get_all_accounts($filters = array()) {
        global $wpdb;
        
        $where_conditions = array('1=1');
        $where_values = array();
        
        // Filter by currency
        if (!empty($filters['currency'])) {
            $where_conditions[] = 'currency = %s';
            $where_values[] = $filters['currency'];
        }
        
        // Filter by account type
        if (!empty($filters['account_type'])) {
            $where_conditions[] = 'account_type = %s';
            $where_values[] = $filters['account_type'];
        }
        
        // Filter by active status
        if (isset($filters['is_active'])) {
            $where_conditions[] = 'is_active = %d';
            $where_values[] = $filters['is_active'];
        }
        
        // Filter by user
        if (!empty($filters['user_id'])) {
            $where_conditions[] = 'user_id = %d';
            $where_values[] = $filters['user_id'];
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $query = "SELECT * FROM {$this->table_bank_accounts} WHERE {$where_clause} ORDER BY account_name ASC";
        
        if (!empty($where_values)) {
            $query = $wpdb->prepare($query, $where_values);
        }
        
        $accounts = $wpdb->get_results($query);
        
        return $accounts;
    }
    
    /**
     * Update bank account
     */
    public function update_account($id, $data) {
        global $wpdb;
        
        // Validate currency if provided
        if (isset($data['currency']) && !in_array($data['currency'], ['NPR', 'USD'])) {
            return new WP_Error('invalid_currency', __('Invalid currency. Must be NPR or USD.', 'hisab-financial-tracker'));
        }
        
        // Validate account type if provided
        if (isset($data['account_type']) && !in_array($data['account_type'], ['savings', 'current', 'credit_card', 'fixed_deposit', 'loan'])) {
            return new WP_Error('invalid_account_type', __('Invalid account type.', 'hisab-financial-tracker'));
        }
        
        $result = $wpdb->update(
            $this->table_bank_accounts,
            $data,
            array('id' => $id),
            null,
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', __('Failed to update bank account.', 'hisab-financial-tracker'));
        }
        
        return true;
    }
    
    /**
     * Delete bank account
     */
    public function delete_account($id) {
        global $wpdb;
        
        // Check if account has transactions
        $transaction_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_bank_transactions} WHERE account_id = %d",
            $id
        ));
        
        if ($transaction_count > 0) {
            return new WP_Error('has_transactions', __('Cannot delete account with existing transactions.', 'hisab-financial-tracker'));
        }
        
        $result = $wpdb->delete(
            $this->table_bank_accounts,
            array('id' => $id),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', __('Failed to delete bank account.', 'hisab-financial-tracker'));
        }
        
        return true;
    }
    
    /**
     * Update account balance
     */
    public function update_balance($account_id) {
        global $wpdb;
        
        // Calculate current balance from transactions
        $balance = $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(
                CASE 
                    WHEN transaction_type IN ('deposit', 'transfer_in') THEN amount
                    WHEN transaction_type IN ('withdrawal', 'phone_pay', 'transfer_out') THEN -amount
                    ELSE 0
                END
            ), 0) FROM {$this->table_bank_transactions} WHERE account_id = %d",
            $account_id
        ));
        
        // Get initial balance
        $initial_balance = $wpdb->get_var($wpdb->prepare(
            "SELECT initial_balance FROM {$this->table_bank_accounts} WHERE id = %d",
            $account_id
        ));
        
        $current_balance = $initial_balance + $balance;
        
        // Update current balance
        $result = $wpdb->update(
            $this->table_bank_accounts,
            array('current_balance' => $current_balance),
            array('id' => $account_id),
            array('%f'),
            array('%d')
        );
        
        return $current_balance;
    }
    
    /**
     * Get account balance
     */
    public function get_balance($account_id) {
        $account = $this->get_account($account_id);
        return $account ? $account->current_balance : 0;
    }
    
    /**
     * Get accounts by currency
     */
    public function get_accounts_by_currency($currency) {
        return $this->get_all_accounts(array('currency' => $currency, 'is_active' => 1));
    }
    
    /**
     * Get account summary
     */
    public function get_account_summary($account_id) {
        global $wpdb;
        
        $account = $this->get_account($account_id);
        if (!$account) {
            return null;
        }
        
        // Get transaction counts by type
        $transaction_counts = $wpdb->get_results($wpdb->prepare(
            "SELECT transaction_type, COUNT(*) as count, SUM(amount) as total 
             FROM {$this->table_bank_transactions} 
             WHERE account_id = %d 
             GROUP BY transaction_type",
            $account_id
        ));
        
        // Get recent transactions
        $recent_transactions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_bank_transactions} 
             WHERE account_id = %d 
             ORDER BY transaction_date DESC, created_at DESC 
             LIMIT 5",
            $account_id
        ));
        
        return array(
            'account' => $account,
            'transaction_counts' => $transaction_counts,
            'recent_transactions' => $recent_transactions
        );
    }
}
