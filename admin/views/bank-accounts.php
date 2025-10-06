<?php
/**
 * Bank Accounts Listing Page
 */

if (!defined('ABSPATH')) {
    exit;
}

// Initialize bank account class
$bank_account = new HisabBankAccount();

// Handle actions
if (isset($_POST['action'])) {
    $action = sanitize_text_field($_POST['action']);
    $account_id = isset($_POST['account_id']) ? intval($_POST['account_id']) : 0;
    
    switch ($action) {
        case 'delete_account':
            if ($account_id > 0) {
                $result = $bank_account->delete_account($account_id);
                if (is_wp_error($result)) {
                    $error_message = $result->get_error_message();
                } else {
                    $success_message = __('Bank account deleted successfully.', 'hisab-financial-tracker');
                }
            }
            break;
            
        case 'toggle_status':
            if ($account_id > 0) {
                $account = $bank_account->get_account($account_id);
                if ($account) {
                    $new_status = $account->is_active ? 0 : 1;
                    $result = $bank_account->update_account($account_id, array('is_active' => $new_status));
                    if (is_wp_error($result)) {
                        $error_message = $result->get_error_message();
                    } else {
                        $success_message = $new_status ? __('Account activated successfully.', 'hisab-financial-tracker') : __('Account deactivated successfully.', 'hisab-financial-tracker');
                    }
                }
            }
            break;
    }
}

// Get filter parameters
$currency_filter = isset($_GET['currency']) ? sanitize_text_field($_GET['currency']) : '';
$type_filter = isset($_GET['account_type']) ? sanitize_text_field($_GET['account_type']) : '';
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';

// Build filters
$filters = array();
if ($currency_filter) {
    $filters['currency'] = $currency_filter;
}
if ($type_filter) {
    $filters['account_type'] = $type_filter;
}
if ($status_filter !== '') {
    $filters['is_active'] = $status_filter;
}

// Get bank accounts
$accounts = $bank_account->get_all_accounts($filters);

// Calculate totals
$total_balance_npr = 0;
$total_balance_usd = 0;
$active_accounts = 0;
$inactive_accounts = 0;

