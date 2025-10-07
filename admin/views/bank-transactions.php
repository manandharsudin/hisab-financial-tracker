<?php
/**
 * Bank Transactions Listing Page
 */

if (!defined('ABSPATH')) {
    exit;
}

// Initialize classes
$bank_account = new HisabBankAccount();
$bank_transaction = new HisabBankTransaction();

// Check for success messages from redirects
if (isset($_GET['created']) && $_GET['created'] == '1') {
    $success_message = __('Bank transaction created successfully.', 'hisab-financial-tracker');
}

if (isset($_GET['updated']) && $_GET['updated'] == '1') {
    $success_message = __('Bank transaction updated successfully.', 'hisab-financial-tracker');
}

// Get account ID from URL parameter
$account_id = isset($_GET['account']) ? intval($_GET['account']) : 0;
$account = null;

if ($account_id > 0) {
    $account = $bank_account->get_account($account_id);
    if (!$account) {
        $error_message = __('Bank account not found.', 'hisab-financial-tracker');
    }
}

// Get all bank accounts for the selector
$all_accounts = $bank_account->get_all_accounts(array('is_active' => 1));

// Handle actions
if (isset($_POST['action']) && $account) {
    $action = sanitize_text_field($_POST['action']);
    $transaction_id = isset($_POST['transaction_id']) ? intval($_POST['transaction_id']) : 0;
    
    switch ($action) {
        case 'delete_transaction':
            if ($transaction_id > 0) {
                $result = $bank_transaction->delete_transaction($transaction_id);
                if (is_wp_error($result)) {
                    $error_message = $result->get_error_message();
                } else {
                    $success_message = __('Transaction deleted successfully.', 'hisab-financial-tracker');
                }
            }
            break;
    }
}

// Get filter parameters
$transaction_type_filter = isset($_GET['transaction_type']) ? sanitize_text_field($_GET['transaction_type']) : '';
$start_date_filter = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
$end_date_filter = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';
$page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
$per_page = 20;

// Build filters
$filters = array();
if ($account_id > 0) {
    $filters['account_id'] = $account_id; // Filter by specific account
}
if ($transaction_type_filter) {
    $filters['transaction_type'] = $transaction_type_filter;
}
if ($start_date_filter) {
    $filters['start_date'] = $start_date_filter;
}
if ($end_date_filter) {
    $filters['end_date'] = $end_date_filter;
}

// Get transactions
$transactions_data = array();
if ($account) {
    $transactions_data = $bank_transaction->get_all_transactions($filters, $page, $per_page);
    $transactions = $transactions_data['transactions'];
    $total = $transactions_data['total'];
    $total_pages = $transactions_data['total_pages'];
} else {
    $transactions = array();
    $total = 0;
    $total_pages = 0;
}

// Get account summary
$account_summary = null;
if ($account) {
    $account_summary = $bank_transaction->get_account_summary($account->id, $start_date_filter, $end_date_filter);
}
?>

