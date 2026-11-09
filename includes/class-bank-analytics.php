<?php
/**
 * Bank Analytics Class for Hisab Financial Tracker
 * 
 * Handles bank transaction analytics including charts, trends, dual-calendar (AD/BS) filtering,
 * and multi-currency metrics.
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
     * Helper to get Bikram Sambat month name safely as a string
     */
    public function get_bs_month_name($month) {
        $month = intval($month);
        if (class_exists('HisabNepaliDate')) {
            $name = HisabNepaliDate::get_bs_months($month);
            if (is_string($name) && !empty($name)) {
                return $name;
            }
        }
        $names = array(
            1 => 'Baisakh', 2 => 'Jestha', 3 => 'Ashadh', 4 => 'Shrawan',
            5 => 'Bhadra', 6 => 'Ashwin', 7 => 'Kartik', 8 => 'Mangsir',
            9 => 'Poush', 10 => 'Magh', 11 => 'Falgun', 12 => 'Chaitra'
        );
        return isset($names[$month]) ? $names[$month] : "Month {$month}";
    }
    
    /**
     * Get bank transaction trend data for line charts
     *
     * @param int|null $months Number of months back from end_date (AD only)
     * @param string|null $start_date YYYY-MM-DD (AD)
     * @param string|null $end_date YYYY-MM-DD (AD)
     * @param int|null $account_id Filter by specific bank account
     * @param string|null $currency Filter by currency (NPR, USD)
     * @param string $calendar_type 'ad' or 'bs'
     * @param int|null $year Specific year filter (AD or BS)
     * @param int|null $month Specific month filter (1-12)
     * @return array
     */
    public function get_bank_trend_data($months = null, $start_date = null, $end_date = null, $account_id = null, $currency = null, $calendar_type = 'ad', $year = null, $month = null) {
        global $wpdb;
        
        $calendar_type = ($calendar_type === 'bs') ? 'bs' : 'ad';
        $trend_data = array();
        $months_list = array();
        $month_keys = array();
        
        $transaction_types = array('deposit', 'withdrawal', 'phone_pay', 'transfer_in', 'transfer_out');
        $blank_entry = array_fill_keys($transaction_types, 0.0);
        
        if ($calendar_type === 'bs') {
            if ($year !== null && $month !== null) {
                $m_name = $this->get_bs_month_name($month);
                $key = sprintf('%04d-%02d', $year, $month);
                $month_keys[] = $key;
                $months_list[] = "{$m_name} {$year}";
                $trend_data[$key] = $blank_entry;
            } elseif ($year !== null) {
                for ($m = 1; $m <= 12; $m++) {
                    $m_name = $this->get_bs_month_name($m);
                    $key = sprintf('%04d-%02d', $year, $m);
                    $month_keys[] = $key;
                    $months_list[] = "{$m_name} {$year}";
                    $trend_data[$key] = $blank_entry;
                }
            } else {
                $min_max = $wpdb->get_row("SELECT MIN(bs_year) as min_y, MAX(bs_year) as max_y FROM {$this->table_bank_transactions} WHERE bs_year IS NOT NULL AND bs_year > 0");
                $current_bs = class_exists('HisabNepaliDate') ? HisabNepaliDate::get_current_bs_date() : null;
                $curr_y = $current_bs ? intval($current_bs['year']) : 2081;
                $min_y = ($min_max && $min_max->min_y) ? intval($min_max->min_y) : $curr_y;
                $max_y = ($min_max && $min_max->max_y) ? intval($min_max->max_y) : $curr_y;
                
                for ($y = $min_y; $y <= $max_y; $y++) {
                    for ($m = 1; $m <= 12; $m++) {
                        $m_name = $this->get_bs_month_name($m);
                        $key = sprintf('%04d-%02d', $y, $m);
                        $month_keys[] = $key;
                        $months_list[] = "{$m_name} {$y}";
                        $trend_data[$key] = $blank_entry;
                    }
                }
            }
            
            $where_clauses = array("bs_year IS NOT NULL", "bs_month IS NOT NULL");
            $params = array();
            
            if ($year !== null) {
                $where_clauses[] = "bs_year = %d";
                $params[] = $year;
            }
            if ($month !== null) {
                $where_clauses[] = "bs_month = %d";
                $params[] = $month;
            }
            if ($account_id !== null) {
                $where_clauses[] = "account_id = %d";
                $params[] = $account_id;
            }
            if ($currency !== null) {
                $where_clauses[] = "currency = %s";
                $params[] = $currency;
            }
            
            $where_sql = implode(' AND ', $where_clauses);
            $query = "
                SELECT 
                    CONCAT(LPAD(bs_year, 4, '0'), '-', LPAD(bs_month, 2, '0')) as period,
                    transaction_type,
                    SUM(amount) as total
                FROM {$this->table_bank_transactions}
                WHERE {$where_sql}
                GROUP BY period, transaction_type
                ORDER BY period ASC
            ";
            
            $sql = !empty($params) ? $wpdb->prepare($query, $params) : $query;
            $results = $wpdb->get_results($sql);
            
            foreach ($results as $result) {
                if (isset($trend_data[$result->period]) && isset($trend_data[$result->period][$result->transaction_type])) {
                    $trend_data[$result->period][$result->transaction_type] = floatval($result->total);
                }
            }
        } else {
            // Gregorian (AD)
            if ($year !== null && $month !== null) {
                $start_date = sprintf('%04d-%02d-01', $year, $month);
                $end_date = date('Y-m-t', strtotime($start_date));
            } elseif ($year !== null) {
                $start_date = sprintf('%04d-01-01', $year);
                $end_date = sprintf('%04d-12-31', $year);
            }
            
            if (!$end_date) {
                $end_date = date('Y-m-t');
            }
            
            if (!$start_date) {
                if ($months) {
                    $start_date = date('Y-m-01', strtotime("-{$months} months", strtotime($end_date)));
                } else {
                    $start_date = $wpdb->get_var("SELECT MIN(transaction_date) FROM {$this->table_bank_transactions}");
                    if (!$start_date) {
                        $start_date = date('Y-m-01', strtotime('-12 months'));
                    }
                }
            }
            
            $current = new DateTime(date('Y-m-01', strtotime($start_date)));
            $end = new DateTime(date('Y-m-01', strtotime($end_date)));
            
            while ($current <= $end) {
                $month_date = $current->format('Y-m');
                $month_keys[] = $month_date;
                $months_list[] = $current->format('M Y');
                $trend_data[$month_date] = $blank_entry;
                $current->add(new DateInterval('P1M'));
            }
            
            $where_clauses = array("transaction_date >= %s", "transaction_date <= %s");
            $params = array($start_date, $end_date);
            
            if ($account_id !== null) {
                $where_clauses[] = "account_id = %d";
                $params[] = $account_id;
            }
            if ($currency !== null) {
                $where_clauses[] = "currency = %s";
                $params[] = $currency;
            }
            
            $where_sql = implode(' AND ', $where_clauses);
            $query = "
                SELECT 
                    DATE_FORMAT(transaction_date, '%%Y-%%m') as period,
                    transaction_type,
                    SUM(amount) as total
                FROM {$this->table_bank_transactions}
                WHERE {$where_sql}
                GROUP BY period, transaction_type
                ORDER BY period ASC
            ";
            
            $sql = $wpdb->prepare($query, $params);
            $results = $wpdb->get_results($sql);
            
            foreach ($results as $result) {
                if (isset($trend_data[$result->period]) && isset($trend_data[$result->period][$result->transaction_type])) {
                    $trend_data[$result->period][$result->transaction_type] = floatval($result->total);
                }
            }
        }
        
        $datasets = array(
            'deposit' => array(),
            'withdrawal' => array(),
            'phone_pay' => array(),
            'transfer_in' => array(),
            'transfer_out' => array()
        );
        
        foreach ($month_keys as $mk) {
            foreach (array_keys($datasets) as $type) {
                $datasets[$type][] = isset($trend_data[$mk][$type]) ? $trend_data[$mk][$type] : 0.0;
            }
        }
        
        return array(
            'months' => $months_list,
            'keys' => $month_keys,
            'datasets' => $datasets,
            'data' => $trend_data
        );
    }
    
    /**
     * Get bank transaction summary by type (for pie charts)
     */
    public function get_bank_transaction_summary($year = null, $month = null, $account_id = null, $currency = null, $calendar_type = 'ad') {
        global $wpdb;
        
        $calendar_type = ($calendar_type === 'bs') ? 'bs' : 'ad';
        $where = array('1=1');
        $params = array();
        
        if ($calendar_type === 'bs') {
            if ($year !== null) {
                $where[] = 'bs_year = %d';
                $params[] = $year;
            }
            if ($month !== null) {
                $where[] = 'bs_month = %d';
                $params[] = $month;
            }
        } else {
            if ($year !== null && $month !== null) {
                $start_date = sprintf('%04d-%02d-01', $year, $month);
                $end_date = date('Y-m-t', strtotime($start_date));
                $where[] = 'transaction_date >= %s AND transaction_date <= %s';
                $params[] = $start_date;
                $params[] = $end_date;
            } elseif ($year !== null) {
                $start_date = sprintf('%04d-01-01', $year);
                $end_date = sprintf('%04d-12-31', $year);
                $where[] = 'transaction_date >= %s AND transaction_date <= %s';
                $params[] = $start_date;
                $params[] = $end_date;
            }
        }
        
        if ($account_id !== null) {
            $where[] = 'account_id = %d';
            $params[] = $account_id;
        }
        if ($currency !== null) {
            $where[] = 'currency = %s';
            $params[] = $currency;
        }
        
        $where_sql = implode(' AND ', $where);
        $query = "
            SELECT 
                transaction_type,
                SUM(amount) as total,
                COUNT(id) as count
            FROM {$this->table_bank_transactions}
            WHERE {$where_sql}
            GROUP BY transaction_type
            ORDER BY total DESC
        ";
        
        $sql = !empty($params) ? $wpdb->prepare($query, $params) : $query;
        $results = $wpdb->get_results($sql);
        
        $colors = array(
            'deposit' => '#28a745',
            'withdrawal' => '#dc3545',
            'phone_pay' => '#ffc107',
            'transfer_in' => '#17a2b8',
            'transfer_out' => '#6f42c1'
        );
        
        $labels = array(
            'deposit' => __('Deposits', 'hisab-financial-tracker'),
            'withdrawal' => __('Withdrawals', 'hisab-financial-tracker'),
            'phone_pay' => __('Phone Pay', 'hisab-financial-tracker'),
            'transfer_in' => __('Transfers In', 'hisab-financial-tracker'),
            'transfer_out' => __('Transfers Out', 'hisab-financial-tracker')
        );
        
        $summary = array();
        foreach ($results as $result) {
            $type = $result->transaction_type;
            $summary[] = array(
                'type' => $type,
                'label' => isset($labels[$type]) ? $labels[$type] : ucfirst($type),
                'amount' => floatval($result->total),
                'count' => intval($result->count),
                'color' => isset($colors[$type]) ? $colors[$type] : '#6c757d'
            );
        }
        
        return $summary;
    }
    
    /**
     * Get bank account balance trends
     */
    public function get_account_balance_trends($months = null, $start_date = null, $end_date = null, $account_id = null, $currency = null, $calendar_type = 'ad', $year = null, $month = null) {
        global $wpdb;
        
        $calendar_type = ($calendar_type === 'bs') ? 'bs' : 'ad';
        
        // Fetch matching active accounts
        $account_where = array("is_active = 1");
        $account_params = array();
        
        if ($account_id !== null) {
            $account_where[] = "id = %d";
            $account_params[] = $account_id;
        }
        if ($currency !== null) {
            $account_where[] = "currency = %s";
            $account_params[] = $currency;
        }
        
        $account_sql = "SELECT id, account_name, currency, initial_balance FROM {$this->table_bank_accounts} WHERE " . implode(' AND ', $account_where) . " ORDER BY account_name ASC";
        $accounts = !empty($account_params) ? $wpdb->get_results($wpdb->prepare($account_sql, $account_params)) : $wpdb->get_results($account_sql);
        
        $trend_data = array();
        $months_list = array();
        $month_keys = array();
        
        if (empty($accounts)) {
            return array(
                'months' => $months_list,
                'keys' => $month_keys,
                'accounts' => array()
            );
        }
        
        if ($calendar_type === 'bs') {
            if ($year !== null && $month !== null) {
                $m_name = $this->get_bs_month_name($month);
                $key = sprintf('%04d-%02d', $year, $month);
                $month_keys[] = $key;
                $months_list[] = "{$m_name} {$year}";
                $min_y = $year;
                $min_m = $month;
                $max_y = $year;
                $max_m = $month;
            } elseif ($year !== null) {
                for ($m = 1; $m <= 12; $m++) {
                    $m_name = $this->get_bs_month_name($m);
                    $key = sprintf('%04d-%02d', $year, $m);
                    $month_keys[] = $key;
                    $months_list[] = "{$m_name} {$year}";
                }
                $min_y = $year;
                $min_m = 1;
                $max_y = $year;
                $max_m = 12;
            } else {
                $min_max = $wpdb->get_row("SELECT MIN(bs_year) as min_y, MAX(bs_year) as max_y FROM {$this->table_bank_transactions} WHERE bs_year IS NOT NULL AND bs_year > 0");
                $current_bs = class_exists('HisabNepaliDate') ? HisabNepaliDate::get_current_bs_date() : null;
                $curr_y = $current_bs ? intval($current_bs['year']) : 2081;
                $min_y = ($min_max && $min_max->min_y) ? intval($min_max->min_y) : $curr_y;
                $max_y = ($min_max && $min_max->max_y) ? intval($min_max->max_y) : $curr_y;
                $min_m = 1;
                $max_m = 12;
                
                for ($y = $min_y; $y <= $max_y; $y++) {
                    for ($m = 1; $m <= 12; $m++) {
                        $m_name = $this->get_bs_month_name($m);
                        $key = sprintf('%04d-%02d', $y, $m);
                        $month_keys[] = $key;
                        $months_list[] = "{$m_name} {$y}";
                    }
                }
            }
            
            $aggregated_sql = $wpdb->prepare("
                SELECT 
                    account_id,
                    CASE 
                        WHEN bs_year < %d OR (bs_year = %d AND bs_month < %d) THEN 'prior'
                        ELSE CONCAT(LPAD(bs_year, 4, '0'), '-', LPAD(bs_month, 2, '0'))
                    END as period,
                    COALESCE(SUM(
                        CASE 
                            WHEN transaction_type IN ('deposit', 'transfer_in') THEN amount
                            WHEN transaction_type IN ('withdrawal', 'phone_pay', 'transfer_out') THEN -amount
                            ELSE 0
                        END
                    ), 0) as net_change
                FROM {$this->table_bank_transactions}
                WHERE bs_year IS NOT NULL AND bs_month IS NOT NULL
                AND (bs_year < %d OR (bs_year = %d AND bs_month <= %d))
                GROUP BY account_id, period
            ", $min_y, $min_y, $min_m, $max_y, $max_y, $max_m);
            
            $results = $wpdb->get_results($aggregated_sql);
        } else {
            // Gregorian (AD)
            if ($year !== null && $month !== null) {
                $start_date = sprintf('%04d-%02d-01', $year, $month);
                $end_date = date('Y-m-t', strtotime($start_date));
            } elseif ($year !== null) {
                $start_date = sprintf('%04d-01-01', $year);
                $end_date = sprintf('%04d-12-31', $year);
            }
            
            if (!$end_date) {
                $end_date = date('Y-m-t');
            }
            
            if (!$start_date) {
                if ($months) {
                    $start_date = date('Y-m-01', strtotime("-{$months} months", strtotime($end_date)));
                } else {
                    $start_date = $wpdb->get_var("SELECT MIN(transaction_date) FROM {$this->table_bank_transactions}");
                    if (!$start_date) {
                        $start_date = date('Y-m-01', strtotime('-12 months'));
                    }
                }
            }
            
            $current = new DateTime(date('Y-m-01', strtotime($start_date)));
            $end = new DateTime(date('Y-m-01', strtotime($end_date)));
            $max_month_end = date('Y-m-t', strtotime($end_date));
            
            while ($current <= $end) {
                $month_keys[] = $current->format('Y-m');
                $months_list[] = $current->format('M Y');
                $current->add(new DateInterval('P1M'));
            }
            
            $aggregated_sql = $wpdb->prepare("
                SELECT 
                    account_id,
                    CASE 
                        WHEN transaction_date < %s THEN 'prior'
                        ELSE DATE_FORMAT(transaction_date, '%%Y-%%m')
                    END as period,
                    COALESCE(SUM(
                        CASE 
                            WHEN transaction_type IN ('deposit', 'transfer_in') THEN amount
                            WHEN transaction_type IN ('withdrawal', 'phone_pay', 'transfer_out') THEN -amount
                            ELSE 0
                        END
                    ), 0) as net_change
                FROM {$this->table_bank_transactions}
                WHERE transaction_date <= %s
                GROUP BY account_id, period
            ", $start_date, $max_month_end);
            
            $results = $wpdb->get_results($aggregated_sql);
        }
        
        $net_changes = array();
        foreach ($results as $row) {
            $net_changes[intval($row->account_id)][$row->period] = floatval($row->net_change);
        }
        
        foreach ($accounts as $account) {
            $acc_id = intval($account->id);
            $running_balance = floatval($account->initial_balance) + (isset($net_changes[$acc_id]['prior']) ? $net_changes[$acc_id]['prior'] : 0.0);
            $account_trends = array();
            
            foreach ($month_keys as $ym) {
                if (isset($net_changes[$acc_id][$ym])) {
                    $running_balance += $net_changes[$acc_id][$ym];
                }
                $account_trends[] = round($running_balance, 2);
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
            'keys' => $month_keys,
            'accounts' => $trend_data
        );
    }
    
    /**
     * Get bank transaction summary by account
     */
    public function get_account_transaction_summary($year = null, $month = null, $account_id = null, $currency = null, $calendar_type = 'ad') {
        global $wpdb;
        
        $calendar_type = ($calendar_type === 'bs') ? 'bs' : 'ad';
        $bt_where = array('1=1');
        $params = array();
        
        if ($calendar_type === 'bs') {
            if ($year !== null) {
                $bt_where[] = 'bt.bs_year = %d';
                $params[] = $year;
            }
            if ($month !== null) {
                $bt_where[] = 'bt.bs_month = %d';
                $params[] = $month;
            }
        } else {
            if ($year !== null && $month !== null) {
                $start_date = sprintf('%04d-%02d-01', $year, $month);
                $end_date = date('Y-m-t', strtotime($start_date));
                $bt_where[] = 'bt.transaction_date >= %s AND bt.transaction_date <= %s';
                $params[] = $start_date;
                $params[] = $end_date;
            } elseif ($year !== null) {
                $start_date = sprintf('%04d-01-01', $year);
                $end_date = sprintf('%04d-12-31', $year);
                $bt_where[] = 'bt.transaction_date >= %s AND bt.transaction_date <= %s';
                $params[] = $start_date;
                $params[] = $end_date;
            }
        }
        
        $ba_where = array('ba.is_active = 1');
        if ($account_id !== null) {
            $ba_where[] = 'ba.id = %d';
            $params[] = $account_id;
        }
        if ($currency !== null) {
            $ba_where[] = 'ba.currency = %s';
            $params[] = $currency;
        }
        
        $bt_where_sql = implode(' AND ', $bt_where);
        $ba_where_sql = implode(' AND ', $ba_where);
        
        $query = "
            SELECT 
                ba.id as account_id,
                ba.account_name,
                ba.currency,
                COALESCE(SUM(
                    CASE 
                        WHEN bt.transaction_type IN ('deposit', 'transfer_in') THEN bt.amount
                        WHEN bt.transaction_type IN ('withdrawal', 'phone_pay', 'transfer_out') THEN -bt.amount
                        ELSE 0
                    END
                ), 0) as net_amount,
                COUNT(bt.id) as transaction_count
            FROM {$this->table_bank_accounts} ba
            LEFT JOIN {$this->table_bank_transactions} bt ON ba.id = bt.account_id 
                AND {$bt_where_sql}
            WHERE {$ba_where_sql}
            GROUP BY ba.id, ba.account_name, ba.currency
            ORDER BY net_amount DESC
        ";
        
        $sql = !empty($params) ? $wpdb->prepare($query, $params) : $query;
        $results = $wpdb->get_results($sql);
        
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
     * Get monthly bank transaction summary with multi-currency breakdown
     */
    public function get_monthly_summary($year = null, $month = null, $account_id = null, $currency = null, $calendar_type = 'ad') {
        global $wpdb;
        
        $calendar_type = ($calendar_type === 'bs') ? 'bs' : 'ad';
        $where = array('1=1');
        $params = array();
        
        if ($calendar_type === 'bs') {
            if ($year !== null) {
                $where[] = 'bs_year = %d';
                $params[] = $year;
            }
            if ($month !== null) {
                $where[] = 'bs_month = %d';
                $params[] = $month;
            }
        } else {
            if ($year !== null && $month !== null) {
                $start_date = sprintf('%04d-%02d-01', $year, $month);
                $end_date = date('Y-m-t', strtotime($start_date));
                $where[] = 'transaction_date >= %s AND transaction_date <= %s';
                $params[] = $start_date;
                $params[] = $end_date;
            } elseif ($year !== null) {
                $start_date = sprintf('%04d-01-01', $year);
                $end_date = sprintf('%04d-12-31', $year);
                $where[] = 'transaction_date >= %s AND transaction_date <= %s';
                $params[] = $start_date;
                $params[] = $end_date;
            }
        }
        
        if ($account_id !== null) {
            $where[] = 'account_id = %d';
            $params[] = $account_id;
        }
        if ($currency !== null) {
            $where[] = 'currency = %s';
            $params[] = $currency;
        }
        
        $where_sql = implode(' AND ', $where);
        $query = "
            SELECT 
                currency,
                transaction_type,
                SUM(amount) as total,
                COUNT(id) as count
            FROM {$this->table_bank_transactions}
            WHERE {$where_sql}
            GROUP BY currency, transaction_type
        ";
        
        $sql = !empty($params) ? $wpdb->prepare($query, $params) : $query;
        $results = $wpdb->get_results($sql);
        
        $summary = array(
            'deposits' => 0.0,
            'withdrawals' => 0.0,
            'phone_pay' => 0.0,
            'transfers_in' => 0.0,
            'transfers_out' => 0.0,
            'net_flow' => 0.0,
            'total_transactions' => 0,
            'by_currency' => array()
        );
        
        foreach ($results as $result) {
            $curr = !empty($result->currency) ? strtoupper($result->currency) : 'NPR';
            if (!isset($summary['by_currency'][$curr])) {
                $summary['by_currency'][$curr] = array(
                    'deposits' => 0.0,
                    'withdrawals' => 0.0,
                    'phone_pay' => 0.0,
                    'transfers_in' => 0.0,
                    'transfers_out' => 0.0,
                    'net_flow' => 0.0,
                    'total_transactions' => 0,
                    'symbol' => ($curr === 'USD') ? '$' : '₹'
                );
            }
            
            $amt = floatval($result->total);
            $cnt = intval($result->count);
            
            switch ($result->transaction_type) {
                case 'deposit':
                    $summary['deposits'] += $amt;
                    $summary['by_currency'][$curr]['deposits'] += $amt;
                    break;
                case 'withdrawal':
                    $summary['withdrawals'] += $amt;
                    $summary['by_currency'][$curr]['withdrawals'] += $amt;
                    break;
                case 'phone_pay':
                    $summary['phone_pay'] += $amt;
                    $summary['by_currency'][$curr]['phone_pay'] += $amt;
                    break;
                case 'transfer_in':
                    $summary['transfers_in'] += $amt;
                    $summary['by_currency'][$curr]['transfers_in'] += $amt;
                    break;
                case 'transfer_out':
                    $summary['transfers_out'] += $amt;
                    $summary['by_currency'][$curr]['transfers_out'] += $amt;
                    break;
            }
            
            $summary['total_transactions'] += $cnt;
            $summary['by_currency'][$curr]['total_transactions'] += $cnt;
        }
        
        $summary['net_flow'] = $summary['deposits'] + $summary['transfers_in'] - $summary['withdrawals'] - $summary['phone_pay'] - $summary['transfers_out'];
        
        foreach ($summary['by_currency'] as $curr => &$curr_data) {
            $curr_data['net_flow'] = $curr_data['deposits'] + $curr_data['transfers_in'] - $curr_data['withdrawals'] - $curr_data['phone_pay'] - $curr_data['transfers_out'];
        }
        unset($curr_data);
        
        return $summary;
    }
    
    /**
     * Get available years for filtering
     */
    public function get_available_years($calendar_type = 'ad') {
        global $wpdb;
        $calendar_type = ($calendar_type === 'bs') ? 'bs' : 'ad';
        
        if ($calendar_type === 'bs') {
            $sql = "
                SELECT DISTINCT bs_year as year
                FROM {$this->table_bank_transactions}
                WHERE bs_year IS NOT NULL AND bs_year > 0
                ORDER BY year DESC
            ";
        } else {
            $sql = "
                SELECT DISTINCT YEAR(transaction_date) as year
                FROM {$this->table_bank_transactions}
                WHERE transaction_date IS NOT NULL
                ORDER BY year DESC
            ";
        }
        
        $results = $wpdb->get_results($sql);
        $years = array();
        
        foreach ($results as $result) {
            if ($result->year) {
                $years[] = intval($result->year);
            }
        }
        
        if (empty($years)) {
            if ($calendar_type === 'bs') {
                $current_bs = class_exists('HisabNepaliDate') ? HisabNepaliDate::get_current_bs_date() : null;
                $years[] = $current_bs ? intval($current_bs['year']) : 2081;
            } else {
                $years[] = intval(date('Y'));
            }
        }
        
        return $years;
    }
    
    /**
     * Get available months for a specific year
     */
    public function get_available_months($year = null, $calendar_type = 'ad') {
        global $wpdb;
        $calendar_type = ($calendar_type === 'bs') ? 'bs' : 'ad';
        
        if ($calendar_type === 'bs') {
            if ($year) {
                $sql = $wpdb->prepare("
                    SELECT DISTINCT bs_month as month
                    FROM {$this->table_bank_transactions}
                    WHERE bs_year = %d AND bs_month IS NOT NULL AND bs_month > 0
                    ORDER BY bs_month ASC
                ", $year);
                $results = $wpdb->get_results($sql);
                $months = array();
                foreach ($results as $result) {
                    $m = intval($result->month);
                    $months[] = array(
                        'value' => $m,
                        'name' => $this->get_bs_month_name($m)
                    );
                }
                if (!empty($months)) {
                    return $months;
                }
            }
            $months = array();
            for ($i = 1; $i <= 12; $i++) {
                $months[] = array(
                    'value' => $i,
                    'name' => $this->get_bs_month_name($i)
                );
            }
            return $months;
        } else {
            $month_names = array(
                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
            );
            if ($year) {
                $sql = $wpdb->prepare("
                    SELECT DISTINCT MONTH(transaction_date) as month
                    FROM {$this->table_bank_transactions}
                    WHERE YEAR(transaction_date) = %d AND transaction_date IS NOT NULL
                    ORDER BY month ASC
                ", $year);
                $results = $wpdb->get_results($sql);
                $months = array();
                foreach ($results as $result) {
                    $m = intval($result->month);
                    $months[] = array(
                        'value' => $m,
                        'name' => isset($month_names[$m]) ? $month_names[$m] : "Month {$m}"
                    );
                }
                if (!empty($months)) {
                    return $months;
                }
            }
            $months = array();
            for ($i = 1; $i <= 12; $i++) {
                $months[] = array(
                    'value' => $i,
                    'name' => $month_names[$i]
                );
            }
            return $months;
        }
    }
    
    /**
     * Get date range for all bank transactions
     */
    public function get_transaction_date_range($calendar_type = 'ad') {
        global $wpdb;
        
        $sql = "
            SELECT 
                MIN(transaction_date) as start_date,
                MAX(transaction_date) as end_date,
                MIN(bs_year) as min_bs_year,
                MAX(bs_year) as max_bs_year
            FROM {$this->table_bank_transactions}
        ";
        
        $result = $wpdb->get_row($sql);
        
        return array(
            'start_date' => $result ? $result->start_date : null,
            'end_date' => $result ? $result->end_date : null,
            'min_bs_year' => $result ? $result->min_bs_year : null,
            'max_bs_year' => $result ? $result->max_bs_year : null,
        );
    }
}
