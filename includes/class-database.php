<?php
/**
 * Database management class for Hisab Financial Tracker
 */

if (!defined('ABSPATH')) {
    exit;
}

class HisabDatabase {
    
    private $table_transactions;
    private $table_categories;
    private $table_owners;
    private $table_transaction_details;
    private $table_bank_accounts;
    private $table_bank_transactions;
    
    public function __construct() {
        global $wpdb;
        $this->table_transactions = $wpdb->prefix . 'hisab_transactions';
        $this->table_categories = $wpdb->prefix . 'hisab_categories';
        $this->table_owners = $wpdb->prefix . 'hisab_owners';
        $this->table_transaction_details = $wpdb->prefix . 'hisab_transaction_details';
        $this->table_bank_accounts = $wpdb->prefix . 'hisab_bank_accounts';
        $this->table_bank_transactions = $wpdb->prefix . 'hisab_bank_transactions';
    }
    
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Create transactions table
        $sql_transactions = "CREATE TABLE {$this->table_transactions} (
            id int(11) NOT NULL AUTO_INCREMENT,
            type enum('income','expense') NOT NULL,
            amount decimal(10,2) NOT NULL,
            description text,
            category_id int(11) DEFAULT NULL,
            owner_id int(11) DEFAULT NULL,
            payment_method varchar(50) DEFAULT NULL,
            bill_image_id int(11) DEFAULT NULL,
            transaction_tax decimal(10,2) DEFAULT NULL,
            transaction_discount decimal(10,2) DEFAULT NULL,
            transaction_date date NOT NULL,
            bs_year int(4) DEFAULT NULL,
            bs_month int(2) DEFAULT NULL,
            bs_day int(2) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            user_id int(11) DEFAULT NULL,
            PRIMARY KEY (id),
            KEY type (type),
            KEY transaction_date (transaction_date),
            KEY bs_date (bs_year, bs_month, bs_day),
            KEY category_id (category_id),
            KEY owner_id (owner_id),
            KEY payment_method (payment_method),
            KEY user_id (user_id)
        ) $charset_collate;";
        
        // Create categories table
        $sql_categories = "CREATE TABLE {$this->table_categories} (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            type enum('income','expense') NOT NULL,
            color varchar(7) DEFAULT '#007cba',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY type (type)
        ) $charset_collate;";
        
        // Create owners table
        $sql_owners = "CREATE TABLE {$this->table_owners} (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            color varchar(7) DEFAULT '#007cba',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_name (name)
        ) $charset_collate;";
        
        // Create transaction details table
        $sql_transaction_details = "CREATE TABLE {$this->table_transaction_details} (
            id int(11) NOT NULL AUTO_INCREMENT,
            transaction_id int(11) NOT NULL,
            item_name varchar(255) NOT NULL,
            rate decimal(10,2) NOT NULL,
            quantity decimal(10,2) NOT NULL,
            item_total decimal(10,2) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY transaction_id (transaction_id)
        ) $charset_collate;";
        
        // Create bank accounts table
        $sql_bank_accounts = "CREATE TABLE {$this->table_bank_accounts} (
            id int(11) NOT NULL AUTO_INCREMENT,
            account_name varchar(255) NOT NULL,
            bank_name varchar(255) NOT NULL,
            account_number varchar(100) DEFAULT NULL,
            account_type enum('savings','current','credit_card','fixed_deposit') NOT NULL DEFAULT 'savings',
            currency enum('NPR','USD') NOT NULL DEFAULT 'NPR',
            initial_balance decimal(15,2) NOT NULL DEFAULT 0.00,
            current_balance decimal(15,2) NOT NULL DEFAULT 0.00,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            user_id int(11) DEFAULT NULL,
            PRIMARY KEY (id),
            KEY account_type (account_type),
            KEY currency (currency),
            KEY is_active (is_active),
            KEY user_id (user_id)
        ) $charset_collate;";
        
        // Create bank transactions table
        $sql_bank_transactions = "CREATE TABLE {$this->table_bank_transactions} (
            id int(11) NOT NULL AUTO_INCREMENT,
            account_id int(11) NOT NULL,
            transaction_type enum('deposit','withdrawal','phone_pay','transfer_in','transfer_out') NOT NULL,
            amount decimal(15,2) NOT NULL,
            currency enum('NPR','USD') NOT NULL,
            description text,
            reference_number varchar(100) DEFAULT NULL,
            phone_pay_reference varchar(100) DEFAULT NULL,
            transaction_date date NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_by int(11) DEFAULT NULL,
            PRIMARY KEY (id),
            KEY account_id (account_id),
            KEY transaction_type (transaction_type),
            KEY transaction_date (transaction_date),
            KEY currency (currency),
            KEY created_by (created_by)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_transactions);
        dbDelta($sql_categories);
        dbDelta($sql_owners);
        dbDelta($sql_transaction_details);
        dbDelta($sql_bank_accounts);
        dbDelta($sql_bank_transactions);
        
        // Create foreign key constraints
        $this->create_foreign_keys();
        
        // Insert default categories
        $this->insert_default_categories();        
    }
    
    private function create_foreign_keys() {
        global $wpdb;
        
        // Add foreign key for transaction_details table
        $wpdb->query("ALTER TABLE {$this->table_transaction_details} 
                      ADD CONSTRAINT fk_transaction_details_transaction_id 
                      FOREIGN KEY (transaction_id) REFERENCES {$this->table_transactions}(id) ON DELETE CASCADE");
        
        // Add foreign key for bank_transactions table
        $wpdb->query("ALTER TABLE {$this->table_bank_transactions} 
                      ADD CONSTRAINT fk_bank_transactions_account_id 
                      FOREIGN KEY (account_id) REFERENCES {$this->table_bank_accounts}(id) ON DELETE CASCADE");
    }
    
    private function insert_default_categories() {
        global $wpdb;
        
        // Check if categories already exist
        $existing_categories = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_categories}");
        
        if ($existing_categories > 0) {
            return; // Categories already exist, don't insert duplicates
        }
        
        $default_categories = $this->get_default_categories();
        
        foreach ($default_categories as $category) {
            $wpdb->insert(
                $this->table_categories,
                $category,
                array('%s', '%s', '%s')
            );
        }
    }
    
    /**
     * Get default categories array
     */
    private function get_default_categories() {
        return array(
            // Income categories
            array('name' => 'Salary', 'type' => 'income', 'color' => '#28a745'),
            array('name' => 'Freelance', 'type' => 'income', 'color' => '#17a2b8'),
            array('name' => 'Investment', 'type' => 'income', 'color' => '#6f42c1'),
            array('name' => 'Business', 'type' => 'income', 'color' => '#fd7e14'),
            array('name' => 'Other Income', 'type' => 'income', 'color' => '#20c997'),
            
            // Expense categories
            array('name' => 'Food & Dining', 'type' => 'expense', 'color' => '#dc3545'),
            array('name' => 'Transportation', 'type' => 'expense', 'color' => '#ffc107'),
            array('name' => 'Housing', 'type' => 'expense', 'color' => '#6c757d'),
            array('name' => 'Utilities', 'type' => 'expense', 'color' => '#007bff'),
            array('name' => 'Healthcare', 'type' => 'expense', 'color' => '#e83e8c'),
            array('name' => 'Entertainment', 'type' => 'expense', 'color' => '#fd7e14'),
            array('name' => 'Shopping', 'type' => 'expense', 'color' => '#20c997'),
            array('name' => 'Education', 'type' => 'expense', 'color' => '#6f42c1'),
            array('name' => 'Other Expense', 'type' => 'expense', 'color' => '#6c757d')
        );
    }
    
    /**
     * Manually insert default categories (for admin use)
     */
    public function insert_default_categories_manually() {
        global $wpdb;
        
        $default_categories = $this->get_default_categories();
        
        $inserted_count = 0;
        foreach ($default_categories as $category) {
            // Check if category already exists
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_categories} WHERE name = %s AND type = %s",
                $category['name'],
                $category['type']
            ));
            
            if ($exists == 0) {
                $result = $wpdb->insert(
                    $this->table_categories,
                    $category,
                    array('%s', '%s', '%s')
                );
                
                if ($result) {
                    $inserted_count++;
                }
            }
        }
        
        return array(
            'success' => true,
            'message' => sprintf('Inserted %d new default categories', $inserted_count),
            'inserted_count' => $inserted_count
        );
    }
    
    public function save_transaction($data) {
        global $wpdb;        
        
        // Validate and fix transaction_date
        if (empty($data['transaction_date'])) {
            // Fallback to current date if empty
            $data['transaction_date'] = date('Y-m-d');
        }
        
        // Ensure date is in correct format
        $transaction_date = sanitize_text_field($data['transaction_date']);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $transaction_date)) {
            return array('success' => false, 'message' => 'Invalid date format');
        }
        
        $transaction_data = array(
            'type' => sanitize_text_field($data['type']),
            'amount' => floatval($data['amount']),
            'description' => sanitize_textarea_field($data['description']),
            'category_id' => intval($data['category_id']),
            'owner_id' => isset($data['owner_id']) && !empty($data['owner_id']) ? intval($data['owner_id']) : null,
            'payment_method' => isset($data['payment_method']) && !empty($data['payment_method']) ? sanitize_text_field($data['payment_method']) : null,
            'bill_image_id' => null, // Will be set later if file uploaded
            'transaction_tax' => isset($data['transaction_tax']) && !empty($data['transaction_tax']) ? floatval($data['transaction_tax']) : null,
            'transaction_discount' => isset($data['transaction_discount']) && !empty($data['transaction_discount']) ? floatval($data['transaction_discount']) : null,
            'transaction_date' => $transaction_date,
            'bs_year' => null, // Will be set later
            'bs_month' => null, // Will be set later
            'bs_day' => null, // Will be set later
            'user_id' => get_current_user_id()
        );
        
        
        // Handle bill image (attachment ID from media uploader)
        if (isset($data['bill_image_id']) && !empty($data['bill_image_id'])) {
            $transaction_data['bill_image_id'] = intval($data['bill_image_id']);
        }
        
        // Add BS date if provided
        if (isset($data['bs_year']) && isset($data['bs_month']) && isset($data['bs_day']) && 
            !empty($data['bs_year']) && !empty($data['bs_month']) && !empty($data['bs_day'])) {
            // BS date was explicitly provided
            $transaction_data['bs_year'] = intval($data['bs_year']);
            $transaction_data['bs_month'] = intval($data['bs_month']);
            $transaction_data['bs_day'] = intval($data['bs_day']);
        } else {
            // Convert AD date to BS date
            $ad_date = explode('-', $transaction_date);
            $bs_date = HisabNepaliDate::ad_to_bs($ad_date[0], $ad_date[1], $ad_date[2]);
            if ($bs_date) {
                $transaction_data['bs_year'] = $bs_date['year'];
                $transaction_data['bs_month'] = $bs_date['month'];
                $transaction_data['bs_day'] = $bs_date['day'];
            }
        }
        
        
        $result = $wpdb->insert(
            $this->table_transactions,
            $transaction_data,
            array('%s', '%f', '%s', '%d', '%d', '%s', '%d', '%f', '%f', '%s', '%d', '%d', '%d', '%d')
        );
        
        if ($result === false) {
            return array('success' => false, 'message' => 'Failed to save transaction: ' . $wpdb->last_error);
        }
        return array('success' => true, 'message' => 'Transaction saved successfully', 'data' => array('transaction_id' => $wpdb->insert_id));
    }
    
    public function update_transaction($data) {
        global $wpdb;
        
        $transaction_id = intval($data['transaction_id']);
        $transaction_data = array(
            'type' => sanitize_text_field($data['type']),
            'amount' => floatval($data['amount']),
            'description' => sanitize_textarea_field($data['description']),
            'category_id' => intval($data['category_id']),
            'owner_id' => !empty($data['owner_id']) ? intval($data['owner_id']) : null,
            'transaction_date' => sanitize_text_field($data['transaction_date']),
            'payment_method' => !empty($data['payment_method']) ? sanitize_text_field($data['payment_method']) : null,
            'bill_image_id' => !empty($data['bill_image_id']) ? intval($data['bill_image_id']) : null,
            'transaction_tax' => !empty($data['transaction_tax']) ? floatval($data['transaction_tax']) : null,
            'transaction_discount' => !empty($data['transaction_discount']) ? floatval($data['transaction_discount']) : null,
            'updated_at' => current_time('mysql')
        );
        
        // Handle BS date conversion if needed
        if (!empty($data['bs_year']) && !empty($data['bs_month']) && !empty($data['bs_day'])) {
            $bs_year = intval($data['bs_year']);
            $bs_month = intval($data['bs_month']);
            $bs_day = intval($data['bs_day']);
            
            $transaction_data['bs_year'] = $bs_year;
            $transaction_data['bs_month'] = $bs_month;
            $transaction_data['bs_day'] = $bs_day;
        } else {
            // Convert AD to BS
            $ad_parts = explode('-', $data['transaction_date']);
            if (count($ad_parts) === 3) {
                $bs_date = HisabNepaliDate::ad_to_bs($ad_parts[0], $ad_parts[1], $ad_parts[2]);
                if ($bs_date) {
                    $transaction_data['bs_year'] = $bs_date['year'];
                    $transaction_data['bs_month'] = $bs_date['month'];
                    $transaction_data['bs_day'] = $bs_date['day'];
                }
            }
        }
        
        $result = $wpdb->update(
            $this->table_transactions,
            $transaction_data,
            array('id' => $transaction_id),
            array('%s', '%f', '%s', '%d', '%d', '%s', '%s', '%d', '%f', '%f', '%s', '%d', '%d', '%d'),
            array('%d')
        );
        
        if ($result === false) {
            return array('success' => false, 'message' => 'Failed to update transaction');
        }
        
        return array('success' => true, 'message' => 'Transaction updated successfully');
    }
    
    public function get_transactions($filters = array()) {
        global $wpdb;
        
        $where_conditions = array('1=1');
        $where_values = array();
        
        if (!empty($filters['type'])) {
            $where_conditions[] = 't.type = %s';
            $where_values[] = sanitize_text_field($filters['type']);
        }
        
        if (!empty($filters['start_date'])) {
            $where_conditions[] = 't.transaction_date >= %s';
            $where_values[] = sanitize_text_field($filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $where_conditions[] = 't.transaction_date <= %s';
            $where_values[] = sanitize_text_field($filters['end_date']);
        }
        
        if (!empty($filters['category_id'])) {
            $where_conditions[] = 't.category_id = %d';
            $where_values[] = intval($filters['category_id']);
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        if (empty($where_values)) {
            $sql = "
                SELECT t.*, c.name as category_name, c.color as category_color, o.name as owner_name, o.color as owner_color,
                       bi.guid as bill_image_url, bi.post_title as bill_image_title
                FROM {$this->table_transactions} t
                LEFT JOIN {$this->table_categories} c ON t.category_id = c.id
                LEFT JOIN {$this->table_owners} o ON t.owner_id = o.id
                LEFT JOIN {$wpdb->posts} bi ON t.bill_image_id = bi.ID
                WHERE {$where_clause}
                ORDER BY t.transaction_date DESC, t.created_at DESC
            ";
        } else {
            $sql = $wpdb->prepare("
                SELECT t.*, c.name as category_name, c.color as category_color, o.name as owner_name, o.color as owner_color,
                       bi.guid as bill_image_url, bi.post_title as bill_image_title
                FROM {$this->table_transactions} t
                LEFT JOIN {$this->table_categories} c ON t.category_id = c.id
                LEFT JOIN {$this->table_owners} o ON t.owner_id = o.id
                LEFT JOIN {$wpdb->posts} bi ON t.bill_image_id = bi.ID
                WHERE {$where_clause}
                ORDER BY t.transaction_date DESC, t.created_at DESC
            ", $where_values);
        }
        
        return $wpdb->get_results($sql);
    }
    
    public function get_monthly_summary($year, $month) {
        global $wpdb;
        
        $start_date = sprintf('%04d-%02d-01', $year, $month);
        $end_date = date('Y-m-t', strtotime($start_date));
        
        $sql = $wpdb->prepare("
            SELECT 
                type,
                SUM(amount) as total,
                COUNT(*) as count
            FROM {$this->table_transactions}
            WHERE transaction_date >= %s AND transaction_date <= %s
            GROUP BY type
        ", $start_date, $end_date);
        
        $results = $wpdb->get_results($sql);
        
        $summary = array(
            'income' => 0,
            'expense' => 0,
            'income_count' => 0,
            'expense_count' => 0
        );
        
        foreach ($results as $result) {
            $summary[$result->type] = floatval($result->total);
            $summary[$result->type . '_count'] = intval($result->count);
        }
        
        $summary['net'] = $summary['income'] - $summary['expense'];
        
        return $summary;
    }
    
    public function get_trend_data($type, $months = 12) {
        global $wpdb;
        
        $end_date = date('Y-m-01');
        $start_date = date('Y-m-01', strtotime("-{$months} months", strtotime($end_date)));
        
        $sql = $wpdb->prepare("
            SELECT 
                DATE_FORMAT(transaction_date, '%%Y-%%m') as month,
                SUM(amount) as total
            FROM {$this->table_transactions}
            WHERE type = %s 
            AND transaction_date >= %s 
            AND transaction_date < %s
            GROUP BY DATE_FORMAT(transaction_date, '%%Y-%%m')
            ORDER BY month ASC
        ", $type, $start_date, $end_date);
        
        return $wpdb->get_results($sql);
    }
    
    public function get_category_summary($type, $year, $month) {
        global $wpdb;
        
        $start_date = sprintf('%04d-%02d-01', $year, $month);
        $end_date = date('Y-m-t', strtotime($start_date));
        
        $sql = $wpdb->prepare("
            SELECT 
                c.name as category_name,
                c.color as category_color,
                SUM(t.amount) as total,
                COUNT(t.id) as count
            FROM {$this->table_categories} c
            LEFT JOIN {$this->table_transactions} t ON c.id = t.category_id 
                AND t.type = %s 
                AND t.transaction_date >= %s 
                AND t.transaction_date <= %s
            WHERE c.type = %s
            GROUP BY c.id, c.name, c.color
            HAVING total > 0
            ORDER BY total DESC
        ", $type, $start_date, $end_date, $type);
        
        return $wpdb->get_results($sql);
    }
    
    public function get_categories($type = null) {
        global $wpdb;
        
        if ($type) {
            $sql = $wpdb->prepare("
                SELECT * FROM {$this->table_categories}
                WHERE type = %s
                ORDER BY name ASC
            ", $type);
        } else {
            $sql = "SELECT * FROM {$this->table_categories} ORDER BY type, name ASC";
        }
        
        return $wpdb->get_results($sql);
    }
    
    public function save_category($data) {
        global $wpdb;
        
        $category_data = array(
            'name' => sanitize_text_field($data['name']),
            'type' => sanitize_text_field($data['type']),
            'color' => sanitize_hex_color($data['color'])
        );
        
        if (isset($data['id']) && !empty($data['id'])) {
            // Update existing category
            $result = $wpdb->update(
                $this->table_categories,
                $category_data,
                array('id' => intval($data['id'])),
                array('%s', '%s', '%s'),
                array('%d')
            );
            
            if ($result === false) {
                return array('success' => false, 'message' => 'Failed to update category');
            }
            
            return array('success' => true, 'message' => 'Category updated successfully', 'id' => intval($data['id']));
        } else {
            // Insert new category
            $result = $wpdb->insert(
                $this->table_categories,
                $category_data,
                array('%s', '%s', '%s')
            );
            
            if ($result === false) {
                return array('success' => false, 'message' => 'Failed to save category');
            }
            
            return array('success' => true, 'message' => 'Category saved successfully', 'id' => $wpdb->insert_id);
        }
    }
    
    public function delete_category($id) {
        global $wpdb;
        
        // Check if category is being used by any transactions
        $usage_check = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_transactions} WHERE category_id = %d",
            intval($id)
        ));
        
        if ($usage_check > 0) {
            return array('success' => false, 'message' => 'Cannot delete category that is being used by transactions');
        }
        
        $result = $wpdb->delete(
            $this->table_categories,
            array('id' => intval($id)),
            array('%d')
        );
        
        if ($result === false) {
            return array('success' => false, 'message' => 'Failed to delete category');
        }
        
        return array('success' => true, 'message' => 'Category deleted successfully');
    }
    
    public function get_category($id) {
        global $wpdb;
        
        $sql = $wpdb->prepare("
            SELECT * FROM {$this->table_categories}
            WHERE id = %d
        ", intval($id));
        
        return $wpdb->get_row($sql);
    }
    
    public function delete_transaction($id) {
        global $wpdb;
        
        // Start transaction
        $wpdb->query('START TRANSACTION');
        
        try {
            // Delete transaction details first
            $wpdb->delete(
                $this->table_transaction_details,
                array('transaction_id' => intval($id)),
                array('%d')
            );
            
            // Delete transaction
            $result = $wpdb->delete(
                $this->table_transactions,
                array('id' => intval($id)),
                array('%d')
            );
            
            if ($result === false) {
                $wpdb->query('ROLLBACK');
                return false;
            }
            
            $wpdb->query('COMMIT');
            return true;
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return false;
        }
    }
    
    public function get_yearly_summary($year) {
        global $wpdb;
        
        $start_date = sprintf('%04d-01-01', $year);
        $end_date = sprintf('%04d-12-31', $year);
        
        $sql = $wpdb->prepare("
            SELECT 
                MONTH(transaction_date) as month,
                type,
                SUM(amount) as total
            FROM {$this->table_transactions}
            WHERE transaction_date >= %s AND transaction_date <= %s
            GROUP BY MONTH(transaction_date), type
            ORDER BY month ASC
        ", $start_date, $end_date);
        
        $results = $wpdb->get_results($sql);
        
        $summary = array();
        for ($i = 1; $i <= 12; $i++) {
            $summary[$i] = array('income' => 0, 'expense' => 0);
        }
        
        foreach ($results as $result) {
            $summary[$result->month][$result->type] = floatval($result->total);
        }
        
        return $summary;
    }
    
    // Owner Management Methods
    
    public function get_owners() {
        global $wpdb;
        
        $sql = "SELECT * FROM {$this->table_owners} ORDER BY name ASC";
        return $wpdb->get_results($sql);
    }
    
    public function save_owner($data) {
        global $wpdb;
        
        $owner_data = array(
            'name' => sanitize_text_field($data['name']),
            'color' => sanitize_hex_color($data['color'])
        );
        
        if (isset($data['id']) && !empty($data['id'])) {
            // Update existing owner
            $result = $wpdb->update(
                $this->table_owners,
                $owner_data,
                array('id' => intval($data['id'])),
                array('%s', '%s'),
                array('%d')
            );
            
            if ($result === false) {
                return array('success' => false, 'message' => 'Failed to update owner');
            }
            
            return array('success' => true, 'message' => 'Owner updated successfully', 'id' => intval($data['id']));
        } else {
            // Insert new owner
            $result = $wpdb->insert(
                $this->table_owners,
                $owner_data,
                array('%s', '%s')
            );
            
            if ($result === false) {
                return array('success' => false, 'message' => 'Failed to save owner');
            }
            
            return array('success' => true, 'message' => 'Owner saved successfully', 'id' => $wpdb->insert_id);
        }
    }
    
    public function delete_owner($id) {
        global $wpdb;
        
        // Check if owner is being used by any transactions
        $usage_check = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_transactions} WHERE owner_id = %d",
            intval($id)
        ));
        
        if ($usage_check > 0) {
            return array('success' => false, 'message' => 'Cannot delete owner that is being used by transactions');
        }
        
        $result = $wpdb->delete(
            $this->table_owners,
            array('id' => intval($id)),
            array('%d')
        );
        
        if ($result === false) {
            return array('success' => false, 'message' => 'Failed to delete owner');
        }
        
        return array('success' => true, 'message' => 'Owner deleted successfully');
    }
    
    public function get_owner($id) {
        global $wpdb;
        
        $sql = $wpdb->prepare("
            SELECT * FROM {$this->table_owners}
            WHERE id = %d
        ", intval($id));
        
        return $wpdb->get_row($sql);
    }
    
    public function get_owner_summary($year, $month) {
        global $wpdb;
        
        $start_date = sprintf('%04d-%02d-01', $year, $month);
        $end_date = date('Y-m-t', strtotime($start_date));
        
        $sql = $wpdb->prepare("
            SELECT 
                o.name as owner_name,
                o.color as owner_color,
                SUM(t.amount) as total,
                COUNT(t.id) as count
            FROM {$this->table_owners} o
            LEFT JOIN {$this->table_transactions} t ON o.id = t.owner_id 
                AND t.transaction_date >= %s 
                AND t.transaction_date <= %s
            GROUP BY o.id, o.name, o.color
            HAVING total > 0
            ORDER BY total DESC
        ", $start_date, $end_date);
        
        return $wpdb->get_results($sql);
    }
    
    
    /**
     * Get transactions with pagination
     */
    public function get_transactions_paginated($page = 1, $per_page = 20, $filters = array()) {
        global $wpdb;
        
        $offset = ($page - 1) * $per_page;
        
        // Build WHERE clause based on filters
        $where_conditions = array('1=1');
        $where_values = array();
        
        if (!empty($filters['type'])) {
            $where_conditions[] = 't.type = %s';
            $where_values[] = $filters['type'];
        }
        
        if (!empty($filters['category_id'])) {
            $where_conditions[] = 't.category_id = %d';
            $where_values[] = intval($filters['category_id']);
        }
        
        if (!empty($filters['owner_id'])) {
            $where_conditions[] = 't.owner_id = %d';
            $where_values[] = intval($filters['owner_id']);
        }
        
        if (!empty($filters['date_from'])) {
            $where_conditions[] = 't.transaction_date >= %s';
            $where_values[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where_conditions[] = 't.transaction_date <= %s';
            $where_values[] = $filters['date_to'];
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        // Get total count
        $count_sql = "
            SELECT COUNT(DISTINCT t.id)
            FROM {$this->table_transactions} t
            LEFT JOIN {$this->table_categories} c ON t.category_id = c.id
            LEFT JOIN {$this->table_owners} o ON t.owner_id = o.id
            WHERE {$where_clause}
        ";
        
        if (!empty($where_values)) {
            $count_sql = $wpdb->prepare($count_sql, $where_values);
        }
        
        $total_items = $wpdb->get_var($count_sql);
        $total_pages = ceil($total_items / $per_page);
        
        // Get transactions
        $sql = "
            SELECT t.*, c.name as category_name, c.color as category_color, o.name as owner_name, o.color as owner_color,
                   p.guid as bill_image_url, p.post_title as bill_image_title
            FROM {$this->table_transactions} t
            LEFT JOIN {$this->table_categories} c ON t.category_id = c.id
            LEFT JOIN {$this->table_owners} o ON t.owner_id = o.id
            LEFT JOIN {$wpdb->posts} p ON t.bill_image_id = p.ID
            WHERE {$where_clause}
            ORDER BY t.transaction_date DESC, t.created_at DESC
            LIMIT %d OFFSET %d
        ";
        
        $where_values[] = $per_page;
        $where_values[] = $offset;
        
        $transactions = $wpdb->get_results($wpdb->prepare($sql, $where_values));
        
        return array(
            'transactions' => $transactions,
            'total_items' => $total_items,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'per_page' => $per_page
        );
    }
    
    /**
     * Get recent transactions (for dashboard)
     */
    public function get_recent_transactions($limit = 5) {
        global $wpdb;
        
        $sql = "
            SELECT t.*, c.name as category_name, c.color as category_color, o.name as owner_name, o.color as owner_color,
                   p.guid as bill_image_url, p.post_title as bill_image_title
            FROM {$this->table_transactions} t
            LEFT JOIN {$this->table_categories} c ON t.category_id = c.id
            LEFT JOIN {$this->table_owners} o ON t.owner_id = o.id
            LEFT JOIN {$wpdb->posts} p ON t.bill_image_id = p.ID
            ORDER BY t.transaction_date DESC, t.created_at DESC
            LIMIT %d
        ";
        
        return $wpdb->get_results($wpdb->prepare($sql, $limit));
    }

    /**
     * Get a single transaction by ID
     */
    public function get_transaction($id) {
        global $wpdb;
        
        $sql = $wpdb->prepare("
            SELECT * FROM {$this->table_transactions}
            WHERE id = %d
        ", intval($id));
        
        return $wpdb->get_row($sql);
    }
    
    // Transaction Details Methods
    
    /**
     * Get transaction details for a specific transaction
     */
    public function get_transaction_details($transaction_id) {
        global $wpdb;
        
        $sql = $wpdb->prepare("
            SELECT * FROM {$this->table_transaction_details}
            WHERE transaction_id = %d
            ORDER BY id ASC
        ", intval($transaction_id));
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Save transaction details (itemized items)
     */
    public function save_transaction_details($transaction_id, $details) {
        global $wpdb;
        
        // Start transaction
        $wpdb->query('START TRANSACTION');
        
        try {
            // Delete existing details
            $wpdb->delete(
                $this->table_transaction_details,
                array('transaction_id' => intval($transaction_id)),
                array('%d')
            );
            
            // Insert new details
            foreach ($details as $detail) {
                $detail_data = array(
                    'transaction_id' => intval($transaction_id),
                    'item_name' => sanitize_text_field($detail['item_name']),
                    'rate' => floatval($detail['rate']),
                    'quantity' => floatval($detail['quantity']),
                    'item_total' => floatval($detail['item_total'])
                );
                
                $result = $wpdb->insert(
                    $this->table_transaction_details,
                    $detail_data,
                    array('%d', '%s', '%f', '%f', '%f')
                );
                
                if ($result === false) {
                    throw new Exception('Failed to save transaction detail');
                }
            }
            
            // Commit transaction
            $wpdb->query('COMMIT');
            
            return array('success' => true, 'message' => 'Transaction details saved successfully');
            
        } catch (Exception $e) {
            // Rollback transaction
            $wpdb->query('ROLLBACK');
            return array('success' => false, 'message' => $e->getMessage());
        }
    }
    
    /**
     * Delete transaction details
     */
    public function delete_transaction_details($transaction_id) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $this->table_transaction_details,
            array('transaction_id' => intval($transaction_id)),
            array('%d')
        );
        
        if ($result === false) {
            return array('success' => false, 'message' => 'Failed to delete transaction details');
        }
        
        return array('success' => true, 'message' => 'Transaction details deleted successfully');
    }
    
    /**
     * Validate transaction details against main amount
     */
    public function validate_transaction_details($transaction_id, $details) {
        // Get main transaction
        $transaction = $this->get_transaction($transaction_id);
        if (!$transaction) {
            return array('success' => false, 'message' => 'Transaction not found');
        }
        
        // Calculate totals from details
        $subtotal = 0;
        foreach ($details as $detail) {
            $subtotal += floatval($detail['item_total']);
        }
        
        // Add tax and subtract discount
        $tax = floatval($transaction->transaction_tax ?? 0);
        $discount = floatval($transaction->transaction_discount ?? 0);
        $calculated_total = $subtotal + $tax - $discount;
        
        // Compare with main amount
        $main_amount = floatval($transaction->amount);
        $difference = abs($calculated_total - $main_amount);
        
        if ($difference > 0.01) { // Allow for small rounding differences
            return array(
                'success' => false, 
                'message' => sprintf(
                    'Details total (%.2f) does not match transaction amount (%.2f). Difference: %.2f',
                    $calculated_total,
                    $main_amount,
                    $difference
                )
            );
        }
        
        return array('success' => true, 'message' => 'Validation passed');
    }
}
