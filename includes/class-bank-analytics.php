<?php
/**
 * Bank Analytics Class for Hisab Financial Tracker
 * 
 * Handles bank transaction analytics including charts and trends
 */

if (!defined('ABSPATH')) {
    exit;
}

class HisabBankAnalytics {
    
    private $table_bank_transactions;
    private $table_bank_accounts;
    
    public function __construct() {
        global $wpdb;
        $this->table_bank_transactions = $wpdb->prefix . 'hisab_bank_transactions';
        $this->table_bank_accounts = $wpdb->prefix . 'hisab_bank_accounts';
    }
    
    /**
     * Get bank transaction trend data for charts
     */
    public function get_bank_trend_data($months = null, $start_date = null, $end_date = null) {
        global $wpdb;
        
        if (!$end_date) {
            $end_date = date('Y-m-01');
        }
        
        if (!$start_date) {
            if ($months) {
                $start_date = date('Y-m-01', strtotime("-{$months} months", strtotime($end_date)));
            } else {
                // Get data from the very beginning
                $start_date = $wpdb->get_var("SELECT MIN(transaction_date) FROM {$this->table_bank_transactions}");
                if (!$start_date) {
                    $start_date = date('Y-m-01', strtotime('-12 months'));
                }
            }
        }
        
        $sql = $wpdb->prepare("
            SELECT 
                DATE_FORMAT(transaction_date, '%%Y-%%m') as month,
                transaction_type,
                SUM(amount) as total
            FROM {$this->table_bank_transactions}
            WHERE transaction_date >= %s 
            AND transaction_date < %s
            GROUP BY DATE_FORMAT(transaction_date, '%%Y-%%m'), transaction_type
            ORDER BY month ASC, transaction_type ASC
        ", $start_date, $end_date);
        
        $results = $wpdb->get_results($sql);
        
        // Organize data by month and transaction type
        $trend_data = array();
        $months_list = array();
        
        // Generate month labels from start_date to end_date
        $current = new DateTime($start_date);
        $end = new DateTime($end_date);
        
        while ($current <= $end) {
            $month_date = $current->format('Y-m');
            $months_list[] = $current->format('M Y');
            $trend_data[$month_date] = array(
                'deposit' => 0,
                'withdrawal' => 0,
                'phone_pay' => 0,
                'transfer_in' => 0,
                'transfer_out' => 0
            );
            $current->add(new DateInterval('P1M'));
        }
        
        // Fill in actual data
        foreach ($results as $result) {
            if (isset($trend_data[$result->month])) {
                $trend_data[$result->month][$result->transaction_type] = floatval($result->total);
            }
        }
        
        return array(
            'months' => $months_list,
            'data' => $trend_data
        );
    }
    
    /**
     * Get bank transaction summary by type (for pie charts)
     */
    public function get_bank_transaction_summary($year = null, $month = null) {
        global $wpdb;
        
        if (!$year) {
            $year = date('Y');
        }
        
        if ($month === null) {
            // Get yearly data
            $start_date = sprintf('%04d-01-01', $year);
            $end_date = sprintf('%04d-12-31', $year);
        } else {
            // Get monthly data
            $start_date = sprintf('%04d-%02d-01', $year, $month);
            $end_date = date('Y-m-t', strtotime($start_date));
        }
        
        $sql = $wpdb->prepare("
            SELECT 
                transaction_type,
                SUM(amount) as total,
                COUNT(id) as count
            FROM {$this->table_bank_transactions}
            WHERE transaction_date >= %s 
            AND transaction_date <= %s
            GROUP BY transaction_type
            ORDER BY total DESC
        ", $start_date, $end_date);
        
        $results = $wpdb->get_results($sql);
        
        // Define colors for each transaction type
        $colors = array(
            'deposit' => '#28a745',
            'withdrawal' => '#dc3545',
            'phone_pay' => '#ffc107',
            'transfer_in' => '#17a2b8',
            'transfer_out' => '#6f42c1'
        );
        
        // Define labels for each transaction type
        $labels = array(
            'deposit' => __('Deposits', 'hisab-financial-tracker'),
            'withdrawal' => __('Withdrawals', 'hisab-financial-tracker'),
            'phone_pay' => __('Phone Pay', 'hisab-financial-tracker'),
            'transfer_in' => __('Transfers In', 'hisab-financial-tracker'),
            'transfer_out' => __('Transfers Out', 'hisab-financial-tracker')
        );
        
        $summary = array();
        foreach ($results as $result) {
            $summary[] = array(
                'type' => $result->transaction_type,
                'label' => $labels[$result->transaction_type],
                'amount' => floatval($result->total),
                'count' => intval($result->count),
                'color' => $colors[$result->transaction_type]
            );
        }
        
        return $summary;
    }
    
    /**
     * Get bank account balance trends
     */
    public function get_account_balance_trends($months = null, $start_date = null, $end_date = null) {
        global $wpdb;
        
        if (!$end_date) {
            $end_date = date('Y-m-01');
        }
        
        if (!$start_date) {
            if ($months) {
                $start_date = date('Y-m-01', strtotime("-{$months} months", strtotime($end_date)));
            } else {
                // Get data from the very beginning
                $start_date = $wpdb->get_var("SELECT MIN(transaction_date) FROM {$this->table_bank_transactions}");
                if (!$start_date) {
                    $start_date = date('Y-m-01', strtotime('-12 months'));
                }
            }
        }
        
        // Get all active accounts
        $accounts = $wpdb->get_results("
            SELECT id, account_name, currency
            FROM {$this->table_bank_accounts}
            WHERE is_active = 1
            ORDER BY account_name ASC
        ");
        
        $trend_data = array();
        $months_list = array();
        
        // Generate month labels from start_date to end_date
        $current = new DateTime($start_date);
        $end = new DateTime($end_date);
        
        while ($current <= $end) {
            $months_list[] = $current->format('M Y');
            $current->add(new DateInterval('P1M'));
        }
        
        foreach ($accounts as $account) {
            $account_trends = array();
            
            // Get balance at the end of each month
            $current = new DateTime($start_date);
            $end = new DateTime($end_date);
            
            while ($current <= $end) {
                $month_end = $current->format('Y-m-t');
                
                // Calculate balance at end of month
                $balance_sql = $wpdb->prepare("
                    SELECT 
                        (SELECT initial_balance FROM {$this->table_bank_accounts} WHERE id = %d) +
                        COALESCE(SUM(
                            CASE 
                                WHEN transaction_type IN ('deposit', 'transfer_in') THEN amount
                                WHEN transaction_type IN ('withdrawal', 'phone_pay', 'transfer_out') THEN -amount
                                ELSE 0
                            END
                        ), 0) as balance
                    FROM {$this->table_bank_transactions}
                    WHERE account_id = %d 
                    AND transaction_date <= %s
                ", $account->id, $account->id, $month_end);
                
                $balance_result = $wpdb->get_var($balance_sql);
                $account_trends[] = floatval($balance_result);
                $current->add(new DateInterval('P1M'));
            }
            
            $trend_data[] = array(
                'account_id' => $account->id,
                'account_name' => $account->account_name,
                'currency' => $account->currency,
                'balances' => $account_trends
            );
        }
        
        return array(
            'months' => $months_list,
            'accounts' => $trend_data
        );
    }
    
    /**
     * Get bank transaction summary by account
     */
    public function get_account_transaction_summary($year = null, $month = null) {
        global $wpdb;
        
        if (!$year) {
            $year = date('Y');
        }
        
        if ($month === null) {
            // Get yearly data
            $start_date = sprintf('%04d-01-01', $year);
            $end_date = sprintf('%04d-12-31', $year);
        } else {
            // Get monthly data
            $start_date = sprintf('%04d-%02d-01', $year, $month);
            $end_date = date('Y-m-t', strtotime($start_date));
        }
        
        $sql = $wpdb->prepare("
            SELECT 
                ba.id as account_id,
                ba.account_name,
                ba.currency,
                SUM(
                    CASE 
                        WHEN bt.transaction_type IN ('deposit', 'transfer_in') THEN bt.amount
                        WHEN bt.transaction_type IN ('withdrawal', 'phone_pay', 'transfer_out') THEN -bt.amount
                        ELSE 0
                    END
                ) as net_amount,
                COUNT(bt.id) as transaction_count
            FROM {$this->table_bank_accounts} ba
            LEFT JOIN {$this->table_bank_transactions} bt ON ba.id = bt.account_id 
                AND bt.transaction_date >= %s 
                AND bt.transaction_date <= %s
            WHERE ba.is_active = 1
            GROUP BY ba.id, ba.account_name, ba.currency
            ORDER BY net_amount DESC
        ", $start_date, $end_date);
        
        $results = $wpdb->get_results($sql);
        
        // Generate colors for accounts
        $colors = array('#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6f42c1', '#fd7e14', '#20c997', '#e83e8c');
        
        $summary = array();
        foreach ($results as $index => $result) {
            $summary[] = array(
                'account_id' => $result->account_id,
                'account_name' => $result->account_name,
                'currency' => $result->currency,
                'net_amount' => floatval($result->net_amount),
                'transaction_count' => intval($result->transaction_count),
                'color' => $colors[$index % count($colors)]
            );
        }
        
        return $summary;
    }
    
    /**
     * Get monthly bank transaction summary
     */
    public function get_monthly_summary($year = null, $month = null) {
        global $wpdb;
        
        if (!$year) {
            $year = date('Y');
        }
        
        if ($month === null) {
            $month = date('n');
        }
        
        $start_date = sprintf('%04d-%02d-01', $year, $month);
        $end_date = date('Y-m-t', strtotime($start_date));
        
        $sql = $wpdb->prepare("
            SELECT 
                transaction_type,
                SUM(amount) as total,
                COUNT(id) as count
            FROM {$this->table_bank_transactions}
            WHERE transaction_date >= %s 
            AND transaction_date <= %s
            GROUP BY transaction_type
        ", $start_date, $end_date);
        
        $results = $wpdb->get_results($sql);
        
        $summary = array(
            'deposits' => 0,
            'withdrawals' => 0,
            'phone_pay' => 0,
            'transfers_in' => 0,
            'transfers_out' => 0,
            'total_transactions' => 0
        );
        
        foreach ($results as $result) {
            switch ($result->transaction_type) {
                case 'deposit':
                    $summary['deposits'] = floatval($result->total);
                    break;
                case 'withdrawal':
                    $summary['withdrawals'] = floatval($result->total);
                    break;
                case 'phone_pay':
                    $summary['phone_pay'] = floatval($result->total);
                    break;
                case 'transfer_in':
                    $summary['transfers_in'] = floatval($result->total);
                    break;
                case 'transfer_out':
                    $summary['transfers_out'] = floatval($result->total);
                    break;
            }
            $summary['total_transactions'] += intval($result->count);
        }
        
        $summary['net_flow'] = $summary['deposits'] + $summary['transfers_in'] - $summary['withdrawals'] - $summary['phone_pay'] - $summary['transfers_out'];
        
        return $summary;
    }
    
    /**
     * Get available years for filtering
     */
    public function get_available_years() {
        global $wpdb;
        
        $sql = "
            SELECT DISTINCT YEAR(transaction_date) as year
            FROM {$this->table_bank_transactions}
            ORDER BY year DESC
        ";
        
        $results = $wpdb->get_results($sql);
        $years = array();
        
        foreach ($results as $result) {
            $years[] = intval($result->year);
        }
        
        return $years;
    }
    
    /**
     * Get available months for a specific year
     */
    public function get_available_months($year) {
        global $wpdb;
        
        $sql = $wpdb->prepare("
            SELECT DISTINCT MONTH(transaction_date) as month
            FROM {$this->table_bank_transactions}
            WHERE YEAR(transaction_date) = %d
            ORDER BY month ASC
        ", $year);
        
        $results = $wpdb->get_results($sql);
        $months = array();
        
        $month_names = array(
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        );
        
        foreach ($results as $result) {
            $months[] = array(
                'value' => intval($result->month),
                'name' => $month_names[intval($result->month)]
            );
        }
        
        return $months;
    }
    
    /**
     * Get date range for all bank transactions
     */
    public function get_transaction_date_range() {
        global $wpdb;
        
        $sql = "
            SELECT 
                MIN(transaction_date) as start_date,
                MAX(transaction_date) as end_date
            FROM {$this->table_bank_transactions}
        ";
        
        $result = $wpdb->get_row($sql);
        
        if (!$result || !$result->start_date || !$result->end_date) {
            return array(
                'start_date' => null,
                'end_date' => null
            );
        }
        
        return array(
            'start_date' => $result->start_date,
            'end_date' => $result->end_date
        );
    }
}
