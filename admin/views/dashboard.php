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
    
    <!-- Summary Cards -->
    <div class="hisab-summary-cards">
        <div class="hisab-card">
            <div class="hisab-card-header">
                <h3><?php _e('This Month', 'hisab-financial-tracker'); ?></h3>
                <span class="hisab-card-date"><?php echo date('F Y'); ?></span>
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
            <canvas id="hisab-trend-chart" width="400" height="200"></canvas>
        </div>
    </div>
    
    <!-- Recent Transactions -->
    <div class="hisab-recent-transactions">
        <h3><?php _e('Recent Transactions', 'hisab-financial-tracker'); ?></h3>
        <div class="hisab-table-container">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Date', 'hisab-financial-tracker'); ?></th>
                        <th><?php _e('Type', 'hisab-financial-tracker'); ?></th>
                        <th><?php _e('Description', 'hisab-financial-tracker'); ?></th>
                        <th><?php _e('Category', 'hisab-financial-tracker'); ?></th>
                        <th><?php _e('Amount', 'hisab-financial-tracker'); ?></th>
                        <th><?php _e('Actions', 'hisab-financial-tracker'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_transactions)): ?>
                        <tr>
                            <td colspan="6" class="hisab-no-data">
                                <?php _e('No transactions found. Add your first transaction!', 'hisab-financial-tracker'); ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_transactions as $transaction): ?>
                            <tr>
                                <td><?php echo date('M j, Y', strtotime($transaction->transaction_date)); ?></td>
                                <td>
                                    <span class="hisab-type-badge <?php echo $transaction->type; ?>">
                                        <?php echo ucfirst($transaction->type); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html($transaction->description); ?></td>
                                <td>
                                    <?php if ($transaction->category_name): ?>
                                        <span class="hisab-category-badge" style="background-color: <?php echo $transaction->category_color; ?>">
                                            <?php echo esc_html($transaction->category_name); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="hisab-category-badge"><?php _e('Uncategorized', 'hisab-financial-tracker'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="hisab-amount <?php echo $transaction->type; ?>">
                                    <?php echo number_format($transaction->amount, 2); ?>
                                </td>
                                <td>
                                    <button class="button button-small hisab-delete-transaction" data-id="<?php echo $transaction->id; ?>">
                                        <?php _e('Delete', 'hisab-financial-tracker'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Initialize trend chart
    const trendCtx = document.getElementById('hisab-trend-chart').getContext('2d');
    
    const incomeData = <?php echo json_encode(array_column($income_trend, 'total')); ?>;
    const expenseData = <?php echo json_encode(array_column($expense_trend, 'total')); ?>;
    const labels = <?php echo json_encode(array_column($income_trend, 'month')); ?>;
    
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: '<?php _e('Income', 'hisab-financial-tracker'); ?>',
                data: incomeData,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4
            }, {
                label: '<?php _e('Expenses', 'hisab-financial-tracker'); ?>',
                data: expenseData,
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Delete transaction handler
    $('.hisab-delete-transaction').on('click', function() {
        if (confirm('<?php _e('Are you sure you want to delete this transaction?', 'hisab-financial-tracker'); ?>')) {
            const transactionId = $(this).data('id');
            
            $.ajax({
                url: hisab_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'hisab_delete_transaction',
                    id: transactionId,
                    nonce: hisab_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                }
            });
        }
    });
});
</script>
