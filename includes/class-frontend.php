<?php
/**
 * Frontend class for Hisab Financial Tracker
 */

if (!defined('ABSPATH')) {
    exit;
}

class HisabFrontend {
    
    private $database;
    
    public function __construct() {
        $this->database = new HisabDatabase();
    }
    
    public function render_dashboard($atts) {
        $atts = shortcode_atts(array(
            'months' => 6,
            'show_charts' => 'true',
            'show_recent' => 'true'
        ), $atts);
        
        $current_month = date('n');
        $current_year = date('Y');
        
        $monthly_summary = $this->database->get_monthly_summary($current_year, $current_month);
        $recent_transactions = $this->database->get_transactions(array('limit' => 5));
        
        ob_start();
        ?>
        <div class="hisab-frontend-dashboard">
            <div class="hisab-summary-cards">
                <div class="hisab-card">
                    <h3><?php _e('This Month', 'hisab-financial-tracker'); ?></h3>
                    <div class="hisab-summary-item income">
                        <span><?php _e('Income:', 'hisab-financial-tracker'); ?></span>
                        <strong><?php echo number_format($monthly_summary['income'], 2); ?></strong>
                    </div>
                    <div class="hisab-summary-item expense">
                        <span><?php _e('Expenses:', 'hisab-financial-tracker'); ?></span>
                        <strong><?php echo number_format($monthly_summary['expense'], 2); ?></strong>
                    </div>
                    <div class="hisab-summary-item net">
                        <span><?php _e('Net:', 'hisab-financial-tracker'); ?></span>
                        <strong class="<?php echo $monthly_summary['net'] >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo number_format($monthly_summary['net'], 2); ?>
                        </strong>
                    </div>
                </div>
            </div>
            
            <?php if ($atts['show_recent'] === 'true'): ?>
            <div class="hisab-recent-transactions">
                <h3><?php _e('Recent Transactions', 'hisab-financial-tracker'); ?></h3>
                <table class="hisab-table">
                    <thead>
                        <tr>
                            <th><?php _e('Date', 'hisab-financial-tracker'); ?></th>
                            <th><?php _e('Type', 'hisab-financial-tracker'); ?></th>
                            <th><?php _e('Description', 'hisab-financial-tracker'); ?></th>
                            <th><?php _e('Amount', 'hisab-financial-tracker'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_transactions as $transaction): ?>
                        <tr>
                            <td><?php echo date('M j', strtotime($transaction->transaction_date)); ?></td>
                            <td>
                                <span class="hisab-type-badge <?php echo $transaction->type; ?>">
                                    <?php echo ucfirst($transaction->type); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($transaction->description); ?></td>
                            <td class="hisab-amount <?php echo $transaction->type; ?>">
                                <?php echo number_format($transaction->amount, 2); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function render_income_chart($atts) {
        $atts = shortcode_atts(array(
            'months' => 6,
            'height' => '300'
        ), $atts);
        
        $trend_data = $this->database->get_trend_data('income', $atts['months']);
        
        ob_start();
        ?>
        <div class="hisab-chart-container">
            <canvas id="hisab-income-chart-<?php echo uniqid(); ?>" width="400" height="<?php echo $atts['height']; ?>"></canvas>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            const ctx = document.getElementById('hisab-income-chart-<?php echo uniqid(); ?>').getContext('2d');
            const data = <?php echo json_encode($trend_data); ?>;
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(d => d.month),
                    datasets: [{
                        label: '<?php _e('Income', 'hisab-financial-tracker'); ?>',
                        data: data.map(d => d.total),
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
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
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    public function render_expense_chart($atts) {
        $atts = shortcode_atts(array(
            'months' => 6,
            'height' => '300'
        ), $atts);
        
        $trend_data = $this->database->get_trend_data('expense', $atts['months']);
        
        ob_start();
        ?>
        <div class="hisab-chart-container">
            <canvas id="hisab-expense-chart-<?php echo uniqid(); ?>" width="400" height="<?php echo $atts['height']; ?>"></canvas>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            const ctx = document.getElementById('hisab-expense-chart-<?php echo uniqid(); ?>').getContext('2d');
            const data = <?php echo json_encode($trend_data); ?>;
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(d => d.month),
                    datasets: [{
                        label: '<?php _e('Expenses', 'hisab-financial-tracker'); ?>',
                        data: data.map(d => d.total),
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
        });
        </script>
        <?php
        return ob_get_clean();
    }
}
