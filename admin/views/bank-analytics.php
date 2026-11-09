<?php
/**
 * Bank Analytics view template
 *
 * Provides comprehensive bank analytics with dual-calendar (AD/BS) filtering,
 * interactive Chart.js trend visualizations, and multi-currency metrics.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables passed from HisabAdmin::render_bank_analytics():
// $calendar_type, $selected_year, $selected_month, $selected_account_id, $selected_currency
// $available_years, $available_months, $available_accounts, $date_range
// $trend_data, $transaction_summary, $account_summary, $balance_trends, $monthly_summary

if (!isset($calendar_type)) $calendar_type = get_option('hisab_default_calendar', 'ad');
if (!isset($selected_year)) $selected_year = null;
if (!isset($selected_month)) $selected_month = null;
if (!isset($selected_account_id)) $selected_account_id = null;
if (!isset($selected_currency)) $selected_currency = null;
if (!isset($available_years)) $available_years = array();
if (!isset($available_months)) $available_months = array();
if (!isset($available_accounts)) $available_accounts = array();
if (!isset($date_range)) $date_range = array('start_date' => null, 'end_date' => null);
if (!isset($trend_data)) $trend_data = array('months' => array(), 'data' => array(), 'datasets' => array());
if (!isset($transaction_summary)) $transaction_summary = array();
if (!isset($account_summary)) $account_summary = array();
if (!isset($balance_trends)) $balance_trends = array('months' => array(), 'accounts' => array());
if (!isset($monthly_summary)) $monthly_summary = array(
    'deposits' => 0.0, 'withdrawals' => 0.0, 'phone_pay' => 0.0,
    'transfers_in' => 0.0, 'transfers_out' => 0.0, 'net_flow' => 0.0, 'total_transactions' => 0,
    'by_currency' => array()
);

// Determine period title string
if ($calendar_type === 'bs') {
    $bs_month_name = ($selected_month && class_exists('HisabNepaliDate')) ? HisabNepaliDate::get_bs_months($selected_month) : '';
    if ($selected_year && $selected_month) {
        $period_title = esc_html($bs_month_name . ' ' . $selected_year . ' (BS)');
    } elseif ($selected_year) {
        $period_title = esc_html($selected_year . ' (BS)');
    } else {
        $period_title = __('All Time (BS)', 'hisab-financial-tracker');
    }
} else {
    if ($selected_year && $selected_month) {
        $period_title = esc_html(date('F Y', mktime(0, 0, 0, $selected_month, 1, $selected_year)) . ' (AD)');
    } elseif ($selected_year) {
        $period_title = esc_html($selected_year . ' (AD)');
    } else {
        $period_title = __('All Time (AD)', 'hisab-financial-tracker');
    }
}

// Find selected account details if any
$selected_account_name = '';
if ($selected_account_id) {
    foreach ($available_accounts as $acc) {
        if (intval($acc->id) === intval($selected_account_id)) {
            $selected_account_name = $acc->account_name . ' (' . $acc->currency . ')';
            break;
        }
    }
}
?>

<div class="wrap hisab-wrap">
    <h1 class="wp-heading-inline"><?php _e('Bank Analytics', 'hisab-financial-tracker'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=hisab-add-bank-transaction'); ?>" class="page-title-action"><?php _e('Add Bank Transaction', 'hisab-financial-tracker'); ?></a>
    <a href="<?php echo admin_url('admin.php?page=hisab-bank-accounts'); ?>" class="page-title-action"><?php _e('Manage Accounts', 'hisab-financial-tracker'); ?></a>
    <hr class="wp-header-end">
    
    <?php if (empty($date_range['start_date']) && empty($available_years)): ?>
        <div class="notice notice-info">
            <p><strong><?php _e('No Bank Transactions Found', 'hisab-financial-tracker'); ?></strong></p>
            <p><?php _e('There are no bank transactions in the system yet. Please add bank transactions to see analytics.', 'hisab-financial-tracker'); ?></p>
            <p>
                <a href="<?php echo admin_url('admin.php?page=hisab-add-bank-transaction'); ?>" class="button button-primary">
                    <?php _e('Add Bank Transaction', 'hisab-financial-tracker'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=hisab-bank-accounts'); ?>" class="button">
                    <?php _e('Manage Bank Accounts', 'hisab-financial-tracker'); ?>
                </a>
            </p>
        </div>
    <?php else: ?>
    
    <!-- Filter Controls -->
    <div class="hisab-analytics-filters">
        <form id="hisab-bank-analytics-filter-form" method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
            <input type="hidden" name="page" value="hisab-bank-analytics">
            
            <div class="hisab-filter-group">
                <label for="hisab-calendar-type-filter"><?php _e('Calendar:', 'hisab-financial-tracker'); ?></label>
                <select id="hisab-calendar-type-filter" name="calendar_type">
                    <option value="ad" <?php selected($calendar_type, 'ad'); ?>><?php _e('AD (Gregorian)', 'hisab-financial-tracker'); ?></option>
                    <option value="bs" <?php selected($calendar_type, 'bs'); ?>><?php _e('BS (Bikram Sambat)', 'hisab-financial-tracker'); ?></option>
                </select>
            </div>
            
            <div class="hisab-filter-group">
                <label for="year-filter"><?php _e('Year:', 'hisab-financial-tracker'); ?></label>
                <select id="year-filter" name="year">
                    <option value=""><?php _e('All Years', 'hisab-financial-tracker'); ?></option>
                    <?php foreach ($available_years as $year): ?>
                        <option value="<?php echo esc_attr($year); ?>" <?php selected($selected_year, $year); ?>>
                            <?php echo esc_html($year); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="hisab-filter-group">
                <label for="month-filter"><?php _e('Month:', 'hisab-financial-tracker'); ?></label>
                <select id="month-filter" name="month" <?php echo !$selected_year ? 'disabled' : ''; ?>>
                    <option value=""><?php _e('All Months', 'hisab-financial-tracker'); ?></option>
                    <?php foreach ($available_months as $month): ?>
                        <option value="<?php echo esc_attr($month['value']); ?>" <?php selected($selected_month, $month['value']); ?>>
                            <?php echo esc_html($month['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="hisab-filter-group">
                <label for="account-filter"><?php _e('Account:', 'hisab-financial-tracker'); ?></label>
                <select id="account-filter" name="account_id">
                    <option value=""><?php _e('All Accounts', 'hisab-financial-tracker'); ?></option>
                    <?php foreach ($available_accounts as $acc): ?>
                        <option value="<?php echo esc_attr($acc->id); ?>" <?php selected($selected_account_id, $acc->id); ?>>
                            <?php echo esc_html($acc->account_name . ' (' . $acc->bank_name . ' - ' . $acc->currency . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="hisab-filter-group">
                <label for="currency-filter"><?php _e('Currency:', 'hisab-financial-tracker'); ?></label>
                <select id="currency-filter" name="currency">
                    <option value=""><?php _e('All Currencies', 'hisab-financial-tracker'); ?></option>
                    <option value="NPR" <?php selected($selected_currency, 'NPR'); ?>><?php _e('NPR (₹)', 'hisab-financial-tracker'); ?></option>
                    <option value="USD" <?php selected($selected_currency, 'USD'); ?>><?php _e('USD ($)', 'hisab-financial-tracker'); ?></option>
                </select>
            </div>
            
            <div class="hisab-filter-group hisab-filter-actions">
                <button type="submit" class="button button-primary"><?php _e('Apply Filters', 'hisab-financial-tracker'); ?></button>
                <a href="<?php echo admin_url('admin.php?page=hisab-bank-analytics'); ?>" class="button"><?php _e('Clear Filters', 'hisab-financial-tracker'); ?></a>
            </div>
        </form>
        
        <div class="hisab-date-range-info">
            <p>
                <strong><?php _e('Period:', 'hisab-financial-tracker'); ?></strong> 
                <span class="hisab-badge"><?php echo $period_title; ?></span>
                <?php if ($selected_account_name): ?>
                    &bull; <strong><?php _e('Account:', 'hisab-financial-tracker'); ?></strong> 
                    <span><?php echo esc_html($selected_account_name); ?></span>
                <?php endif; ?>
                <?php if ($selected_currency): ?>
                    &bull; <strong><?php _e('Currency:', 'hisab-financial-tracker'); ?></strong> 
                    <span class="hisab-currency-badge hisab-currency-<?php echo strtolower($selected_currency); ?>"><?php echo esc_html($selected_currency); ?></span>
                <?php endif; ?>
                <?php if (!empty($date_range['start_date']) && !empty($date_range['end_date'])): ?>
                    &bull; <strong><?php _e('AD Span:', 'hisab-financial-tracker'); ?></strong> 
                    <?php echo esc_html(date('M j, Y', strtotime($date_range['start_date'])) . ' - ' . date('M j, Y', strtotime($date_range['end_date']))); ?>
                <?php endif; ?>
            </p>
        </div>
    </div>
    
    <!-- Summary Metrics -->
    <?php
    $by_currency = !empty($monthly_summary['by_currency']) ? $monthly_summary['by_currency'] : array();
    $has_multiple_currencies = (empty($selected_currency) && count($by_currency) > 1);
    ?>
    
    <?php if ($has_multiple_currencies): ?>
        <?php foreach ($by_currency as $curr_code => $curr_stats): ?>
            <div class="hisab-analytics-section">
                <h2>
                    <?php echo esc_html($curr_code); ?> <?php _e('Summary', 'hisab-financial-tracker'); ?> - <?php echo $period_title; ?>
                    <span class="hisab-currency-badge hisab-currency-<?php echo strtolower($curr_code); ?>"><?php echo esc_html($curr_code); ?></span>
                </h2>
                
                <div class="hisab-monthly-stats">
                    <div class="hisab-stat-card">
                        <h3><?php _e('Total Deposits', 'hisab-financial-tracker'); ?></h3>
                        <span class="hisab-stat-value positive"><?php echo esc_html($curr_stats['symbol'] . ' ' . number_format($curr_stats['deposits'], 2)); ?></span>
                    </div>
                    <div class="hisab-stat-card">
                        <h3><?php _e('Total Withdrawals', 'hisab-financial-tracker'); ?></h3>
                        <span class="hisab-stat-value negative"><?php echo esc_html($curr_stats['symbol'] . ' ' . number_format($curr_stats['withdrawals'], 2)); ?></span>
                    </div>
                    <div class="hisab-stat-card">
                        <h3><?php _e('Phone Pay', 'hisab-financial-tracker'); ?></h3>
                        <span class="hisab-stat-value negative"><?php echo esc_html($curr_stats['symbol'] . ' ' . number_format($curr_stats['phone_pay'], 2)); ?></span>
                    </div>
                    <div class="hisab-stat-card">
                        <h3><?php _e('Transfers In', 'hisab-financial-tracker'); ?></h3>
                        <span class="hisab-stat-value positive"><?php echo esc_html($curr_stats['symbol'] . ' ' . number_format($curr_stats['transfers_in'], 2)); ?></span>
                    </div>
                    <div class="hisab-stat-card">
                        <h3><?php _e('Transfers Out', 'hisab-financial-tracker'); ?></h3>
                        <span class="hisab-stat-value negative"><?php echo esc_html($curr_stats['symbol'] . ' ' . number_format($curr_stats['transfers_out'], 2)); ?></span>
                    </div>
                    <div class="hisab-stat-card">
                        <h3><?php _e('Net Flow', 'hisab-financial-tracker'); ?></h3>
                        <span class="hisab-stat-value <?php echo $curr_stats['net_flow'] >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo esc_html(($curr_stats['net_flow'] >= 0 ? '+' : '-') . $curr_stats['symbol'] . ' ' . number_format(abs($curr_stats['net_flow']), 2)); ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <?php
        $primary_currency = $selected_currency ?: (!empty($by_currency) ? array_keys($by_currency)[0] : 'NPR');
        $currency_symbol = ($primary_currency === 'USD') ? '$' : '₹';
        ?>
        <div class="hisab-analytics-section">
            <h2>
                <?php _e('Cash Flow Summary', 'hisab-financial-tracker'); ?> - <?php echo $period_title; ?>
                <span class="hisab-currency-badge hisab-currency-<?php echo strtolower($primary_currency); ?>"><?php echo esc_html($primary_currency); ?></span>
            </h2>
            
            <div class="hisab-monthly-stats">
                <div class="hisab-stat-card">
                    <h3><?php _e('Total Deposits', 'hisab-financial-tracker'); ?></h3>
                    <span class="hisab-stat-value positive"><?php echo esc_html($currency_symbol . ' ' . number_format($monthly_summary['deposits'], 2)); ?></span>
                </div>
                <div class="hisab-stat-card">
                    <h3><?php _e('Total Withdrawals', 'hisab-financial-tracker'); ?></h3>
                    <span class="hisab-stat-value negative"><?php echo esc_html($currency_symbol . ' ' . number_format($monthly_summary['withdrawals'], 2)); ?></span>
                </div>
                <div class="hisab-stat-card">
                    <h3><?php _e('Phone Pay', 'hisab-financial-tracker'); ?></h3>
                    <span class="hisab-stat-value negative"><?php echo esc_html($currency_symbol . ' ' . number_format($monthly_summary['phone_pay'], 2)); ?></span>
                </div>
                <div class="hisab-stat-card">
                    <h3><?php _e('Transfers In', 'hisab-financial-tracker'); ?></h3>
                    <span class="hisab-stat-value positive"><?php echo esc_html($currency_symbol . ' ' . number_format($monthly_summary['transfers_in'], 2)); ?></span>
                </div>
                <div class="hisab-stat-card">
                    <h3><?php _e('Transfers Out', 'hisab-financial-tracker'); ?></h3>
                    <span class="hisab-stat-value negative"><?php echo esc_html($currency_symbol . ' ' . number_format($monthly_summary['transfers_out'], 2)); ?></span>
                </div>
                <div class="hisab-stat-card">
                    <h3><?php _e('Net Flow', 'hisab-financial-tracker'); ?></h3>
                    <span class="hisab-stat-value <?php echo $monthly_summary['net_flow'] >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo esc_html(($monthly_summary['net_flow'] >= 0 ? '+' : '-') . $currency_symbol . ' ' . number_format(abs($monthly_summary['net_flow']), 2)); ?>
                    </span>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Bank Transaction Trends Chart -->
    <div class="hisab-analytics-section">
        <h2><?php _e('Bank Transaction Trends', 'hisab-financial-tracker'); ?> - <?php echo $period_title; ?></h2>
        <div class="hisab-trend-chart">
            <canvas id="hisab-bank-trend-chart" width="800" height="400" 
                    data-trend-data="<?php echo esc_attr(json_encode($trend_data)); ?>"></canvas>
        </div>
    </div>
    
    <!-- Transaction Types & Account Breakdown -->
    <div class="hisab-category-breakdown">
        <div class="hisab-category-section">
            <h3><?php _e('Transaction Types', 'hisab-financial-tracker'); ?> - <?php echo $period_title; ?></h3>
            <div class="hisab-category-chart">
                <canvas id="hisab-transaction-types" width="400" height="300" 
                        data-transaction-types="<?php echo esc_attr(json_encode($transaction_summary)); ?>"></canvas>
            </div>
        </div>
        
        <div class="hisab-category-section">
            <h3><?php _e('Account Activity', 'hisab-financial-tracker'); ?> - <?php echo $period_title; ?></h3>
            <div class="hisab-category-chart">
                <canvas id="hisab-account-activity" width="400" height="300" 
                        data-account-activity="<?php echo esc_attr(json_encode($account_summary)); ?>"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Account Balance Trends Chart -->
    <div class="hisab-analytics-section">
        <h2><?php _e('Account Balance Trends', 'hisab-financial-tracker'); ?> - <?php echo $period_title; ?></h2>
        <div class="hisab-balance-chart">
            <canvas id="hisab-balance-trend-chart" width="800" height="400" 
                    data-balance-trends="<?php echo esc_attr(json_encode($balance_trends)); ?>"></canvas>
        </div>
    </div>
    
    <?php endif; // End of transactions check ?>
</div>
