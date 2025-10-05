<?php
/**
 * Add/Edit Bank Account Page
 */

if (!defined('ABSPATH')) {
    exit;
}

// Initialize bank account class
$bank_account = new HisabBankAccount();

// Check if editing
$edit_account = null;
$is_edit = false;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $account_id = intval($_GET['edit']);
    $edit_account = $bank_account->get_account($account_id);
    $is_edit = $edit_account ? true : false;
}

// Handle form submission
if (isset($_POST['submit_bank_account'])) {
    $nonce = sanitize_text_field($_POST['_wpnonce']);
    if (!wp_verify_nonce($nonce, 'hisab_bank_account')) {
        $error_message = __('Security check failed. Please try again.', 'hisab-financial-tracker');
    } else {
        $data = array(
            'account_name' => sanitize_text_field($_POST['account_name']),
            'bank_name' => sanitize_text_field($_POST['bank_name']),
            'account_number' => sanitize_text_field($_POST['account_number']),
            'account_type' => sanitize_text_field($_POST['account_type']),
            'currency' => sanitize_text_field($_POST['currency']),
            'initial_balance' => floatval($_POST['initial_balance']),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        );
        
        if ($is_edit) {
            $result = $bank_account->update_account($edit_account->id, $data);
            if (is_wp_error($result)) {
                $error_message = $result->get_error_message();
            } else {
                $success_message = __('Bank account updated successfully.', 'hisab-financial-tracker');
                $edit_account = $bank_account->get_account($edit_account->id); // Refresh data
            }
        } else {
            $result = $bank_account->create_account($data);
            if (is_wp_error($result)) {
                $error_message = $result->get_error_message();
            } else {
                $success_message = __('Bank account created successfully.', 'hisab-financial-tracker');
                // Clear form data
                $edit_account = null;
                $is_edit = false;
            }
        }
    }
}

// Set default values
$defaults = array(
    'account_name' => '',
    'bank_name' => '',
    'account_number' => '',
    'account_type' => 'savings',
    'currency' => 'NPR',
    'initial_balance' => 0.00,
    'is_active' => 1
);

if ($edit_account) {
    $form_data = array(
        'account_name' => $edit_account->account_name,
        'bank_name' => $edit_account->bank_name,
        'account_number' => $edit_account->account_number,
        'account_type' => $edit_account->account_type,
        'currency' => $edit_account->currency,
        'initial_balance' => $edit_account->initial_balance,
        'is_active' => $edit_account->is_active
    );
} else {
    $form_data = $defaults;
}
?>

