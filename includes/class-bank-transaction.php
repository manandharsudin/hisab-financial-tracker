<?php
/**
 * Bank Transaction Management Class
 * 
 * Handles all bank transaction operations including deposits, withdrawals,
 * phone pay transactions, and transfers between accounts.
 */

if (!defined('ABSPATH')) {
    exit;
}

class HisabBankTransaction {
    
    private $table_bank_transactions;
    private $table_bank_accounts;
    
    public function __construct() {
        global $wpdb;
        $this->table_bank_transactions = $wpdb->prefix . 'hisab_bank_transactions';
        $this->table_bank_accounts = $wpdb->prefix . 'hisab_bank_accounts';
    }
    
    /**
     * Create a new bank transaction
     */
    public function create_transaction($data) {
        global $wpdb;
        
        $defaults = array(
            'account_id' => 0,
            'transaction_type' => 'deposit',
            'amount' => 0.00,
            'currency' => 'NPR',
            'description' => '',
            'reference_number' => '',
            'phone_pay_reference' => '',
            'transaction_date' => current_time('Y-m-d'),
            'bs_year' => null,
            'bs_month' => null,
            'bs_day' => null,
            'created_by' => get_current_user_id()
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Validate required fields
        if (empty($data['account_id']) || $data['amount'] <= 0) {
            return new WP_Error('missing_fields', __('Account ID and amount are required.', 'hisab-financial-tracker'));
        }
        
        // Validate transaction type
        if (!in_array($data['transaction_type'], ['deposit', 'withdrawal', 'phone_pay', 'transfer_in', 'transfer_out'])) {
            return new WP_Error('invalid_transaction_type', __('Invalid transaction type.', 'hisab-financial-tracker'));
        }
        
        // Validate currency
        if (!in_array($data['currency'], ['NPR', 'USD'])) {
            return new WP_Error('invalid_currency', __('Invalid currency. Must be NPR or USD.', 'hisab-financial-tracker'));
        }
        
        // Check if account exists and get currency
        $account = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_bank_accounts} WHERE id = %d",
            $data['account_id']
        ));
        
        if (!$account) {
            return new WP_Error('account_not_found', __('Bank account not found.', 'hisab-financial-tracker'));
        }
        
        // Validate currency matches account currency
        if ($data['currency'] !== $account->currency) {
            return new WP_Error('currency_mismatch', __('Transaction currency must match account currency.', 'hisab-financial-tracker'));
        }
        
        // Check sufficient balance for withdrawals
        if (in_array($data['transaction_type'], ['withdrawal', 'phone_pay', 'transfer_out'])) {
            $current_balance = $account->current_balance;
            if ($current_balance < $data['amount']) {
                return new WP_Error('insufficient_balance', __('Insufficient balance for this transaction.', 'hisab-financial-tracker'));
            }
        }
        
        // Handle BS date conversion if needed
        if (!empty($data['bs_year']) && !empty($data['bs_month']) && !empty($data['bs_day'])) {
            $bs_year = intval($data['bs_year']);
            $bs_month = intval($data['bs_month']);
            $bs_day = intval($data['bs_day']);
            
            $data['bs_year'] = $bs_year;
            $data['bs_month'] = $bs_month;
            $data['bs_day'] = $bs_day;
        } else {
            // Convert AD to BS
            if (!empty($data['transaction_date'])) {
                $ad_parts = explode('-', $data['transaction_date']);
                if (count($ad_parts) === 3) {
                    $bs_date = HisabNepaliDate::ad_to_bs($ad_parts[0], $ad_parts[1], $ad_parts[2]);
                    if ($bs_date) {
                        $data['bs_year'] = $bs_date['year'];
                        $data['bs_month'] = $bs_date['month'];
                        $data['bs_day'] = $bs_date['day'];
                    }
                }
            }
        }
        
        $result = $wpdb->insert(
            $this->table_bank_transactions,
            $data,
            array(
                '%d', // account_id
                '%s', // transaction_type
                '%f', // amount
                '%s', // currency
                '%s', // description
                '%s', // reference_number
                '%s', // phone_pay_reference
                '%s', // transaction_date
                '%d', // bs_year
                '%d', // bs_month
                '%d', // bs_day
                '%d'  // created_by
            )
        );
        
        if ($result === false) {
            return new WP_Error('db_error', __('Failed to create bank transaction.', 'hisab-financial-tracker'));
        }
        
        $transaction_id = $wpdb->insert_id;
        
        // Update account balance
        $this->update_account_balance($data['account_id']);
        
        return $transaction_id;
    }
    
    /**
     * Get bank transaction by ID
     */
    public function get_transaction($id) {
        global $wpdb;
        
        $transaction = $wpdb->get_row($wpdb->prepare(
            "SELECT bt.*, ba.account_name, ba.bank_name, ba.currency as account_currency 
             FROM {$this->table_bank_transactions} bt
             LEFT JOIN {$this->table_bank_accounts} ba ON bt.account_id = ba.id
             WHERE bt.id = %d",
            $id
        ));
        
        return $transaction;
    }
    
    /**
     * Get all bank transactions for an account
     */
    public function get_account_transactions($account_id, $filters = array()) {
        global $wpdb;
        
        $where_conditions = array('bt.account_id = %d');
        $where_values = array($account_id);
        
        // Filter by transaction type
        if (!empty($filters['transaction_type'])) {
            $where_conditions[] = 'bt.transaction_type = %s';
            $where_values[] = $filters['transaction_type'];
        }
        
        // Filter by date range
        if (!empty($filters['start_date'])) {
            $where_conditions[] = 'bt.transaction_date >= %s';
            $where_values[] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $where_conditions[] = 'bt.transaction_date <= %s';
            $where_values[] = $filters['end_date'];
        }
        
        // Filter by amount range
        if (!empty($filters['min_amount'])) {
            $where_conditions[] = 'bt.amount >= %f';
            $where_values[] = $filters['min_amount'];
        }
        
        if (!empty($filters['max_amount'])) {
            $where_conditions[] = 'bt.amount <= %f';
            $where_values[] = $filters['max_amount'];
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $query = "SELECT bt.*, ba.account_name, ba.bank_name, ba.currency as account_currency 
                  FROM {$this->table_bank_transactions} bt
                  LEFT JOIN {$this->table_bank_accounts} ba ON bt.account_id = ba.id
                  WHERE {$where_clause}
                  ORDER BY bt.transaction_date DESC, bt.created_at DESC";
        
        if (!empty($filters['limit'])) {
            $query .= " LIMIT " . intval($filters['limit']);
        }
        
        $transactions = $wpdb->get_results($wpdb->prepare($query, $where_values));
        
        return $transactions;
    }
    
    /**
     * Get all bank transactions with pagination
     */
    public function get_all_transactions($filters = array(), $page = 1, $per_page = 20) {
        global $wpdb;
        
        $where_conditions = array('1=1');
        $where_values = array();
        
        // Filter by account
        if (!empty($filters['account_id'])) {
            $where_conditions[] = 'bt.account_id = %d';
            $where_values[] = $filters['account_id'];
        }
        
        // Filter by transaction type
        if (!empty($filters['transaction_type'])) {
            $where_conditions[] = 'bt.transaction_type = %s';
            $where_values[] = $filters['transaction_type'];
        }
        
        // Filter by currency
        if (!empty($filters['currency'])) {
            $where_conditions[] = 'bt.currency = %s';
            $where_values[] = $filters['currency'];
        }
        
        // Filter by date range
        if (!empty($filters['start_date'])) {
            $where_conditions[] = 'bt.transaction_date >= %s';
            $where_values[] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $where_conditions[] = 'bt.transaction_date <= %s';
            $where_values[] = $filters['end_date'];
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        // Get total count
        $count_query = "SELECT COUNT(*) FROM {$this->table_bank_transactions} bt WHERE {$where_clause}";
        if (!empty($where_values)) {
            $count_query = $wpdb->prepare($count_query, $where_values);
        }
        $total = $wpdb->get_var($count_query);
        
        // Get transactions with pagination
        $offset = ($page - 1) * $per_page;
        $query = "SELECT bt.*, ba.account_name, ba.bank_name, ba.currency as account_currency 
                  FROM {$this->table_bank_transactions} bt
                  LEFT JOIN {$this->table_bank_accounts} ba ON bt.account_id = ba.id
                  WHERE {$where_clause}
                  ORDER BY bt.transaction_date DESC, bt.created_at DESC
                  LIMIT %d OFFSET %d";
        
        $where_values[] = $per_page;
        $where_values[] = $offset;
        
        $transactions = $wpdb->get_results($wpdb->prepare($query, $where_values));
        
        return array(
            'transactions' => $transactions,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        );
    }
    
    /**
     * Calculate effective balance for transaction updates
     * This adds back the original transaction amount to get the effective balance
     */
    public function calculate_effective_balance($account_id, $original_transaction) {
        global $wpdb;
        
        // Get current account balance
        $account = $wpdb->get_row($wpdb->prepare(
            "SELECT current_balance FROM {$this->table_bank_accounts} WHERE id = %d",
            $account_id
        ));
        
        if (!$account) {
            return 0;
        }
        
        $current_balance = $account->current_balance;
        
        // For debit transactions (withdrawals, phone_pay, transfer_out), add back the original amount
        if (in_array($original_transaction->transaction_type, ['withdrawal', 'phone_pay', 'transfer_out'])) {
            return $current_balance + $original_transaction->amount;
        }
        
        // For credit transactions (deposits, transfer_in), subtract the original amount
        if (in_array($original_transaction->transaction_type, ['deposit', 'transfer_in'])) {
            return $current_balance - $original_transaction->amount;
        }
        
        return $current_balance;
    }
    
    /**
     * Update bank transaction
     */
    public function update_transaction($id, $data) {
        global $wpdb;
        
        // Get the original transaction first
        $original_transaction = $this->get_transaction($id);
        if (!$original_transaction) {
            return new WP_Error('transaction_not_found', __('Transaction not found.', 'hisab-financial-tracker'));
        }
        
        // Validate transaction type if provided
        if (isset($data['transaction_type']) && !in_array($data['transaction_type'], ['deposit', 'withdrawal', 'phone_pay', 'transfer_in', 'transfer_out'])) {
            return new WP_Error('invalid_transaction_type', __('Invalid transaction type.', 'hisab-financial-tracker'));
        }
        
        // Validate currency if provided
        if (isset($data['currency']) && !in_array($data['currency'], ['NPR', 'USD'])) {
            return new WP_Error('invalid_currency', __('Invalid currency. Must be NPR or USD.', 'hisab-financial-tracker'));
        }
        
        // Context-aware balance validation for withdrawals
        if (isset($data['amount']) || isset($data['transaction_type'])) {
            $new_transaction_type = isset($data['transaction_type']) ? $data['transaction_type'] : $original_transaction->transaction_type;
            $new_amount = isset($data['amount']) ? $data['amount'] : $original_transaction->amount;
            
            // Check sufficient balance for debit transactions
            if (in_array($new_transaction_type, ['withdrawal', 'phone_pay', 'transfer_out'])) {
                $effective_balance = $this->calculate_effective_balance($original_transaction->account_id, $original_transaction);
                
                if ($effective_balance < $new_amount) {
                    return new WP_Error('insufficient_balance', 
                        sprintf(__('Insufficient balance for this transaction. Available: %s %s, Required: %s %s', 'hisab-financial-tracker'),
                            number_format($effective_balance, 2),
                            $original_transaction->currency,
                            number_format($new_amount, 2),
                            $original_transaction->currency
                        )
                    );
                }
            }
        }
        
        // Handle BS date conversion if needed
        if (isset($data['bs_year']) || isset($data['bs_month']) || isset($data['bs_day'])) {
            if (!empty($data['bs_year']) && !empty($data['bs_month']) && !empty($data['bs_day'])) {
                $bs_year = intval($data['bs_year']);
                $bs_month = intval($data['bs_month']);
                $bs_day = intval($data['bs_day']);
                
                $data['bs_year'] = $bs_year;
                $data['bs_month'] = $bs_month;
                $data['bs_day'] = $bs_day;
            }
        } else if (isset($data['transaction_date'])) {
            // Convert AD to BS if transaction_date is updated
            $ad_parts = explode('-', $data['transaction_date']);
            if (count($ad_parts) === 3) {
                $bs_date = HisabNepaliDate::ad_to_bs($ad_parts[0], $ad_parts[1], $ad_parts[2]);
                if ($bs_date) {
                    $data['bs_year'] = $bs_date['year'];
                    $data['bs_month'] = $bs_date['month'];
                    $data['bs_day'] = $bs_date['day'];
                }
            }
        }
        
        $result = $wpdb->update(
            $this->table_bank_transactions,
            $data,
            array('id' => $id),
            null,
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', __('Failed to update bank transaction.', 'hisab-financial-tracker'));
        }
        
        // Update account balance if amount or type changed
        if (isset($data['amount']) || isset($data['transaction_type'])) {
            $transaction = $this->get_transaction($id);
            if ($transaction) {
                $this->update_account_balance($transaction->account_id);
            }
        }
        
        return true;
    }
    
    /**
     * Delete bank transaction
     */
    public function delete_transaction($id) {
        global $wpdb;
        
        // Get transaction details before deletion
        $transaction = $this->get_transaction($id);
        if (!$transaction) {
            return new WP_Error('transaction_not_found', __('Transaction not found.', 'hisab-financial-tracker'));
        }
        
        $result = $wpdb->delete(
            $this->table_bank_transactions,
            array('id' => $id),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', __('Failed to delete bank transaction.', 'hisab-financial-tracker'));
        }
        
        // Update account balance after deletion
        $this->update_account_balance($transaction->account_id);
        
        return true;
    }
    
    /**
     * Update account balance after transaction
     */
    private function update_account_balance($account_id) {
        $bank_account = new HisabBankAccount();
        return $bank_account->update_balance($account_id);
    }
    
    /**
     * Get transaction summary for an account
     */
    public function get_account_summary($account_id, $start_date = null, $end_date = null) {
        global $wpdb;
        
        $where_conditions = array('account_id = %d');
        $where_values = array($account_id);
        
        if ($start_date) {
            $where_conditions[] = 'transaction_date >= %s';
            $where_values[] = $start_date;
        }
        
        if ($end_date) {
            $where_conditions[] = 'transaction_date <= %s';
            $where_values[] = $end_date;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $summary = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                transaction_type,
                COUNT(*) as count,
                SUM(amount) as total_amount,
                AVG(amount) as avg_amount,
                MIN(amount) as min_amount,
                MAX(amount) as max_amount
             FROM {$this->table_bank_transactions} 
             WHERE {$where_clause}
             GROUP BY transaction_type",
            $where_values
        ));
        
        return $summary;
    }
    
    /**
     * Create phone pay transaction
     */
    public function create_phone_pay_transaction($account_id, $amount, $description, $phone_pay_reference, $transaction_date = null) {
        $data = array(
            'account_id' => $account_id,
            'transaction_type' => 'phone_pay',
            'amount' => $amount,
            'description' => $description,
            'phone_pay_reference' => $phone_pay_reference,
            'transaction_date' => $transaction_date ?: current_time('Y-m-d')
        );
        
        return $this->create_transaction($data);
    }
    
    /**
     * Create transfer between accounts
     */
    public function create_transfer($from_account_id, $to_account_id, $amount, $description, $transaction_date = null) {
        $transaction_date = $transaction_date ?: current_time('Y-m-d');
        
        // Create withdrawal from source account
        $withdrawal_data = array(
            'account_id' => $from_account_id,
            'transaction_type' => 'transfer_out',
            'amount' => $amount,
            'description' => $description . ' (Transfer Out)',
            'transaction_date' => $transaction_date
        );
        
        $withdrawal_id = $this->create_transaction($withdrawal_data);
        if (is_wp_error($withdrawal_id)) {
            return $withdrawal_id;
        }
        
        // Create deposit to destination account
        $deposit_data = array(
            'account_id' => $to_account_id,
            'transaction_type' => 'transfer_in',
            'amount' => $amount,
            'description' => $description . ' (Transfer In)',
            'transaction_date' => $transaction_date
        );
        
        $deposit_id = $this->create_transaction($deposit_data);
        if (is_wp_error($deposit_id)) {
            // If deposit fails, delete the withdrawal
            $this->delete_transaction($withdrawal_id);
            return $deposit_id;
        }
        
        return array(
            'withdrawal_id' => $withdrawal_id,
            'deposit_id' => $deposit_id
        );
    }
}
