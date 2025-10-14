<?php
/**
 * Bank Analytics view template
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables are passed from the admin class:
// $selected_year, $selected_month, $available_years, $available_months, $date_range
// $trend_data, $transaction_summary, $account_summary, $balance_trends, $monthly_summary

// Safety checks to ensure variables are defined
if (!isset($selected_year)) $selected_year = null;
if (!isset($selected_month)) $selected_month = null;
if (!isset($available_years)) $available_years = array();
if (!isset($available_months)) $available_months = array();
if (!isset($date_range)) $date_range = array('start_date' => null, 'end_date' => null);
if (!isset($trend_data)) $trend_data = array('months' => array(), 'data' => array());
if (!isset($transaction_summary)) $transaction_summary = array();
if (!isset($account_summary)) $account_summary = array();
if (!isset($balance_trends)) $balance_trends = array('months' => array(), 'accounts' => array());
if (!isset($monthly_summary)) $monthly_summary = array(
    'deposits' => 0, 'withdrawals' => 0, 'phone_pay' => 0,
    'transfers_in' => 0, 'transfers_out' => 0, 'net_flow' => 0, 'total_transactions' => 0
);
?>

    <div class="wrap">
    <h1><?php _e('Bank Analytics', 'hisab-financial-tracker'); ?></h1>
    
    <?php if (empty($available_years)): ?>
        <div class="notice notice-info">
            <p><strong><?php _e('No Bank Transactions Found', 'hisab-financial-tracker'); ?></strong></p>
            <p><?php _e('There are no bank transactions in the system yet. Please add some bank transactions to see analytics.', 'hisab-financial-tracker'); ?></p>
            <p>
                <a href="?page=hisab-add-bank-transaction" class="button button-primary">
                    <?php _e('Add Bank Transaction', 'hisab-financial-tracker'); ?>
                </a>
                <a href="?page=hisab-bank-accounts" class="button">
                    <?php _e('Manage Bank Accounts', 'hisab-financial-tracker'); ?>
                </a>
            </p>
        </div>
    <?php else: ?>
    
    <!-- Filter Controls -->
    <div class="hisab-analytics-filters">
        <form method="get" action="">
            <input type="hidden" name="page" value="hisab-bank-analytics">
            
            <div class="hisab-filter-group">
                <label for="year-filter"><?php _e('Year:', 'hisab-financial-tracker'); ?></label>
                <select id="year-filter" name="year" onchange="this.form.submit()">
                    <option value=""><?php _e('All Years', 'hisab-financial-tracker'); ?></option>
                    <?php foreach ($available_years as $year): ?>
                        <option value="<?php echo $year; ?>" <?php selected($selected_year, $year); ?>>
                            <?php echo $year; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="hisab-filter-group">
                <label for="month-filter"><?php _e('Month:', 'hisab-financial-tracker'); ?></label>
                <select id="month-filter" name="month" onchange="this.form.submit()" <?php echo !$selected_year ? 'disabled' : ''; ?>>
                    <option value=""><?php _e('All Months', 'hisab-financial-tracker'); ?></option>
                    <?php foreach ($available_months as $month): ?>
                        <option value="<?php echo $month['value']; ?>" <?php selected($selected_month, $month['value']); ?>>
                            <?php echo $month['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="hisab-filter-group">
                <button type="submit" class="button button-primary"><?php _e('Apply Filters', 'hisab-financial-tracker'); ?></button>
                <a href="?page=hisab-bank-analytics" class="button"><?php _e('Clear Filters', 'hisab-financial-tracker'); ?></a>
            </div>
        </form>
        
        <div class="hisab-date-range-info">
            <?php if ($date_range['start_date'] && $date_range['end_date']): ?>
                <p><strong><?php _e('Data Range:', 'hisab-financial-tracker'); ?></strong> 
                   <?php echo date('F j, Y', strtotime($date_range['start_date'])); ?> - 
                   <?php echo date('F j, Y', strtotime($date_range['end_date'])); ?>
                </p>
            <?php endif; ?>
            
            <?php if ($selected_year && $selected_month): ?>
                <p><strong><?php _e('Showing:', 'hisab-financial-tracker'); ?></strong> 
                   <?php echo date('F Y', mktime(0, 0, 0, $selected_month, 1, $selected_year)); ?>
                </p>
            <?php elseif ($selected_year): ?>
                <p><strong><?php _e('Showing:', 'hisab-financial-tracker'); ?></strong> 
                   <?php echo $selected_year; ?>
                </p>
            <?php else: ?>
                <p><strong><?php _e('Showing:', 'hisab-financial-tracker'); ?></strong> 
                   <?php _e('All Data', 'hisab-financial-tracker'); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="hisab-analytics-section">
        <?php if ($selected_year && $selected_month): ?>
            <h2><?php _e('Monthly Summary', 'hisab-financial-tracker'); ?> - <?php echo date('F Y', mktime(0, 0, 0, $selected_month, 1, $selected_year)); ?></h2>
        <?php elseif ($selected_year): ?>
            <h2><?php _e('Yearly Summary', 'hisab-financial-tracker'); ?> - <?php echo $selected_year; ?></h2>
        <?php else: ?>
            <h2><?php _e('Overall Summary', 'hisab-financial-tracker'); ?> - <?php _e('All Time', 'hisab-financial-tracker'); ?></h2>
        <?php endif; ?>
        
        <div class="hisab-monthly-stats">
            <div class="hisab-stat-card">
                <h3><?php _e('Total Deposits', 'hisab-financial-tracker'); ?></h3>
                <span class="hisab-stat-value positive"><?php echo number_format($monthly_summary['deposits'], 2); ?></span>
            </div>
            <div class="hisab-stat-card">
                <h3><?php _e('Total Withdrawals', 'hisab-financial-tracker'); ?></h3>
                <span class="hisab-stat-value negative"><?php echo number_format($monthly_summary['withdrawals'], 2); ?></span>
            </div>
            <div class="hisab-stat-card">
                <h3><?php _e('Phone Pay', 'hisab-financial-tracker'); ?></h3>
                <span class="hisab-stat-value negative"><?php echo number_format($monthly_summary['phone_pay'], 2); ?></span>
            </div>
            <div class="hisab-stat-card">
                <h3><?php _e('Transfers In', 'hisab-financial-tracker'); ?></h3>
                <span class="hisab-stat-value positive"><?php echo number_format($monthly_summary['transfers_in'], 2); ?></span>
            </div>
            <div class="hisab-stat-card">
                <h3><?php _e('Transfers Out', 'hisab-financial-tracker'); ?></h3>
                <span class="hisab-stat-value negative"><?php echo number_format($monthly_summary['transfers_out'], 2); ?></span>
            </div>
            <div class="hisab-stat-card">
                <h3><?php _e('Net Flow', 'hisab-financial-tracker'); ?></h3>
                <span class="hisab-stat-value <?php echo $monthly_summary['net_flow'] >= 0 ? 'positive' : 'negative'; ?>">
                    <?php echo number_format($monthly_summary['net_flow'], 2); ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Bank Transaction Trends -->
    <div class="hisab-analytics-section">
        <?php if ($selected_year && $selected_month): ?>
            <h2><?php _e('Bank Transaction Trends', 'hisab-financial-tracker'); ?> - <?php echo date('F Y', mktime(0, 0, 0, $selected_month, 1, $selected_year)); ?></h2>
        <?php elseif ($selected_year): ?>
            <h2><?php _e('Bank Transaction Trends', 'hisab-financial-tracker'); ?> - <?php echo $selected_year; ?></h2>
        <?php else: ?>
            <h2><?php _e('Bank Transaction Trends', 'hisab-financial-tracker'); ?> - <?php _e('All Time', 'hisab-financial-tracker'); ?></h2>
        <?php endif; ?>
        
        <div class="hisab-trend-chart">
            <canvas id="hisab-bank-trend-chart" width="800" height="400" 
                    data-trend-data="<?php echo esc_attr(json_encode($trend_data)); ?>"></canvas>
        </div>
    </div>
    
    <!-- Transaction Type Breakdown -->
    <div class="hisab-category-breakdown">
        <div class="hisab-category-section">
            <?php if ($selected_year && $selected_month): ?>
                <h3><?php _e('Transaction Types', 'hisab-financial-tracker'); ?> - <?php echo date('F Y', mktime(0, 0, 0, $selected_month, 1, $selected_year)); ?></h3>
            <?php elseif ($selected_year): ?>
                <h3><?php _e('Transaction Types', 'hisab-financial-tracker'); ?> - <?php echo $selected_year; ?></h3>
            <?php else: ?>
                <h3><?php _e('Transaction Types', 'hisab-financial-tracker'); ?> - <?php _e('All Time', 'hisab-financial-tracker'); ?></h3>
            <?php endif; ?>
            <div class="hisab-category-chart">
                <canvas id="hisab-transaction-types" width="400" height="300" 
                        data-transaction-types="<?php echo esc_attr(json_encode($transaction_summary)); ?>"></canvas>
            </div>
        </div>
        
        <div class="hisab-category-section">
            <?php if ($selected_year && $selected_month): ?>
                <h3><?php _e('Account Activity', 'hisab-financial-tracker'); ?> - <?php echo date('F Y', mktime(0, 0, 0, $selected_month, 1, $selected_year)); ?></h3>
            <?php elseif ($selected_year): ?>
                <h3><?php _e('Account Activity', 'hisab-financial-tracker'); ?> - <?php echo $selected_year; ?></h3>
            <?php else: ?>
                <h3><?php _e('Account Activity', 'hisab-financial-tracker'); ?> - <?php _e('All Time', 'hisab-financial-tracker'); ?></h3>
            <?php endif; ?>
            <div class="hisab-category-chart">
                <canvas id="hisab-account-activity" width="400" height="300" 
                        data-account-activity="<?php echo esc_attr(json_encode($account_summary)); ?>"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Account Balance Trends -->
    <div class="hisab-analytics-section">
        <?php if ($selected_year && $selected_month): ?>
            <h2><?php _e('Account Balance Trends', 'hisab-financial-tracker'); ?> - <?php echo date('F Y', mktime(0, 0, 0, $selected_month, 1, $selected_year)); ?></h2>
        <?php elseif ($selected_year): ?>
            <h2><?php _e('Account Balance Trends', 'hisab-financial-tracker'); ?> - <?php echo $selected_year; ?></h2>
        <?php else: ?>
            <h2><?php _e('Account Balance Trends', 'hisab-financial-tracker'); ?> - <?php _e('All Time', 'hisab-financial-tracker'); ?></h2>
        <?php endif; ?>
        
        <div class="hisab-balance-chart">
            <canvas id="hisab-balance-trend-chart" width="800" height="400" 
                    data-balance-trends="<?php echo esc_attr(json_encode($balance_trends)); ?>"></canvas>
        </div>
    </div>
    
    <!-- Summary Statistics -->
    <div class="hisab-analytics-section">
        <?php if ($selected_year && $selected_month): ?>
            <h2><?php _e('Summary Statistics', 'hisab-financial-tracker'); ?> - <?php echo date('F Y', mktime(0, 0, 0, $selected_month, 1, $selected_year)); ?></h2>
        <?php elseif ($selected_year): ?>
            <h2><?php _e('Summary Statistics', 'hisab-financial-tracker'); ?> - <?php echo $selected_year; ?></h2>
        <?php else: ?>
            <h2><?php _e('Summary Statistics', 'hisab-financial-tracker'); ?> - <?php _e('All Time', 'hisab-financial-tracker'); ?></h2>
        <?php endif; ?>
        
        <div class="hisab-yearly-stats">
            <?php
            $total_deposits = array_sum(array_column($transaction_summary, 'amount'));
            $total_withdrawals = 0;
            $total_phone_pay = 0;
            $total_transfers_in = 0;
            $total_transfers_out = 0;
            
            foreach ($transaction_summary as $transaction) {
                switch ($transaction['type']) {
                    case 'deposit':
                        $total_deposits = $transaction['amount'];
                        break;
                    case 'withdrawal':
                        $total_withdrawals = $transaction['amount'];
                        break;
                    case 'phone_pay':
                        $total_phone_pay = $transaction['amount'];
                        break;
                    case 'transfer_in':
                        $total_transfers_in = $transaction['amount'];
                        break;
                    case 'transfer_out':
                        $total_transfers_out = $transaction['amount'];
                        break;
                }
            }
            
            $net_flow = $total_deposits + $total_transfers_in - $total_withdrawals - $total_phone_pay - $total_transfers_out;
            ?>
            <div class="hisab-stat-card">
                <h3><?php _e('Total Deposits', 'hisab-financial-tracker'); ?></h3>
                <span class="hisab-stat-value positive"><?php echo number_format($total_deposits, 2); ?></span>
            </div>
            <div class="hisab-stat-card">
                <h3><?php _e('Total Withdrawals', 'hisab-financial-tracker'); ?></h3>
                <span class="hisab-stat-value negative"><?php echo number_format($total_withdrawals, 2); ?></span>
            </div>
            <div class="hisab-stat-card">
                <h3><?php _e('Phone Pay', 'hisab-financial-tracker'); ?></h3>
                <span class="hisab-stat-value negative"><?php echo number_format($total_phone_pay, 2); ?></span>
            </div>
            <div class="hisab-stat-card">
                <h3><?php _e('Transfers In', 'hisab-financial-tracker'); ?></h3>
                <span class="hisab-stat-value positive"><?php echo number_format($total_transfers_in, 2); ?></span>
            </div>
            <div class="hisab-stat-card">
                <h3><?php _e('Transfers Out', 'hisab-financial-tracker'); ?></h3>
                <span class="hisab-stat-value negative"><?php echo number_format($total_transfers_out, 2); ?></span>
            </div>
            <div class="hisab-stat-card">
                <h3><?php _e('Net Flow', 'hisab-financial-tracker'); ?></h3>
                <span class="hisab-stat-value <?php echo $net_flow >= 0 ? 'positive' : 'negative'; ?>">
                    <?php echo number_format($net_flow, 2); ?>
                </span>
            </div>
        </div>
    </div>
    
    <?php endif; // End of available_years check ?>
</div>