<div class="wrap">
    <h1><?php echo $is_edit ? __('Edit Bank Account', 'hisab-financial-tracker') : __('Add New Bank Account', 'hisab-financial-tracker'); ?></h1>
    
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
    
    <form method="post" class="hisab-form">
        <?php wp_nonce_field('hisab_bank_account', '_wpnonce'); ?>
        
        <div class="hisab-form-row">
            <div class="hisab-form-group">
                <label for="account_name"><?php _e('Account Name', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                <input type="text" id="account_name" name="account_name" value="<?php echo esc_attr($form_data['account_name']); ?>" required>
                <p class="description"><?php _e('e.g., Nepal Bank - Savings, Chase Credit Card', 'hisab-financial-tracker'); ?></p>
            </div>
        </div>
        
        <div class="hisab-form-row">
            <div class="hisab-form-group">
                <label for="bank_name"><?php _e('Bank Name', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                <input type="text" id="bank_name" name="bank_name" value="<?php echo esc_attr($form_data['bank_name']); ?>" required>
                <p class="description"><?php _e('e.g., Nepal Bank Limited, Chase Bank', 'hisab-financial-tracker'); ?></p>
            </div>
        </div>
        
        <div class="hisab-form-row">
            <div class="hisab-form-group">
                <label for="account_number"><?php _e('Account Number', 'hisab-financial-tracker'); ?></label>
                <input type="text" id="account_number" name="account_number" value="<?php echo esc_attr($form_data['account_number']); ?>">
                <p class="description"><?php _e('Optional: Bank account number or card number', 'hisab-financial-tracker'); ?></p>
            </div>
        </div>
        
        <div class="hisab-form-row">
            <div class="hisab-form-group">
                <label for="account_type"><?php _e('Account Type', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                <select id="account_type" name="account_type" required>
                    <option value="savings" <?php selected($form_data['account_type'], 'savings'); ?>><?php _e('Savings Account', 'hisab-financial-tracker'); ?></option>
                    <option value="current" <?php selected($form_data['account_type'], 'current'); ?>><?php _e('Current Account', 'hisab-financial-tracker'); ?></option>
                    <option value="credit_card" <?php selected($form_data['account_type'], 'credit_card'); ?>><?php _e('Credit Card', 'hisab-financial-tracker'); ?></option>
                    <option value="fixed_deposit" <?php selected($form_data['account_type'], 'fixed_deposit'); ?>><?php _e('Fixed Deposit', 'hisab-financial-tracker'); ?></option>
                </select>
            </div>
            <div class="hisab-form-group">
                <label for="currency"><?php _e('Currency', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                <select id="currency" name="currency" required>
                    <option value="NPR" <?php selected($form_data['currency'], 'NPR'); ?>><?php _e('NPR (Nepalese Rupee)', 'hisab-financial-tracker'); ?></option>
                    <option value="USD" <?php selected($form_data['currency'], 'USD'); ?>><?php _e('USD (US Dollar)', 'hisab-financial-tracker'); ?></option>
                </select>
            </div>
        </div>
        
        <div class="hisab-form-row">
            <div class="hisab-form-group">
                <label for="initial_balance"><?php _e('Initial Balance', 'hisab-financial-tracker'); ?></label>
                <input type="number" id="initial_balance" name="initial_balance" step="0.01" value="<?php echo esc_attr($form_data['initial_balance']); ?>">
                <p class="description"><?php _e('Starting balance for this account', 'hisab-financial-tracker'); ?></p>
            </div>
        </div>
        
        <div class="hisab-form-row">
            <div class="hisab-form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1" <?php checked($form_data['is_active'], 1); ?>>
                    <?php _e('Active Account', 'hisab-financial-tracker'); ?>
                </label>
                <p class="description"><?php _e('Uncheck to deactivate this account', 'hisab-financial-tracker'); ?></p>
            </div>
        </div>
        
        <?php if ($is_edit): ?>
            <div class="hisab-form-row">
                <div class="hisab-form-group">
                    <label><?php _e('Current Balance', 'hisab-financial-tracker'); ?></label>
                    <div style="font-size: 18px; font-weight: bold; color: <?php echo $edit_account->current_balance >= 0 ? '#00a32a' : '#d63638'; ?>;">
                        <?php echo $edit_account->currency === 'NPR' ? '₹' : '$'; ?><?php echo number_format($edit_account->current_balance, 2); ?>
                    </div>
                    <p class="description"><?php _e('Current balance is calculated from all transactions', 'hisab-financial-tracker'); ?></p>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="hisab-form-actions">
            <input type="submit" name="submit_bank_account" class="button button-primary" value="<?php echo $is_edit ? __('Update Account', 'hisab-financial-tracker') : __('Create Account', 'hisab-financial-tracker'); ?>">
            <a href="<?php echo admin_url('admin.php?page=hisab-bank-accounts'); ?>" class="button"><?php _e('Cancel', 'hisab-financial-tracker'); ?></a>
        </div>
    </form>
</div>

<style>
.hisab-form {
    max-width: 800px;
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
}

.hisab-form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.hisab-form-group {
    flex: 1;
}

.hisab-form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.hisab-form-group input[type="text"],
.hisab-form-group input[type="number"],
.hisab-form-group select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #8c8f94;
    border-radius: 3px;
    font-size: 14px;
}

.hisab-form-group input[type="checkbox"] {
    margin-right: 8px;
}

.hisab-form-group .description {
    margin-top: 5px;
    color: #646970;
    font-size: 13px;
}

.hisab-form-actions {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #ccd0d4;
}

.hisab-form-actions .button {
    margin-right: 10px;
}

.required {
    color: #d63638;
}
</style>
