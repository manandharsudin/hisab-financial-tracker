<?php
/**
 * Dashboard view template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Financial Dashboard', 'hisab-financial-tracker'); ?></h1>

    <!-- Recent Transactions -->
    <div class="hisab-recent-transactions">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="margin: 0;"><?php _e('Recent Transactions', 'hisab-financial-tracker'); ?></h3>
            <a href="<?php echo admin_url('admin.php?page=hisab-transactions'); ?>" class="button button-secondary">
                <?php _e('View All Transactions', 'hisab-financial-tracker'); ?>
            </a>
        </div>
        <div class="hisab-table-container">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Date', 'hisab-financial-tracker'); ?></th>
                        <th><?php _e('Type', 'hisab-financial-tracker'); ?></th>
                        <th><?php _e('Description', 'hisab-financial-tracker'); ?></th>
                        <th><?php _e('Category', 'hisab-financial-tracker'); ?></th>
                        <th><?php _e('Owner', 'hisab-financial-tracker'); ?></th>
                        <th><?php _e('Payment', 'hisab-financial-tracker'); ?></th>
                        <th><?php _e('Amount', 'hisab-financial-tracker'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_transactions)): ?>
                        <tr>
                            <td colspan="7" class="hisab-no-data">
                                <?php _e('No transactions found. Add your first transaction!', 'hisab-financial-tracker'); ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_transactions as $transaction): ?>
                            <tr>
                                <td>
                                    <div class="hisab-date-display">
                                        <div class="ad-date"><?php echo date(HISAB_DATE_FORMAT, strtotime($transaction->transaction_date)); ?></div>
                                        <?php 
                                        $show_dual_dates = get_option('hisab_show_dual_dates', 1);
                                        if ($show_dual_dates && isset($transaction->bs_year) && isset($transaction->bs_month) && isset($transaction->bs_day)) {
                                            $bs_month_name = HisabNepaliDate::get_bs_months($transaction->bs_month);
                                            echo '<div class="bs-date">' . $bs_month_name . ' ' . $transaction->bs_day . ', ' . $transaction->bs_year . '</div>';
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="hisab-type-badge <?php echo $transaction->type; ?>">
                                        <?php echo ucfirst($transaction->type); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html($transaction->description); ?></td>
                                <td>
                                    <?php if ($transaction->category_name): ?>
                                        <span class="hisab-category-badge" style="background-color: <?php echo esc_attr(isset($transaction->category_color) && $transaction->category_color ? $transaction->category_color : '#6c757d'); ?>">
                                            <?php echo esc_html($transaction->category_name); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="hisab-category-badge"><?php _e('Uncategorized', 'hisab-financial-tracker'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($transaction->owner_name): ?>
                                        <span class="hisab-owner-badge" style="background-color: <?php echo esc_attr(isset($transaction->owner_color) && $transaction->owner_color ? $transaction->owner_color : '#6c757d'); ?>">
                                            <?php echo esc_html($transaction->owner_name); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="hisab-owner-badge hisab-no-owner"><?php _e('No Owner', 'hisab-financial-tracker'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($transaction->payment_method): ?>
                                        <span class="hisab-payment-method">
                                            <?php echo esc_html(ucfirst(str_replace('_', ' ', $transaction->payment_method))); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="hisab-payment-method hisab-no-payment"><?php _e('No Payment Method', 'hisab-financial-tracker'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="hisab-amount <?php echo $transaction->type; ?>">
                                    <?php echo HISAB_CURRENCY_SYMBOL . number_format($transaction->amount, 2); ?>
                                    <?php if ($transaction->transaction_tax || $transaction->transaction_discount): ?>
                                        <div class="hisab-amount-details">
                                            <?php if ($transaction->transaction_tax): ?>
                                                <small class="tax">+Tax: <?php echo HISAB_CURRENCY_SYMBOL . number_format($transaction->transaction_tax, 2); ?></small>
                                            <?php endif; ?>
                                            <?php if ($transaction->transaction_discount): ?>
                                                <small class="discount">-Disc: <?php echo HISAB_CURRENCY_SYMBOL . number_format($transaction->transaction_discount, 2); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="hisab-summary-cards">
        <div class="hisab-card">
            <div class="hisab-card-header">
                <h3><?php _e('This Month', 'hisab-financial-tracker'); ?></h3>
                <span class="hisab-card-date">
                    <?php 
                    $default_calendar = get_option('hisab_default_calendar', 'ad');
                    if ($default_calendar === 'bs') {
                        $current_bs = HisabNepaliDate::get_current_bs_date();
                        $bs_month_name = HisabNepaliDate::get_bs_months($current_bs['month']);
                        echo $bs_month_name . ' ' . $current_bs['year'];
                    } else {
                        echo date('F Y');
                    }
                    ?>
                </span>
            </div>
            <div class="hisab-card-content">
                <div class="hisab-summary-item income">
                    <span class="hisab-label"><?php _e('Income', 'hisab-financial-tracker'); ?></span>
                    <span class="hisab-amount"><?php echo number_format($monthly_summary['income'], 2); ?></span>
                </div>
                <div class="hisab-summary-item expense">
                    <span class="hisab-label"><?php _e('Expenses', 'hisab-financial-tracker'); ?></span>
                    <span class="hisab-amount"><?php echo number_format($monthly_summary['expense'], 2); ?></span>
                </div>
                <div class="hisab-summary-item net">
                    <span class="hisab-label"><?php _e('Net', 'hisab-financial-tracker'); ?></span>
                    <span class="hisab-amount <?php echo $monthly_summary['net'] >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo number_format($monthly_summary['net'], 2); ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="hisab-card">
            <div class="hisab-card-header">
                <h3><?php _e('Quick Stats', 'hisab-financial-tracker'); ?></h3>
                <span class="hisab-card-date">
                    <?php 
                    if ($default_calendar === 'bs') {
                        $current_bs = HisabNepaliDate::get_current_bs_date();
                        $bs_month_name = HisabNepaliDate::get_bs_months($current_bs['month']);
                        echo $bs_month_name . ' ' . $current_bs['year'];
                    } else {
                        echo date('F Y');
                    }
                    ?>
                </span>
            </div>
            <div class="hisab-card-content">
                <div class="hisab-stat-item">
                    <span class="hisab-stat-label"><?php _e('Income Transactions', 'hisab-financial-tracker'); ?></span>
                    <span class="hisab-stat-value"><?php echo $monthly_summary['income_count']; ?></span>
                </div>
                <div class="hisab-stat-item">
                    <span class="hisab-stat-label"><?php _e('Expense Transactions', 'hisab-financial-tracker'); ?></span>
                    <span class="hisab-stat-value"><?php echo $monthly_summary['expense_count']; ?></span>
                </div>
                <div class="hisab-stat-item">
                    <span class="hisab-stat-label"><?php _e('Total Transactions', 'hisab-financial-tracker'); ?></span>
                    <span class="hisab-stat-value"><?php echo $monthly_summary['income_count'] + $monthly_summary['expense_count']; ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Section -->
    <div class="hisab-charts-section">
        <div class="hisab-chart-container">
            <h3><?php _e('Income vs Expense Trend (Last 6 Months)', 'hisab-financial-tracker'); ?></h3>
            <canvas id="hisab-trend-chart" width="400" height="200" 
                    data-income-data="<?php echo esc_attr(json_encode(array_column($income_trend, 'total'))); ?>"
                    data-expense-data="<?php echo esc_attr(json_encode(array_column($expense_trend, 'total'))); ?>"
                    data-labels="<?php echo esc_attr(json_encode(array_column($income_trend, 'month'))); ?>"></canvas>
        </div>
    </div>
</div>

<!-- Transaction Details Modal (same as in add-transaction.php) -->
<div id="transaction-details-modal" class="hisab-modal" style="display: none;">
    <div class="hisab-modal-content">
        <div class="hisab-modal-header">
            <h3 id="modal-title"><?php _e('Transaction Details', 'hisab-financial-tracker'); ?></h3>
            <span class="hisab-modal-close">&times;</span>
        </div>
        <div class="hisab-modal-body">
            <div id="transaction-info" class="hisab-transaction-info">
                <!-- Transaction summary will be loaded here -->
            </div>
            
            <div class="hisab-details-form">
                <h4><?php _e('Itemized Details', 'hisab-financial-tracker'); ?></h4>
                <div id="details-items">
                    <!-- Dynamic items will be added here -->
                </div>
                <button type="button" class="button button-secondary" id="add-detail-item">
                    <?php _e('+ Add Item', 'hisab-financial-tracker'); ?>
                </button>
            </div>
            
            <div class="hisab-details-summary">
                <div class="summary-row">
                    <span><?php _e('Subtotal:', 'hisab-financial-tracker'); ?></span>
                    <span id="details-subtotal">0.00</span>
                </div>
                <div class="summary-row">
                    <span><?php _e('Tax:', 'hisab-financial-tracker'); ?></span>
                    <span id="details-tax">0.00</span>
                </div>
                <div class="summary-row">
                    <span><?php _e('Discount:', 'hisab-financial-tracker'); ?></span>
                    <span id="details-discount">0.00</span>
                </div>
                <div class="summary-row total-row">
                    <span><?php _e('Grand Total:', 'hisab-financial-tracker'); ?></span>
                    <span id="details-grand-total">0.00</span>
                </div>
            </div>
            
            <div id="details-messages"></div>
        </div>
        <div class="hisab-modal-footer">
            <button type="button" class="button button-primary" id="save-details">
                <?php _e('Save Details', 'hisab-financial-tracker'); ?>
            </button>
            <button type="button" class="button" id="cancel-details">
                <?php _e('Cancel', 'hisab-financial-tracker'); ?>
            </button>
        </div>
    </div>
</div>