foreach ($accounts as $account) {
    if ($account->currency === 'NPR') {
        $total_balance_npr += $account->current_balance;
    } else {
        $total_balance_usd += $account->current_balance;
    }
    
    if ($account->is_active) {
        $active_accounts++;
    } else {
        $inactive_accounts++;
    }
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Bank Accounts', 'hisab-financial-tracker'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=hisab-add-bank-account'); ?>" class="page-title-action"><?php _e('Add New Account', 'hisab-financial-tracker'); ?></a>
    <hr class="wp-header-end">
    
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
    
    <!-- Summary Cards -->
    <div class="hisab-summary-cards" style="display: flex; gap: 20px; margin: 20px 0;">
        <div class="hisab-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; flex: 1;">
            <h3 style="margin: 0 0 10px 0; color: #1d2327;"><?php _e('Total Balance (NPR)', 'hisab-financial-tracker'); ?></h3>
            <div style="font-size: 24px; font-weight: bold; color: #00a32a;">₹<?php echo number_format($total_balance_npr, 2); ?></div>
        </div>
        <div class="hisab-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; flex: 1;">
            <h3 style="margin: 0 0 10px 0; color: #1d2327;"><?php _e('Total Balance (USD)', 'hisab-financial-tracker'); ?></h3>
            <div style="font-size: 24px; font-weight: bold; color: #00a32a;">$<?php echo number_format($total_balance_usd, 2); ?></div>
        </div>
        <div class="hisab-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; flex: 1;">
            <h3 style="margin: 0 0 10px 0; color: #1d2327;"><?php _e('Active Accounts', 'hisab-financial-tracker'); ?></h3>
            <div style="font-size: 24px; font-weight: bold; color: #00a32a;"><?php echo $active_accounts; ?></div>
        </div>
        <div class="hisab-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; flex: 1;">
            <h3 style="margin: 0 0 10px 0; color: #1d2327;"><?php _e('Total Accounts', 'hisab-financial-tracker'); ?></h3>
            <div style="font-size: 24px; font-weight: bold; color: #1d2327;"><?php echo count($accounts); ?></div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="hisab-filters" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 15px; margin: 20px 0;">
        <form method="get" style="display: flex; gap: 15px; align-items: center;">
            <input type="hidden" name="page" value="hisab-bank-accounts">
            
            <div>
                <label for="currency"><?php _e('Currency:', 'hisab-financial-tracker'); ?></label>
                <select name="currency" id="currency">
                    <option value=""><?php _e('All Currencies', 'hisab-financial-tracker'); ?></option>
                    <option value="NPR" <?php selected($currency_filter, 'NPR'); ?>><?php _e('NPR', 'hisab-financial-tracker'); ?></option>
                    <option value="USD" <?php selected($currency_filter, 'USD'); ?>><?php _e('USD', 'hisab-financial-tracker'); ?></option>
                </select>
            </div>
            
            <div>
                <label for="account_type"><?php _e('Account Type:', 'hisab-financial-tracker'); ?></label>
                <select name="account_type" id="account_type">
                    <option value=""><?php _e('All Types', 'hisab-financial-tracker'); ?></option>
                    <option value="savings" <?php selected($type_filter, 'savings'); ?>><?php _e('Savings', 'hisab-financial-tracker'); ?></option>
                    <option value="current" <?php selected($type_filter, 'current'); ?>><?php _e('Current', 'hisab-financial-tracker'); ?></option>
                    <option value="credit_card" <?php selected($type_filter, 'credit_card'); ?>><?php _e('Credit Card', 'hisab-financial-tracker'); ?></option>
                    <option value="fixed_deposit" <?php selected($type_filter, 'fixed_deposit'); ?>><?php _e('Fixed Deposit', 'hisab-financial-tracker'); ?></option>
                </select>
            </div>
            
            <div>
                <label for="status"><?php _e('Status:', 'hisab-financial-tracker'); ?></label>
                <select name="status" id="status">
                    <option value=""><?php _e('All Status', 'hisab-financial-tracker'); ?></option>
                    <option value="1" <?php selected($status_filter, '1'); ?>><?php _e('Active', 'hisab-financial-tracker'); ?></option>
                    <option value="0" <?php selected($status_filter, '0'); ?>><?php _e('Inactive', 'hisab-financial-tracker'); ?></option>
                </select>
            </div>
            
            <div>
                <input type="submit" class="button" value="<?php _e('Filter', 'hisab-financial-tracker'); ?>">
                <a href="<?php echo admin_url('admin.php?page=hisab-bank-accounts'); ?>" class="button"><?php _e('Clear', 'hisab-financial-tracker'); ?></a>
            </div>
        </form>
    </div>
    
    <!-- Accounts Table -->
    <?php if (empty($accounts)): ?>
        <div class="hisab-no-accounts" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 40px; text-align: center; color: #666;">
            <h3><?php _e('No bank accounts found', 'hisab-financial-tracker'); ?></h3>
            <p><?php _e('Try adjusting your filters or add a new bank account.', 'hisab-financial-tracker'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=hisab-add-bank-account'); ?>" class="button button-primary"><?php _e('Add Bank Account', 'hisab-financial-tracker'); ?></a>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 50px;"><?php _e('#', 'hisab-financial-tracker'); ?></th>
                    <th><?php _e('Account Name', 'hisab-financial-tracker'); ?></th>
                    <th><?php _e('Bank Name', 'hisab-financial-tracker'); ?></th>
                    <th><?php _e('Account Number', 'hisab-financial-tracker'); ?></th>
                    <th style="width: 120px;"><?php _e('Type', 'hisab-financial-tracker'); ?></th>
                    <th style="width: 80px;"><?php _e('Currency', 'hisab-financial-tracker'); ?></th>
                    <th style="width: 120px;"><?php _e('Current Balance', 'hisab-financial-tracker'); ?></th>
                    <th style="width: 80px;"><?php _e('Status', 'hisab-financial-tracker'); ?></th>
                    <th style="width: 150px;"><?php _e('Actions', 'hisab-financial-tracker'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $serial_number = 1; foreach ($accounts as $account): ?>
                    <tr>
                        <td><?php echo $serial_number++; ?></td>
                        <td>
                            <strong><?php echo esc_html($account->account_name); ?></strong>
                        </td>
                        <td><?php echo esc_html($account->bank_name); ?></td>
                        <td><?php echo esc_html($account->account_number ?: '—'); ?></td>
                        <td>
                            <span class="hisab-account-type-badge hisab-type-<?php echo esc_attr($account->account_type); ?>">
                                <?php echo esc_html(ucfirst(str_replace('_', ' ', $account->account_type))); ?>
                            </span>
                        </td>
                        <td>
                            <span class="hisab-currency-badge hisab-currency-<?php echo esc_attr($account->currency); ?>">
                                <?php echo esc_html($account->currency); ?>
                            </span>
                        </td>
                        <td>
                            <strong style="color: <?php echo $account->current_balance >= 0 ? '#00a32a' : '#d63638'; ?>;">
                                <?php echo $account->currency === 'NPR' ? '₹' : '$'; ?><?php echo number_format($account->current_balance, 2); ?>
                            </strong>
                        </td>
                        <td>
                            <span class="hisab-status-badge hisab-status-<?php echo $account->is_active ? 'active' : 'inactive'; ?>">
                                <?php echo $account->is_active ? __('Active', 'hisab-financial-tracker') : __('Inactive', 'hisab-financial-tracker'); ?>
                            </span>
                        </td>
                        <td>
                            <div class="hisab-account-actions">
                                <a href="<?php echo admin_url('admin.php?page=hisab-add-bank-account&edit=' . $account->id); ?>" class="button button-small">
                                    <?php _e('Edit', 'hisab-financial-tracker'); ?>
                                </a>
                                <a href="<?php echo admin_url('admin.php?page=hisab-bank-transactions&account=' . $account->id); ?>" class="button button-small">
                                    <?php _e('Transactions', 'hisab-financial-tracker'); ?>
                                </a>
                                <form method="post" style="display: inline-block;" onsubmit="return confirm('<?php _e('Are you sure you want to change the status of this account?', 'hisab-financial-tracker'); ?>');">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="account_id" value="<?php echo $account->id; ?>">
                                    <button type="submit" class="button button-small">
                                        <?php echo $account->is_active ? __('Deactivate', 'hisab-financial-tracker') : __('Activate', 'hisab-financial-tracker'); ?>
                                    </button>
                                </form>
                                <form method="post" style="display: inline-block;" onsubmit="return confirm('<?php _e('Are you sure you want to delete this account? This action cannot be undone.', 'hisab-financial-tracker'); ?>');">
                                    <input type="hidden" name="action" value="delete_account">
                                    <input type="hidden" name="account_id" value="<?php echo $account->id; ?>">
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
    <?php endif; ?>
</div>