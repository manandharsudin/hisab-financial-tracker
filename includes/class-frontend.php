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
    
    public function render_monthly_summary($atts) {
        $year = intval($atts['year']);
        $month = intval($atts['month']);
        $show_net = $atts['show_net'] === 'true';
        $currency = $atts['currency'];
        
        $database = new HisabDatabase();
        $monthly_summary = $database->get_monthly_summary($year, $month);
        
        ob_start();
        ?>
        <div class="hisab-monthly-summary">
            <h3>
                <?php 
                $default_calendar = get_option('hisab_default_calendar', 'ad');
                if ($default_calendar === 'bs') {
                    // Convert AD month/year to BS for display
                    $bs_date = HisabNepaliDate::ad_to_bs($year, $month, 1);
                    $bs_month_name = HisabNepaliDate::get_bs_months($bs_date['month']);
                    echo $bs_month_name . ' ' . $bs_date['year'];
                } else {
                    echo date('F Y', mktime(0, 0, 0, $month, 1, $year));
                }
                ?>
            </h3>
            <div class="hisab-summary-cards">
                <div class="hisab-card">
                    <h4><?php _e('Income', 'hisab-financial-tracker'); ?></h4>
                    <span class="hisab-amount income"><?php echo number_format($monthly_summary['income'], 2); ?></span>
                </div>
                <div class="hisab-card">
                    <h4><?php _e('Expenses', 'hisab-financial-tracker'); ?></h4>
                    <span class="hisab-amount expense"><?php echo number_format($monthly_summary['expense'], 2); ?></span>
                </div>
                <?php if ($show_net): ?>
                <div class="hisab-card">
                    <h4><?php _e('Net', 'hisab-financial-tracker'); ?></h4>
                    <span class="hisab-amount <?php echo $monthly_summary['net'] >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo number_format($monthly_summary['net'], 2); ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function render_transaction_form($atts) {
        $show_categories = $atts['show_categories'] === 'true';
        $default_type = $atts['default_type'];
        $redirect_url = $atts['redirect_url'];
        
        $database = new HisabDatabase();
        $categories = $database->get_categories();
        $income_categories = array_filter($categories, function($cat) {
            return $cat->type === 'income';
        });
        $expense_categories = array_filter($categories, function($cat) {
            return $cat->type === 'expense';
        });
        
        ob_start();
        ?>
        <div class="hisab-transaction-form-frontend">
            <form id="hisab-frontend-transaction-form" class="hisab-form">
                <?php wp_nonce_field('hisab_transaction', 'nonce'); ?>
                
                <div class="hisab-form-row">
                    <div class="hisab-form-group">
                        <label for="frontend-transaction-type"><?php _e('Transaction Type', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                        <select id="frontend-transaction-type" name="type" required>
                            <option value=""><?php _e('Select Type', 'hisab-financial-tracker'); ?></option>
                            <option value="income" <?php selected($default_type, 'income'); ?>><?php _e('Income', 'hisab-financial-tracker'); ?></option>
                            <option value="expense" <?php selected($default_type, 'expense'); ?>><?php _e('Expense', 'hisab-financial-tracker'); ?></option>
                        </select>
                    </div>
                    
                    <div class="hisab-form-group">
                        <label for="frontend-transaction-amount"><?php _e('Amount', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                        <input type="number" id="frontend-transaction-amount" name="amount" step="0.01" min="0" required>
                    </div>
                </div>
                
                <div class="hisab-form-row">
                    <div class="hisab-form-group">
                        <label for="frontend-transaction-description"><?php _e('Description', 'hisab-financial-tracker'); ?></label>
                        <input type="text" id="frontend-transaction-description" name="description" placeholder="<?php _e('Enter transaction description', 'hisab-financial-tracker'); ?>">
                    </div>
                    
                    <?php if ($show_categories): ?>
                    <div class="hisab-form-group">
                        <label for="frontend-transaction-category"><?php _e('Category', 'hisab-financial-tracker'); ?></label>
                        <select id="frontend-transaction-category" name="category_id">
                            <option value=""><?php _e('Select Category', 'hisab-financial-tracker'); ?></option>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="hisab-form-row">
                    <div class="hisab-form-group">
                        <label for="frontend-transaction-date"><?php _e('Transaction Date', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                        <input type="date" id="frontend-transaction-date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                
                <div class="hisab-form-actions">
                    <button type="submit" class="button button-primary">
                        <?php _e('Save Transaction', 'hisab-financial-tracker'); ?>
                    </button>
                    <button type="button" class="button" onclick="document.getElementById('hisab-frontend-transaction-form').reset();">
                        <?php _e('Reset Form', 'hisab-financial-tracker'); ?>
                    </button>
                </div>
                
                <?php if ($redirect_url): ?>
                <input type="hidden" name="redirect_url" value="<?php echo esc_url($redirect_url); ?>">
                <?php endif; ?>
            </form>
            
            <div id="hisab-frontend-form-messages"></div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            const incomeCategories = <?php echo json_encode($income_categories); ?>;
            const expenseCategories = <?php echo json_encode($expense_categories); ?>;
            
            // Update categories based on transaction type
            $('#frontend-transaction-type').on('change', function() {
                const type = $(this).val();
                const categorySelect = $('#frontend-transaction-category');
                
                categorySelect.empty();
                categorySelect.append('<option value=""><?php _e('Select Category', 'hisab-financial-tracker'); ?></option>');
                
                if (type === 'income') {
                    incomeCategories.forEach(function(category) {
                        categorySelect.append(`<option value="${category.id}">${category.name}</option>`);
                    });
                } else if (type === 'expense') {
                    expenseCategories.forEach(function(category) {
                        categorySelect.append(`<option value="${category.id}">${category.name}</option>`);
                    });
                }
            });
            
            // Form submission
            $('#hisab-frontend-transaction-form').on('submit', function(e) {
                e.preventDefault();
                
                const formData = $(this).serialize();
                const messagesDiv = $('#hisab-frontend-form-messages');
                
                messagesDiv.empty();
                messagesDiv.html('<div class="notice notice-info"><p><?php _e('Saving transaction...', 'hisab-financial-tracker'); ?></p></div>');
                
                $.ajax({
                    url: hisab_ajax.ajax_url,
                    type: 'POST',
                    data: formData + '&action=hisab_save_transaction',
                    success: function(response) {
                        messagesDiv.empty();
                        
                        if (response.success) {
                            messagesDiv.html('<div class="notice notice-success"><p>' + response.message + '</p></div>');
                            $('#hisab-frontend-transaction-form')[0].reset();
                            $('#frontend-transaction-category').empty().append('<option value=""><?php _e('Select Category', 'hisab-financial-tracker'); ?></option>');
                            
                            <?php if ($redirect_url): ?>
                            setTimeout(function() {
                                window.location.href = '<?php echo esc_url($redirect_url); ?>';
                            }, 2000);
                            <?php endif; ?>
                        } else {
                            messagesDiv.html('<div class="notice notice-error"><p>' + response.message + '</p></div>');
                        }
                    },
                    error: function() {
                        messagesDiv.empty();
                        messagesDiv.html('<div class="notice notice-error"><p><?php _e('An error occurred while saving the transaction.', 'hisab-financial-tracker'); ?></p></div>');
                    }
                });
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }
}
