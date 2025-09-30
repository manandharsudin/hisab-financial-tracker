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
    
    public function __construct() {
        global $wpdb;
        $this->table_transactions = $wpdb->prefix . 'hisab_transactions';
        $this->table_categories = $wpdb->prefix . 'hisab_categories';
        $this->table_owners = $wpdb->prefix . 'hisab_owners';
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
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_transactions);
        dbDelta($sql_categories);
        dbDelta($sql_owners);
        
        // Insert default categories
        $this->insert_default_categories();        
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
        
        $transaction_data = array(
            'type' => sanitize_text_field($data['type']),
            'amount' => floatval($data['amount']),
            'description' => sanitize_textarea_field($data['description']),
            'category_id' => intval($data['category_id']),
            'owner_id' => isset($data['owner_id']) && !empty($data['owner_id']) ? intval($data['owner_id']) : null,
            'transaction_date' => sanitize_text_field($data['transaction_date']),
            'user_id' => get_current_user_id()
        );
        
        // Add BS date if provided
        if (isset($data['bs_year']) && isset($data['bs_month']) && isset($data['bs_day'])) {
            $transaction_data['bs_year'] = intval($data['bs_year']);
            $transaction_data['bs_month'] = intval($data['bs_month']);
            $transaction_data['bs_day'] = intval($data['bs_day']);
        } else {
            // Convert AD date to BS date
            $ad_date = explode('-', $data['transaction_date']);
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
            array('%s', '%f', '%s', '%d', '%s', '%d', '%d', '%d', '%d')
        );
        
        if ($result === false) {
            return array('success' => false, 'message' => 'Failed to save transaction');
        }
        
        return array('success' => true, 'message' => 'Transaction saved successfully', 'id' => $wpdb->insert_id);
    }
    
    public function update_transaction($data) {
        global $wpdb;
        
        $transaction_id = intval($data['id']);
        $transaction_data = array(
            'type' => sanitize_text_field($data['type']),
            'amount' => floatval($data['amount']),
            'description' => sanitize_textarea_field($data['description']),
            'category_id' => intval($data['category_id']),
            'transaction_date' => sanitize_text_field($data['transaction_date']),
            'updated_at' => current_time('mysql')
        );
        
        $result = $wpdb->update(
            $this->table_transactions,
            $transaction_data,
            array('id' => $transaction_id),
            array('%s', '%f', '%s', '%d', '%s', '%s'),
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
                SELECT t.*, c.name as category_name, c.color as category_color, o.name as owner_name, o.color as owner_color
                FROM {$this->table_transactions} t
                LEFT JOIN {$this->table_categories} c ON t.category_id = c.id
                LEFT JOIN {$this->table_owners} o ON t.owner_id = o.id
                WHERE {$where_clause}
                ORDER BY t.transaction_date DESC, t.created_at DESC
            ";
        } else {
            $sql = $wpdb->prepare("
                SELECT t.*, c.name as category_name, c.color as category_color, o.name as owner_name, o.color as owner_color
                FROM {$this->table_transactions} t
                LEFT JOIN {$this->table_categories} c ON t.category_id = c.id
                LEFT JOIN {$this->table_owners} o ON t.owner_id = o.id
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
        
        $result = $wpdb->delete(
            $this->table_transactions,
            array('id' => intval($id)),
            array('%d')
        );
        
        if ($result === false) {
            return array('success' => false, 'message' => 'Failed to delete transaction');
        }
        
        return array('success' => true, 'message' => 'Transaction deleted successfully');
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
}
