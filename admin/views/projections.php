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
            <canvas id="hisab-projections-chart" width="800" height="400" 
                    data-projections="<?php echo esc_attr(json_encode($projections)); ?>"></canvas>
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
                    <button type="submit" class="button button-primary btn-saving-calculator">
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