<div class="wrap">
    <?php if ($account): ?>
        <h1 class="wp-heading-inline">
            <?php printf(__('Transactions - %s', 'hisab-financial-tracker'), esc_html($account->account_name)); ?>
        </h1>
        <a href="<?php echo admin_url('admin.php?page=hisab-add-bank-transaction&account=' . $account->id); ?>" class="page-title-action">
            <?php _e('Add Transaction', 'hisab-financial-tracker'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=hisab-bank-accounts'); ?>" class="page-title-action">
            <?php _e('Back to Accounts', 'hisab-financial-tracker'); ?>
        </a>
    <?php else: ?>
        <h1><?php _e('Bank Transactions', 'hisab-financial-tracker'); ?></h1>
    <?php endif; ?>
    <hr class="wp-header-end">
    
    <?php if ($account && !empty($all_accounts)): ?>
        <!-- Quick Account Switcher -->
        <div class="hisab-quick-switcher" style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 15px; margin: 20px 0;">
            <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <label for="account-switcher" style="font-weight: 600; margin: 0;">
                    <?php _e('Switch Account:', 'hisab-financial-tracker'); ?>
                </label>
                <select id="account-switcher" style="min-width: 300px; max-width: 600px; width: auto; padding: 5px 10px; border: 1px solid #8c8f94; border-radius: 3px;">
                    <option value=""><?php _e('Select Account', 'hisab-financial-tracker'); ?></option>
                    <?php foreach ($all_accounts as $acc): ?>
                        <option value="<?php echo $acc->id; ?>" <?php selected($account->id, $acc->id); ?>>
                            <?php echo esc_html($acc->account_name . ' (' . $acc->bank_name . ') - ' . $acc->currency . ' ' . number_format($acc->current_balance, 2)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="button" id="switch-account-btn" class="button button-secondary" style="margin: 0;">
                    <?php _e('Go', 'hisab-financial-tracker'); ?>
                </button>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (isset($success_message)): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html($success_message); ?></p>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html($error_message); ?></p>
        </div>
    <?php endif; ?>
    
    <?php if (!$account): ?>
        <!-- Bank Account Selector -->
        <div class="hisab-account-selector" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 30px; margin: 20px 0; text-align: center;">
            <h3 style="margin: 0 0 20px 0;"><?php _e('Select a Bank Account', 'hisab-financial-tracker'); ?></h3>
            <p style="margin: 0 0 20px 0; color: #666;"><?php _e('Choose a bank account to view its transactions.', 'hisab-financial-tracker'); ?></p>
            
            <?php if (empty($all_accounts)): ?>
                <div class="hisab-no-accounts" style="color: #d63638; margin: 20px 0;">
                    <p><?php _e('No bank accounts found. Please create a bank account first.', 'hisab-financial-tracker'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=hisab-add-bank-account'); ?>" class="button button-primary">
                        <?php _e('Create Bank Account', 'hisab-financial-tracker'); ?>
                    </a>
                </div>
            <?php else: ?>
                <form method="get" style="display: inline-block;">
                    <input type="hidden" name="page" value="hisab-bank-transactions">
                    
                    <div style="display: flex; gap: 15px; align-items: center; justify-content: center; flex-wrap: wrap;">
                        <div>
                            <label for="account_select" style="display: block; margin-bottom: 5px; font-weight: 600;">
                                <?php _e('Bank Account:', 'hisab-financial-tracker'); ?>
                            </label>
                            <select name="account" id="account_select" style="min-width: 350px; max-width: 700px; width: auto; padding: 8px 12px; border: 1px solid #8c8f94; border-radius: 3px; font-size: 14px;">
                                <option value=""><?php _e('Select an account...', 'hisab-financial-tracker'); ?></option>
                                <?php foreach ($all_accounts as $acc): ?>
                                    <option value="<?php echo $acc->id; ?>" <?php selected($account_id, $acc->id); ?>>
                                        <?php echo esc_html($acc->account_name . ' (' . $acc->bank_name . ') - ' . $acc->currency . ' ' . number_format($acc->current_balance, 2)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div style="margin-top: 20px;">
                            <input type="submit" class="button button-primary" value="<?php _e('View Transactions', 'hisab-financial-tracker'); ?>">
                        </div>
                    </div>
                </form>
                
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
                    <p style="margin: 0 0 10px 0; color: #666; font-size: 14px;">
                        <?php _e('Quick Actions:', 'hisab-financial-tracker'); ?>
                    </p>
                    <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                        <a href="<?php echo admin_url('admin.php?page=hisab-bank-accounts'); ?>" class="button">
                            <?php _e('Manage Accounts', 'hisab-financial-tracker'); ?>
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=hisab-add-bank-account'); ?>" class="button">
                            <?php _e('Add New Account', 'hisab-financial-tracker'); ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($account): ?>
        <!-- Account Summary -->
        <div class="hisab-account-summary" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin: 20px 0;">
            <div style="display: flex; gap: 30px; align-items: center;">
                <div>
                    <h3 style="margin: 0 0 5px 0;"><?php echo esc_html($account->account_name); ?></h3>
                    <p style="margin: 0; color: #666;"><?php echo esc_html($account->bank_name); ?></p>
                    <p style="margin: 0; color: #666;"><?php echo esc_html($account->account_number ?: 'No account number'); ?></p>
                </div>
                <div>
                    <div style="font-size: 24px; font-weight: bold; margin-bottom: 10px; color: <?php echo $account->current_balance >= 0 ? '#00a32a' : '#d63638'; ?>;">
                        <?php echo $account->currency === 'NPR' ? '₹' : '$'; ?><?php echo number_format($account->current_balance, 2); ?>
                    </div>
                    <div style="color: #666; font-size: 14px;"><?php _e('Current Balance', 'hisab-financial-tracker'); ?></div>
                </div>
                <div>
                    <div style="font-size: 18px; font-weight: bold; color: #1d2327;">
                        <?php echo $account->currency; ?>
                    </div>
                    <div style="color: #666; font-size: 14px;"><?php _e('Currency', 'hisab-financial-tracker'); ?></div>
                </div>
                <div>
                    <span class="hisab-status-badge hisab-status-<?php echo $account->is_active ? 'active' : 'inactive'; ?>">
                        <?php echo $account->is_active ? __('Active', 'hisab-financial-tracker') : __('Inactive', 'hisab-financial-tracker'); ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="hisab-filters" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 15px; margin: 20px 0;">
            <form method="get" style="display: flex; gap: 15px; align-items: center;">
                <input type="hidden" name="page" value="hisab-bank-transactions">
                <input type="hidden" name="account" value="<?php echo $account->id; ?>">
                
                <div>
                    <label for="transaction_type"><?php _e('Transaction Type:', 'hisab-financial-tracker'); ?></label>
                    <select name="transaction_type" id="transaction_type">
                        <option value=""><?php _e('All Types', 'hisab-financial-tracker'); ?></option>
                        <option value="deposit" <?php selected($transaction_type_filter, 'deposit'); ?>><?php _e('Deposit', 'hisab-financial-tracker'); ?></option>
                        <option value="withdrawal" <?php selected($transaction_type_filter, 'withdrawal'); ?>><?php _e('Withdrawal', 'hisab-financial-tracker'); ?></option>
                        <option value="phone_pay" <?php selected($transaction_type_filter, 'phone_pay'); ?>><?php _e('Phone Pay', 'hisab-financial-tracker'); ?></option>
                        <option value="transfer_in" <?php selected($transaction_type_filter, 'transfer_in'); ?>><?php _e('Transfer In', 'hisab-financial-tracker'); ?></option>
                        <option value="transfer_out" <?php selected($transaction_type_filter, 'transfer_out'); ?>><?php _e('Transfer Out', 'hisab-financial-tracker'); ?></option>
                    </select>
                </div>
                
                <div>
                    <label for="start_date"><?php _e('Start Date:', 'hisab-financial-tracker'); ?></label>
                    <input type="date" name="start_date" id="start_date" value="<?php echo esc_attr($start_date_filter); ?>">
                </div>
                
                <div>
                    <label for="end_date"><?php _e('End Date:', 'hisab-financial-tracker'); ?></label>
                    <input type="date" name="end_date" id="end_date" value="<?php echo esc_attr($end_date_filter); ?>">
                </div>
                
                <div>
                    <input type="submit" class="button button-primary" value="<?php _e('Filter', 'hisab-financial-tracker'); ?>">
                    <a href="<?php echo admin_url('admin.php?page=hisab-bank-transactions&account=' . $account->id); ?>" class="button" style="margin-left: 10px;"><?php _e('Clear', 'hisab-financial-tracker'); ?></a>
                </div>
            </form>
        </div>
        
        <!-- Transactions Table -->
        <?php if (empty($transactions)): ?>
            <div class="hisab-no-transactions" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 40px; text-align: center; color: #666;">
                <h3><?php _e('No transactions found', 'hisab-financial-tracker'); ?></h3>
                <p><?php _e('Try adjusting your filters or add a new transaction.', 'hisab-financial-tracker'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=hisab-add-bank-transaction&account=' . $account->id); ?>" class="button button-primary"><?php _e('Add Transaction', 'hisab-financial-tracker'); ?></a>
            </div>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 50px;"><?php _e('#', 'hisab-financial-tracker'); ?></th>
                        <th style="width: 100px;"><?php _e('Date', 'hisab-financial-tracker'); ?></th>
                        <th style="width: 120px;"><?php _e('Type', 'hisab-financial-tracker'); ?></th>
                        <th><?php _e('Description', 'hisab-financial-tracker'); ?></th>
                        <th style="width: 100px;"><?php _e('Reference', 'hisab-financial-tracker'); ?></th>
                        <th style="width: 120px;"><?php _e('Amount', 'hisab-financial-tracker'); ?></th>
                        <th style="width: 120px;"><?php _e('Actions', 'hisab-financial-tracker'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $serial_number = ($page - 1) * $per_page + 1; foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?php echo $serial_number++; ?></td>
                            <td>
                                <div class="hisab-date-display">
                                    <div class="ad-date"><?php echo date('M j, Y', strtotime($transaction->transaction_date)); ?></div>
                                </div>
                            </td>
                            <td>
                                <span class="hisab-transaction-type-badge hisab-type-<?php echo esc_attr($transaction->transaction_type); ?>">
                                    <?php echo esc_html(ucfirst(str_replace('_', ' ', $transaction->transaction_type))); ?>
                                </span>
                            </td>
                            <td>
                                <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo esc_attr($transaction->description); ?>">
                                    <?php echo esc_html($transaction->description); ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($transaction->reference_number): ?>
                                    <span class="hisab-reference-badge"><?php echo esc_html($transaction->reference_number); ?></span>
                                <?php elseif ($transaction->phone_pay_reference): ?>
                                    <span class="hisab-phone-pay-badge"><?php echo esc_html($transaction->phone_pay_reference); ?></span>
                                <?php else: ?>
                                    <span style="color: #999;">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="color: <?php echo in_array($transaction->transaction_type, ['deposit', 'transfer_in']) ? '#00a32a' : '#d63638'; ?>;">
                                    <?php echo in_array($transaction->transaction_type, ['deposit', 'transfer_in']) ? '+' : '-'; ?><?php echo $transaction->currency === 'NPR' ? '₹' : '$'; ?><?php echo number_format($transaction->amount, 2); ?>
                                </strong>
                            </td>
                            <td>
                                <div class="hisab-transaction-actions">
                                    <a href="<?php echo admin_url('admin.php?page=hisab-add-bank-transaction&edit=' . $transaction->id); ?>" class="button button-small">
                                        <?php _e('Edit', 'hisab-financial-tracker'); ?>
                                    </a>
                                    <form method="post" style="display: inline-block;" onsubmit="return confirm('<?php _e('Are you sure you want to delete this transaction?', 'hisab-financial-tracker'); ?>');">
                                        <input type="hidden" name="action" value="delete_transaction">
                                        <input type="hidden" name="transaction_id" value="<?php echo $transaction->id; ?>">
                                        <button type="submit" class="button button-small button-link-delete">
                                            <?php _e('Delete', 'hisab-financial-tracker'); ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="hisab-pagination" style="margin: 20px 0; text-align: center;">
                    <?php
                    $pagination_args = array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'current' => $page,
                        'total' => $total_pages,
                        'prev_text' => __('&laquo; Previous'),
                        'next_text' => __('Next &raquo;')
                    );
                    echo paginate_links($pagination_args);
                    ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>