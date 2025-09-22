<?php
/**
 * Projections view template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Financial Projections', 'hisab-financial-tracker'); ?></h1>
    
    <!-- Projections Chart -->
    <div class="hisab-projections-section">
        <h2><?php _e('12-Month Financial Projections', 'hisab-financial-tracker'); ?></h2>
        <div class="hisab-projections-chart">
            <canvas id="hisab-projections-chart" width="800" height="400"></canvas>
        </div>
    </div>
    
    <!-- Projections Table -->
    <div class="hisab-projections-table">
        <h3><?php _e('Monthly Projections', 'hisab-financial-tracker'); ?></h3>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Month', 'hisab-financial-tracker'); ?></th>
                    <th><?php _e('Projected Income', 'hisab-financial-tracker'); ?></th>
                    <th><?php _e('Projected Expenses', 'hisab-financial-tracker'); ?></th>
                    <th><?php _e('Projected Net', 'hisab-financial-tracker'); ?></th>
                    <th><?php _e('Status', 'hisab-financial-tracker'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projections as $projection): ?>
                    <tr>
                        <td><?php echo esc_html($projection['month_name']); ?></td>
                        <td class="hisab-amount income"><?php echo number_format($projection['projected_income'], 2); ?></td>
                        <td class="hisab-amount expense"><?php echo number_format($projection['projected_expense'], 2); ?></td>
                        <td class="hisab-amount <?php echo $projection['projected_net'] >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo number_format($projection['projected_net'], 2); ?>
                        </td>
                        <td>
                            <?php if ($projection['projected_net'] >= 0): ?>
                                <span class="hisab-status positive"><?php _e('Positive', 'hisab-financial-tracker'); ?></span>
                            <?php else: ?>
                                <span class="hisab-status negative"><?php _e('Deficit', 'hisab-financial-tracker'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Savings Calculator -->
    <div class="hisab-savings-calculator">
        <h3><?php _e('Savings Goal Calculator', 'hisab-financial-tracker'); ?></h3>
        <form id="hisab-savings-form" class="hisab-form">
            <div class="hisab-form-row">
                <div class="hisab-form-group">
                    <label for="target-amount"><?php _e('Target Amount', 'hisab-financial-tracker'); ?></label>
                    <input type="number" id="target-amount" name="target_amount" step="0.01" min="0" required>
                </div>
                <div class="hisab-form-group">
                    <label for="months-to-target"><?php _e('Months to Target', 'hisab-financial-tracker'); ?></label>
                    <input type="number" id="months-to-target" name="months_to_target" min="1" max="60" required>
                </div>
                <div class="hisab-form-group">
                    <button type="submit" class="button button-primary">
                        <?php _e('Calculate', 'hisab-financial-tracker'); ?>
                    </button>
                </div>
            </div>
        </form>
        
        <div id="hisab-savings-result" class="hisab-savings-result"></div>
    </div>
    
    <!-- Projection Summary -->
    <div class="hisab-projection-summary">
        <h3><?php _e('Projection Summary', 'hisab-financial-tracker'); ?></h3>
        <div class="hisab-summary-cards">
            <?php
            $total_projected_income = array_sum(array_column($projections, 'projected_income'));
            $total_projected_expense = array_sum(array_column($projections, 'projected_expense'));
            $total_projected_net = $total_projected_income - $total_projected_expense;
            $positive_months = count(array_filter($projections, function($p) { return $p['projected_net'] >= 0; }));
            $negative_months = count($projections) - $positive_months;
            ?>
            
            <div class="hisab-card">
                <h4><?php _e('Total Projected Income', 'hisab-financial-tracker'); ?></h4>
                <span class="hisab-amount income"><?php echo number_format($total_projected_income, 2); ?></span>
            </div>
            
            <div class="hisab-card">
                <h4><?php _e('Total Projected Expenses', 'hisab-financial-tracker'); ?></h4>
                <span class="hisab-amount expense"><?php echo number_format($total_projected_expense, 2); ?></span>
            </div>
            
            <div class="hisab-card">
                <h4><?php _e('Total Projected Net', 'hisab-financial-tracker'); ?></h4>
                <span class="hisab-amount <?php echo $total_projected_net >= 0 ? 'positive' : 'negative'; ?>">
                    <?php echo number_format($total_projected_net, 2); ?>
                </span>
            </div>
            
            <div class="hisab-card">
                <h4><?php _e('Positive Months', 'hisab-financial-tracker'); ?></h4>
                <span class="hisab-stat-value"><?php echo $positive_months; ?> / <?php echo count($projections); ?></span>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Projections chart
    const projectionsCtx = document.getElementById('hisab-projections-chart').getContext('2d');
    const projections = <?php echo json_encode($projections); ?>;
    
    const labels = projections.map(p => p.month_name);
    const incomeData = projections.map(p => p.projected_income);
    const expenseData = projections.map(p => p.projected_expense);
    const netData = projections.map(p => p.projected_net);
    
    new Chart(projectionsCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: '<?php _e('Projected Income', 'hisab-financial-tracker'); ?>',
                data: incomeData,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4
            }, {
                label: '<?php _e('Projected Expenses', 'hisab-financial-tracker'); ?>',
                data: expenseData,
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4
            }, {
                label: '<?php _e('Projected Net', 'hisab-financial-tracker'); ?>',
                data: netData,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
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
    
    // Savings calculator
    $('#hisab-savings-form').on('submit', function(e) {
        e.preventDefault();
        
        const targetAmount = parseFloat($('#target-amount').val());
        const monthsToTarget = parseInt($('#months-to-target').val());
        
        if (!targetAmount || !monthsToTarget) {
            alert('<?php _e('Please enter both target amount and months to target.', 'hisab-financial-tracker'); ?>');
            return;
        }
        
        $.ajax({
            url: hisab_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'hisab_calculate_savings',
                target_amount: targetAmount,
                months_to_target: monthsToTarget,
                nonce: hisab_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    const result = response.data;
                    let html = '<div class="hisab-savings-analysis">';
                    
                    if (result.achievable) {
                        html += '<div class="notice notice-success">';
                        html += '<p><strong><?php _e('Goal is achievable!', 'hisab-financial-tracker'); ?></strong></p>';
                        html += '<p><?php _e('Required monthly savings:', 'hisab-financial-tracker'); ?> <strong>' + result.required_monthly_savings.toFixed(2) + '</strong></p>';
                        html += '<p><?php _e('Current monthly savings:', 'hisab-financial-tracker'); ?> <strong>' + result.current_monthly_savings.toFixed(2) + '</strong></p>';
                        html += '</div>';
                    } else {
                        html += '<div class="notice notice-error">';
                        html += '<p><strong><?php _e('Goal may be difficult to achieve.', 'hisab-financial-tracker'); ?></strong></p>';
                        html += '<p><?php _e('Required monthly savings:', 'hisab-financial-tracker'); ?> <strong>' + result.required_monthly_savings.toFixed(2) + '</strong></p>';
                        html += '<p><?php _e('Current monthly savings:', 'hisab-financial-tracker'); ?> <strong>' + result.current_monthly_savings.toFixed(2) + '</strong></p>';
                        html += '<p><?php _e('You need to increase savings by:', 'hisab-financial-tracker'); ?> <strong>' + (result.required_monthly_savings - result.current_monthly_savings).toFixed(2) + '</strong></p>';
                        html += '</div>';
                    }
                    
                    html += '</div>';
                    $('#hisab-savings-result').html(html);
                } else {
                    $('#hisab-savings-result').html('<div class="notice notice-error"><p>' + response.message + '</p></div>');
                }
            },
            error: function() {
                $('#hisab-savings-result').html('<div class="notice notice-error"><p><?php _e('An error occurred while calculating savings.', 'hisab-financial-tracker'); ?></p></div>');
            }
        });
    });
});
</script>
