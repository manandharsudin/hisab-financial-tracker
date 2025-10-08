<?php
/**
 * Import/Export functionality for Hisab Financial Tracker
 */

if (!defined('ABSPATH')) {
    exit;
}

class HisabImportExport {
    
    private $database;
    private $bank_account;
    private $bank_transaction;
    
    public function __construct() {
        $this->database = new HisabDatabase();
        $this->bank_account = new HisabBankAccount();
        $this->bank_transaction = new HisabBankTransaction();
    }
    
    /**
     * Export data to JSON format
     */
    public function export_all_data($export_type = 'all') {
        $export_data = array(
            'export_info' => array(
                'export_date' => current_time('Y-m-d H:i:s'),
                'plugin_version' => HISAB_VERSION,
                'wordpress_version' => get_bloginfo('version'),
                'site_url' => get_site_url(),
                'exported_by' => get_current_user_id(),
                'export_type' => $export_type
            )
        );
        
        // Add data based on export type
        if ($export_type === 'all' || $export_type === 'categories') {
            $export_data['categories'] = $this->export_categories();
        }
        
        if ($export_type === 'all' || $export_type === 'owners') {
            $export_data['owners'] = $this->export_owners();
        }
        
        if ($export_type === 'all' || $export_type === 'bank_accounts') {
            $export_data['bank_accounts'] = $this->export_bank_accounts();
        }
        
        if ($export_type === 'all' || $export_type === 'transactions') {
            $export_data['transactions'] = $this->export_transactions();
        }
        
        if ($export_type === 'all' || $export_type === 'bank_transactions') {
            $export_data['bank_transactions'] = $this->export_bank_transactions();
        }
        
        if ($export_type === 'all' || $export_type === 'transactions') {
            $export_data['transaction_details'] = $this->export_transaction_details();
        }
        
        return $export_data;
    }
    
    
    /**
     * Import data from JSON file
     */
    public function import_from_json($json_data, $options = array()) {
        $default_options = array(
            'import_categories' => true,
            'import_owners' => true,
            'import_bank_accounts' => true,
            'import_transactions' => true,
            'import_bank_transactions' => true,
            'import_transaction_details' => true,
            'skip_duplicates' => true,
            'update_existing' => false
        );
        
        $options = wp_parse_args($options, $default_options);
        $results = array(
            'success' => true,
            'imported' => array(),
            'skipped' => array(),
            'errors' => array()
        );
        
        try {
            // Import categories first
            if ($options['import_categories'] && isset($json_data['categories'])) {
                $category_results = $this->import_categories($json_data['categories'], $options);
                $results['imported']['categories'] = $category_results['imported'];
                $results['skipped']['categories'] = $category_results['skipped'];
                $results['errors']['categories'] = $category_results['errors'];
            }
            
            // Import owners
            if ($options['import_owners'] && isset($json_data['owners'])) {
                $owner_results = $this->import_owners($json_data['owners'], $options);
                $results['imported']['owners'] = $owner_results['imported'];
                $results['skipped']['owners'] = $owner_results['skipped'];
                $results['errors']['owners'] = $owner_results['errors'];
            }
            
            // Import bank accounts
            if ($options['import_bank_accounts'] && isset($json_data['bank_accounts'])) {
                $bank_account_results = $this->import_bank_accounts($json_data['bank_accounts'], $options);
                $results['imported']['bank_accounts'] = $bank_account_results['imported'];
                $results['skipped']['bank_accounts'] = $bank_account_results['skipped'];
                $results['errors']['bank_accounts'] = $bank_account_results['errors'];
            }
            
            // Import transactions
            if ($options['import_transactions'] && isset($json_data['transactions'])) {
                $transaction_results = $this->import_transactions($json_data['transactions'], $options);
                $results['imported']['transactions'] = $transaction_results['imported'];
                $results['skipped']['transactions'] = $transaction_results['skipped'];
                $results['errors']['transactions'] = $transaction_results['errors'];
            }
            
            // Import bank transactions
            if ($options['import_bank_transactions'] && isset($json_data['bank_transactions'])) {
                $bank_transaction_results = $this->import_bank_transactions($json_data['bank_transactions'], $options);
                $results['imported']['bank_transactions'] = $bank_transaction_results['imported'];
                $results['skipped']['bank_transactions'] = $bank_transaction_results['skipped'];
                $results['errors']['bank_transactions'] = $bank_transaction_results['errors'];
            }
            
            // Import transaction details
            if ($options['import_transaction_details'] && isset($json_data['transaction_details'])) {
                $details_results = $this->import_transaction_details($json_data['transaction_details'], $options);
                $results['imported']['transaction_details'] = $details_results['imported'];
                $results['skipped']['transaction_details'] = $details_results['skipped'];
                $results['errors']['transaction_details'] = $details_results['errors'];
            }
            
        } catch (Exception $e) {
            $results['success'] = false;
            $results['errors']['general'] = $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Export categories
     */
    private function export_categories() {
        global $wpdb;
        
        $sql = "SELECT * FROM {$wpdb->prefix}hisab_categories ORDER BY type, name";
        return $wpdb->get_results($sql, ARRAY_A);
    }
    
    /**
     * Export owners
     */
    private function export_owners() {
        global $wpdb;
        
        $sql = "SELECT * FROM {$wpdb->prefix}hisab_owners ORDER BY name";
        return $wpdb->get_results($sql, ARRAY_A);
    }
    
    /**
     * Export bank accounts
     */
    private function export_bank_accounts() {
        global $wpdb;
        
        $sql = "SELECT * FROM {$wpdb->prefix}hisab_bank_accounts ORDER BY account_name";
        return $wpdb->get_results($sql, ARRAY_A);
    }
    
    /**
     * Export transactions with related data
     */
    private function export_transactions() {
        global $wpdb;
        
        $sql = "
            SELECT t.*, 
                   c.name as category_name, 
                   o.name as owner_name,
                   ba.account_name as bank_account_name
            FROM {$wpdb->prefix}hisab_transactions t
            LEFT JOIN {$wpdb->prefix}hisab_categories c ON t.category_id = c.id
            LEFT JOIN {$wpdb->prefix}hisab_owners o ON t.owner_id = o.id
            LEFT JOIN {$wpdb->prefix}hisab_bank_accounts ba ON t.bank_account_id = ba.id
            ORDER BY t.transaction_date DESC, t.id DESC
        ";
        
        return $wpdb->get_results($sql, ARRAY_A);
    }
    
    /**
     * Export bank transactions with related data
     */
    private function export_bank_transactions() {
        global $wpdb;
        
        $sql = "
            SELECT bt.*, 
                   ba.account_name,
                   ba.bank_name
            FROM {$wpdb->prefix}hisab_bank_transactions bt
            LEFT JOIN {$wpdb->prefix}hisab_bank_accounts ba ON bt.account_id = ba.id
            ORDER BY bt.transaction_date DESC, bt.id DESC
        ";
        
        return $wpdb->get_results($sql, ARRAY_A);
    }
    
    /**
     * Export transaction details
     */
    private function export_transaction_details() {
        global $wpdb;
        
        $sql = "
            SELECT td.*, t.type as transaction_type, t.transaction_date
            FROM {$wpdb->prefix}hisab_transaction_details td
            LEFT JOIN {$wpdb->prefix}hisab_transactions t ON td.transaction_id = t.id
            ORDER BY t.transaction_date DESC, td.id
        ";
        
        return $wpdb->get_results($sql, ARRAY_A);
    }
    
    /**
     * Import categories
     */
    private function import_categories($categories, $options) {
        global $wpdb;
        
        $results = array('imported' => 0, 'skipped' => 0, 'errors' => array());
        $existing_categories = $this->get_existing_categories();
        
        foreach ($categories as $category) {
            try {
                // Check if category already exists
                if ($options['skip_duplicates'] && $this->category_exists($category, $existing_categories)) {
                    $results['skipped']++;
                    continue;
                }
                
                // Prepare category data
                $category_data = array(
                    'name' => sanitize_text_field($category['name']),
                    'type' => sanitize_text_field($category['type']),
                    'color' => sanitize_hex_color($category['color']),
                    'created_at' => current_time('mysql')
                );
                
                // Insert category
                $result = $wpdb->insert(
                    $wpdb->prefix . 'hisab_categories',
                    $category_data,
                    array('%s', '%s', '%s', '%s')
                );
                
                if ($result !== false) {
                    $results['imported']++;
                } else {
                    $results['errors'][] = 'Failed to import category: ' . $category['name'];
                }
                
            } catch (Exception $e) {
                $results['errors'][] = 'Error importing category ' . $category['name'] . ': ' . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    /**
     * Import owners
     */
    private function import_owners($owners, $options) {
        global $wpdb;
        
        $results = array('imported' => 0, 'skipped' => 0, 'errors' => array());
        $existing_owners = $this->get_existing_owners();
        
        foreach ($owners as $owner) {
            try {
                // Check if owner already exists
                if ($options['skip_duplicates'] && $this->owner_exists($owner, $existing_owners)) {
                    $results['skipped']++;
                    continue;
                }
                
                // Prepare owner data
                $owner_data = array(
                    'name' => sanitize_text_field($owner['name']),
                    'color' => sanitize_hex_color($owner['color']),
                    'created_at' => current_time('mysql')
                );
                
                // Insert owner
                $result = $wpdb->insert(
                    $wpdb->prefix . 'hisab_owners',
                    $owner_data,
                    array('%s', '%s', '%s')
                );
                
                if ($result !== false) {
                    $results['imported']++;
                } else {
                    $results['errors'][] = 'Failed to import owner: ' . $owner['name'];
                }
                
            } catch (Exception $e) {
                $results['errors'][] = 'Error importing owner ' . $owner['name'] . ': ' . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    /**
     * Import bank accounts
     */
    private function import_bank_accounts($bank_accounts, $options) {
        global $wpdb;
        
        $results = array('imported' => 0, 'skipped' => 0, 'errors' => array());
        $existing_accounts = $this->get_existing_bank_accounts();
        
        foreach ($bank_accounts as $account) {
            try {
                // Check if account already exists
                if ($options['skip_duplicates'] && $this->bank_account_exists($account, $existing_accounts)) {
                    $results['skipped']++;
                    continue;
                }
                
                // Prepare account data
                $account_data = array(
                    'account_name' => sanitize_text_field($account['account_name']),
                    'bank_name' => sanitize_text_field($account['bank_name']),
                    'account_number' => sanitize_text_field($account['account_number']),
                    'account_type' => sanitize_text_field($account['account_type']),
                    'currency' => sanitize_text_field($account['currency']),
                    'initial_balance' => floatval($account['initial_balance']),
                    'current_balance' => floatval($account['current_balance']),
                    'is_active' => intval($account['is_active']),
                    'user_id' => get_current_user_id(),
                    'created_at' => current_time('mysql')
                );
                
                // Insert bank account
                $result = $wpdb->insert(
                    $wpdb->prefix . 'hisab_bank_accounts',
                    $account_data,
                    array('%s', '%s', '%s', '%s', '%s', '%f', '%f', '%d', '%d', '%s')
                );
                
                if ($result !== false) {
                    $results['imported']++;
                } else {
                    $results['errors'][] = 'Failed to import bank account: ' . $account['account_name'];
                }
                
            } catch (Exception $e) {
                $results['errors'][] = 'Error importing bank account ' . $account['account_name'] . ': ' . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    /**
     * Import transactions
     */
    private function import_transactions($transactions, $options) {
        global $wpdb;
        
        $results = array('imported' => 0, 'skipped' => 0, 'errors' => array());
        $category_map = $this->get_category_map();
        $owner_map = $this->get_owner_map();
        $bank_account_map = $this->get_bank_account_map();
        
        foreach ($transactions as $transaction) {
            try {
                // Map category ID
                $category_id = null;
                if (!empty($transaction['category_name'])) {
                    $category_id = $this->find_category_id($transaction['category_name'], $transaction['type'], $category_map);
                }
                
                // Map owner ID
                $owner_id = null;
                if (!empty($transaction['owner_name'])) {
                    $owner_id = $this->find_owner_id($transaction['owner_name'], $owner_map);
                }
                
                // Map bank account ID
                $bank_account_id = null;
                if (!empty($transaction['bank_account_name'])) {
                    $bank_account_id = $this->find_bank_account_id($transaction['bank_account_name'], $bank_account_map);
                }
                
                // Prepare transaction data
                $transaction_data = array(
                    'type' => sanitize_text_field($transaction['type']),
                    'amount' => floatval($transaction['amount']),
                    'description' => sanitize_textarea_field($transaction['description']),
                    'category_id' => $category_id,
                    'owner_id' => $owner_id,
                    'payment_method' => sanitize_text_field($transaction['payment_method']),
                    'bank_account_id' => $bank_account_id,
                    'phone_pay_reference' => sanitize_text_field($transaction['phone_pay_reference']),
                    'transaction_tax' => floatval($transaction['transaction_tax']),
                    'transaction_discount' => floatval($transaction['transaction_discount']),
                    'transaction_date' => sanitize_text_field($transaction['transaction_date']),
                    'bs_year' => intval($transaction['bs_year']),
                    'bs_month' => intval($transaction['bs_month']),
                    'bs_day' => intval($transaction['bs_day']),
                    'created_at' => current_time('mysql')
                );
                
                // Insert transaction
                $result = $wpdb->insert(
                    $wpdb->prefix . 'hisab_transactions',
                    $transaction_data,
                    array('%s', '%f', '%s', '%d', '%d', '%s', '%d', '%s', '%f', '%f', '%s', '%d', '%d', '%d', '%s')
                );
                
                if ($result !== false) {
                    $results['imported']++;
                } else {
                    $results['errors'][] = 'Failed to import transaction: ' . $transaction['description'];
                }
                
            } catch (Exception $e) {
                $results['errors'][] = 'Error importing transaction: ' . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    /**
     * Import bank transactions
     */
    private function import_bank_transactions($bank_transactions, $options) {
        global $wpdb;
        
        $results = array('imported' => 0, 'skipped' => 0, 'errors' => array());
        $bank_account_map = $this->get_bank_account_map();
        
        foreach ($bank_transactions as $transaction) {
            try {
                // Map bank account ID
                $account_id = $this->find_bank_account_id($transaction['account_name'], $bank_account_map);
                
                if (!$account_id) {
                    $results['errors'][] = 'Bank account not found: ' . $transaction['account_name'];
                    continue;
                }
                
                // Prepare bank transaction data
                $transaction_data = array(
                    'account_id' => $account_id,
                    'transaction_type' => sanitize_text_field($transaction['transaction_type']),
                    'amount' => floatval($transaction['amount']),
                    'currency' => sanitize_text_field($transaction['currency']),
                    'description' => sanitize_textarea_field($transaction['description']),
                    'reference_number' => sanitize_text_field($transaction['reference_number']),
                    'phone_pay_reference' => sanitize_text_field($transaction['phone_pay_reference']),
                    'transaction_date' => sanitize_text_field($transaction['transaction_date']),
                    'created_at' => current_time('mysql')
                );
                
                // Insert bank transaction
                $result = $wpdb->insert(
                    $wpdb->prefix . 'hisab_bank_transactions',
                    $transaction_data,
                    array('%d', '%s', '%f', '%s', '%s', '%s', '%s', '%s', '%s')
                );
                
                if ($result !== false) {
                    $results['imported']++;
                } else {
                    $results['errors'][] = 'Failed to import bank transaction: ' . $transaction['description'];
                }
                
            } catch (Exception $e) {
                $results['errors'][] = 'Error importing bank transaction: ' . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    /**
     * Import transaction details
     */
    private function import_transaction_details($details, $options) {
        global $wpdb;
        
        $results = array('imported' => 0, 'skipped' => 0, 'errors' => array());
        $transaction_map = $this->get_transaction_map();
        
        foreach ($details as $detail) {
            try {
                // Map transaction ID
                $transaction_id = $this->find_transaction_id($detail, $transaction_map);
                
                if (!$transaction_id) {
                    $results['errors'][] = 'Transaction not found for detail: ' . $detail['item_name'];
                    continue;
                }
                
                // Prepare detail data
                $detail_data = array(
                    'transaction_id' => $transaction_id,
                    'item_name' => sanitize_text_field($detail['item_name']),
                    'rate' => floatval($detail['rate']),
                    'quantity' => floatval($detail['quantity']),
                    'total' => floatval($detail['total']),
                    'created_at' => current_time('mysql')
                );
                
                // Insert transaction detail
                $result = $wpdb->insert(
                    $wpdb->prefix . 'hisab_transaction_details',
                    $detail_data,
                    array('%d', '%s', '%f', '%f', '%f', '%s')
                );
                
                if ($result !== false) {
                    $results['imported']++;
                } else {
                    $results['errors'][] = 'Failed to import transaction detail: ' . $detail['item_name'];
                }
                
            } catch (Exception $e) {
                $results['errors'][] = 'Error importing transaction detail: ' . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    // Helper methods for mapping and checking existence
    
    private function get_existing_categories() {
        global $wpdb;
        $sql = "SELECT name, type FROM {$wpdb->prefix}hisab_categories";
        return $wpdb->get_results($sql, ARRAY_A);
    }
    
    private function get_existing_owners() {
        global $wpdb;
        $sql = "SELECT name FROM {$wpdb->prefix}hisab_owners";
        return $wpdb->get_results($sql, ARRAY_A);
    }
    
    private function get_existing_bank_accounts() {
        global $wpdb;
        $sql = "SELECT account_name, bank_name FROM {$wpdb->prefix}hisab_bank_accounts";
        return $wpdb->get_results($sql, ARRAY_A);
    }
    
    private function category_exists($category, $existing) {
        foreach ($existing as $existing_category) {
            if ($existing_category['name'] === $category['name'] && $existing_category['type'] === $category['type']) {
                return true;
            }
        }
        return false;
    }
    
    private function owner_exists($owner, $existing) {
        foreach ($existing as $existing_owner) {
            if ($existing_owner['name'] === $owner['name']) {
                return true;
            }
        }
        return false;
    }
    
    private function bank_account_exists($account, $existing) {
        foreach ($existing as $existing_account) {
            if ($existing_account['account_name'] === $account['account_name'] && 
                $existing_account['bank_name'] === $account['bank_name']) {
                return true;
            }
        }
        return false;
    }
    
    private function get_category_map() {
        global $wpdb;
        $sql = "SELECT id, name, type FROM {$wpdb->prefix}hisab_categories";
        $categories = $wpdb->get_results($sql, ARRAY_A);
        $map = array();
        foreach ($categories as $category) {
            $map[$category['type']][$category['name']] = $category['id'];
        }
        return $map;
    }
    
    private function get_owner_map() {
        global $wpdb;
        $sql = "SELECT id, name FROM {$wpdb->prefix}hisab_owners";
        $owners = $wpdb->get_results($sql, ARRAY_A);
        $map = array();
        foreach ($owners as $owner) {
            $map[$owner['name']] = $owner['id'];
        }
        return $map;
    }
    
    private function get_bank_account_map() {
        global $wpdb;
        $sql = "SELECT id, account_name FROM {$wpdb->prefix}hisab_bank_accounts";
        $accounts = $wpdb->get_results($sql, ARRAY_A);
        $map = array();
        foreach ($accounts as $account) {
            $map[$account['account_name']] = $account['id'];
        }
        return $map;
    }
    
    private function get_transaction_map() {
        global $wpdb;
        $sql = "SELECT id, description, amount, transaction_date FROM {$wpdb->prefix}hisab_transactions ORDER BY id DESC";
        $transactions = $wpdb->get_results($sql, ARRAY_A);
        $map = array();
        foreach ($transactions as $transaction) {
            $key = $transaction['description'] . '|' . $transaction['amount'] . '|' . $transaction['transaction_date'];
            $map[$key] = $transaction['id'];
        }
        return $map;
    }
    
    private function find_category_id($name, $type, $map) {
        return isset($map[$type][$name]) ? $map[$type][$name] : null;
    }
    
    private function find_owner_id($name, $map) {
        return isset($map[$name]) ? $map[$name] : null;
    }
    
    private function find_bank_account_id($name, $map) {
        return isset($map[$name]) ? $map[$name] : null;
    }
    
    private function find_transaction_id($detail, $map) {
        $key = $detail['transaction_description'] . '|' . $detail['transaction_amount'] . '|' . $detail['transaction_date'];
        return isset($map[$key]) ? $map[$key] : null;
    }
}
