<?php
/**
 * Analytics view template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Financial Analytics', 'hisab-financial-tracker'); ?></h1>
    
    <!-- Yearly Overview -->
    <div class="hisab-analytics-section">
        <h2><?php _e('Yearly Overview', 'hisab-financial-tracker'); ?> - <?php echo $current_year; ?></h2>
        
        <div class="hisab-yearly-chart">
            <canvas id="hisab-yearly-chart" width="800" height="400"></canvas>
        </div>
        
        <div class="hisab-yearly-stats">
            <?php
            $total_income = array_sum(array_column($yearly_data, 'income'));
            $total_expense = array_sum(array_column($yearly_data, 'expense'));
            $net_profit = $total_income - $total_expense;
            ?>
            <div class="hisab-stat-card">
                <h3><?php _e('Total Income', 'hisab-financial-tracker'); ?></h3>
                <span class="hisab-stat-value income"><?php echo number_format($total_income, 2); ?></span>
            </div>
            <div class="hisab-stat-card">
                <h3><?php _e('Total Expenses', 'hisab-financial-tracker'); ?></h3>
                <span class="hisab-stat-value expense"><?php echo number_format($total_expense, 2); ?></span>
            </div>
            <div class="hisab-stat-card">
                <h3><?php _e('Net Profit', 'hisab-financial-tracker'); ?></h3>
                <span class="hisab-stat-value <?php echo $net_profit >= 0 ? 'positive' : 'negative'; ?>">
                    <?php echo number_format($net_profit, 2); ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Category Breakdown -->
    <div class="hisab-category-breakdown">
        <div class="hisab-category-section">
            <h3><?php _e('Income Categories', 'hisab-financial-tracker'); ?></h3>
            <div class="hisab-category-chart">
                <canvas id="hisab-income-categories" width="400" height="300"></canvas>
            </div>
        </div>
        
        <div class="hisab-category-section">
            <h3><?php _e('Expense Categories', 'hisab-financial-tracker'); ?></h3>
            <div class="hisab-category-chart">
                <canvas id="hisab-expense-categories" width="400" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Category Tables -->
    <div class="hisab-category-tables">
        <div class="hisab-category-table">
            <h3><?php _e('Income by Category', 'hisab-financial-tracker'); ?></h3>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Category', 'hisab-financial-tracker'); ?></th>
                        <th><?php _e('Amount', 'hisab-financial-tracker'); ?></th>
                        <th><?php _e('Transactions', 'hisab-financial-tracker'); ?></th>
                        <th><?php _e('Percentage', 'hisab-financial-tracker'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_income_cat = array_sum(array_column($income_categories, 'total'));
                    foreach ($income_categories as $category): 
                        $percentage = $total_income_cat > 0 ? ($category->total / $total_income_cat) * 100 : 0;
                    ?>
                        <tr>
                            <td>
                                <span class="hisab-category-badge" style="background-color: <?php echo $category->category_color; ?>">
                                    <?php echo esc_html($category->category_name); ?>
                                </span>
                            </td>
                            <td class="hisab-amount income"><?php echo number_format($category->total, 2); ?></td>
                            <td><?php echo $category->count; ?></td>
                            <td><?php echo number_format($percentage, 1); ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="hisab-category-table">
            <h3><?php _e('Expenses by Category', 'hisab-financial-tracker'); ?></h3>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Category', 'hisab-financial-tracker'); ?></th>
                        <th><?php _e('Amount', 'hisab-financial-tracker'); ?></th>
                        <th><?php _e('Transactions', 'hisab-financial-tracker'); ?></th>
                        <th><?php _e('Percentage', 'hisab-financial-tracker'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_expense_cat = array_sum(array_column($expense_categories, 'total'));
                    foreach ($expense_categories as $category): 
                        $percentage = $total_expense_cat > 0 ? ($category->total / $total_expense_cat) * 100 : 0;
                    ?>
                        <tr>
                            <td>
                                <span class="hisab-category-badge" style="background-color: <?php echo $category->category_color; ?>">
                                    <?php echo esc_html($category->category_name); ?>
                                </span>
                            </td>
                            <td class="hisab-amount expense"><?php echo number_format($category->total, 2); ?></td>
                            <td><?php echo $category->count; ?></td>
                            <td><?php echo number_format($percentage, 1); ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Yearly chart
    const yearlyCtx = document.getElementById('hisab-yearly-chart').getContext('2d');
    const yearlyData = <?php echo json_encode($yearly_data); ?>;
    
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const incomeData = [];
    const expenseData = [];
    
    for (let i = 1; i <= 12; i++) {
        incomeData.push(yearlyData[i] ? yearlyData[i].income : 0);
        expenseData.push(yearlyData[i] ? yearlyData[i].expense : 0);
    }
    
    new Chart(yearlyCtx, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: '<?php _e('Income', 'hisab-financial-tracker'); ?>',
                data: incomeData,
                backgroundColor: 'rgba(40, 167, 69, 0.8)',
                borderColor: '#28a745',
                borderWidth: 1
            }, {
                label: '<?php _e('Expenses', 'hisab-financial-tracker'); ?>',
                data: expenseData,
                backgroundColor: 'rgba(220, 53, 69, 0.8)',
                borderColor: '#dc3545',
                borderWidth: 1
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
    
    // Income categories pie chart
    const incomeCtx = document.getElementById('hisab-income-categories').getContext('2d');
    const incomeCategories = <?php echo json_encode($income_categories); ?>;
    
    new Chart(incomeCtx, {
        type: 'doughnut',
        data: {
            labels: incomeCategories.map(cat => cat.category_name),
            datasets: [{
                data: incomeCategories.map(cat => cat.total),
                backgroundColor: incomeCategories.map(cat => cat.category_color),
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Expense categories pie chart
    const expenseCtx = document.getElementById('hisab-expense-categories').getContext('2d');
    const expenseCategories = <?php echo json_encode($expense_categories); ?>;
    
    new Chart(expenseCtx, {
        type: 'doughnut',
        data: {
            labels: expenseCategories.map(cat => cat.category_name),
            datasets: [{
                data: expenseCategories.map(cat => cat.total),
                backgroundColor: expenseCategories.map(cat => cat.category_color),
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
