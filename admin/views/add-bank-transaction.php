<?php
/**
 * Add/Edit Bank Transaction Page
 */

if (!defined('ABSPATH')) {
    exit;
}

// Initialize classes
$bank_account = new HisabBankAccount();
$bank_transaction = new HisabBankTransaction();

// Get account ID from URL parameter
$account_id = isset($_GET['account']) ? intval($_GET['account']) : 0;
$account = null;

// Check if editing
$edit_transaction = null;
$is_edit = false;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $transaction_id = intval($_GET['edit']);
    $edit_transaction = $bank_transaction->get_transaction($transaction_id);
    $is_edit = $edit_transaction ? true : false;
    if ($edit_transaction) {
        $account_id = $edit_transaction->account_id;
    }
}

if ($account_id > 0) {
    $account = $bank_account->get_account($account_id);
    if (!$account) {
        $error_message = __('Bank account not found.', 'hisab-financial-tracker');
    }
}

// Get all bank accounts for the selector
$all_accounts = $bank_account->get_all_accounts(array('is_active' => 1));

// Handle form submission
if (isset($_POST['submit_bank_transaction'])) {
    $nonce = sanitize_text_field($_POST['_wpnonce']);
    if (!wp_verify_nonce($nonce, 'hisab_bank_transaction')) {
        $error_message = __('Security check failed. Please try again.', 'hisab-financial-tracker');
    } else {
        // Get account ID from form if not already set
        $form_account_id = isset($_POST['account_id']) ? intval($_POST['account_id']) : $account_id;
        
        if ($form_account_id > 0) {
            $form_account = $bank_account->get_account($form_account_id);
            if (!$form_account) {
                $error_message = __('Selected bank account not found.', 'hisab-financial-tracker');
            } else {
                $data = array(
                    'account_id' => $form_account->id,
                    'transaction_type' => sanitize_text_field($_POST['transaction_type']),
                    'amount' => floatval($_POST['amount']),
                    'currency' => $form_account->currency, // Use account currency
                    'description' => sanitize_textarea_field($_POST['description']),
                    'reference_number' => sanitize_text_field($_POST['reference_number']),
                    'phone_pay_reference' => sanitize_text_field($_POST['phone_pay_reference']),
                    'transaction_date' => sanitize_text_field($_POST['transaction_date'])
                );
                
                // Update account reference for the rest of the processing
                $account = $form_account;
                $account_id = $form_account->id;
                
                if ($is_edit) {
                    $result = $bank_transaction->update_transaction($edit_transaction->id, $data);
                    if (is_wp_error($result)) {
                        $error_message = $result->get_error_message();
                    } else {
                        $success_message = __('Bank transaction updated successfully.', 'hisab-financial-tracker');
                        $edit_transaction = $bank_transaction->get_transaction($edit_transaction->id); // Refresh data
                    }
                } else {
                    $result = $bank_transaction->create_transaction($data);
                    if (is_wp_error($result)) {
                        $error_message = $result->get_error_message();
                    } else {
                        $success_message = __('Bank transaction created successfully.', 'hisab-financial-tracker');
                        // Clear form data
                        $edit_transaction = null;
                        $is_edit = false;
                    }
                }
            }
        } else {
            $error_message = __('Please select a bank account.', 'hisab-financial-tracker');
        }
    }
}

// Set default values
$defaults = array(
    'transaction_type' => 'deposit',
    'amount' => 0.00,
    'description' => '',
    'reference_number' => '',
    'phone_pay_reference' => '',
    'transaction_date' => current_time('Y-m-d')
);

if ($edit_transaction) {
    $form_data = array(
        'transaction_type' => $edit_transaction->transaction_type,
        'amount' => $edit_transaction->amount,
        'description' => $edit_transaction->description,
        'reference_number' => $edit_transaction->reference_number,
        'phone_pay_reference' => $edit_transaction->phone_pay_reference,
        'transaction_date' => $edit_transaction->transaction_date
    );
} else {
    $form_data = $defaults;
}
?>

<div class="wrap">
    <h1><?php echo $is_edit ? __('Edit Bank Transaction', 'hisab-financial-tracker') : __('Add Bank Transaction', 'hisab-financial-tracker'); ?></h1>
    
    <?php if ($account): ?>
        <p class="hisab-account-info" style="background: #f0f6fc; border: 1px solid #c3c4c7; border-radius: 4px; padding: 12px; margin: 20px 0;">
            <strong><?php _e('Account:', 'hisab-financial-tracker'); ?></strong> <?php echo esc_html($account->account_name); ?> 
            (<?php echo esc_html($account->bank_name); ?>) - 
            <strong><?php _e('Balance:', 'hisab-financial-tracker'); ?></strong> 
            <?php echo $account->currency === 'NPR' ? '₹' : '$'; ?><?php echo number_format($account->current_balance, 2); ?>
        </p>
        
        <?php if (!empty($all_accounts) && count($all_accounts) > 1): ?>
            <!-- Quick Account Switcher -->
            <div class="hisab-quick-switcher" style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 15px; margin: 20px 0;">
                <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                    <label for="account-switcher" style="font-weight: 600; margin: 0;">
                        <?php _e('Switch Account:', 'hisab-financial-tracker'); ?>
                    </label>
                    <select id="account-switcher" style="min-width: 250px; padding: 5px 10px; border: 1px solid #8c8f94; border-radius: 3px;">
                        <option value=""><?php _e('Select Account', 'hisab-financial-tracker'); ?></option>
                        <?php foreach ($all_accounts as $acc): ?>
                            <option value="<?php echo $acc->id; ?>" <?php selected($account->id, $acc->id); ?>>
                                <?php echo esc_html($acc->account_name . ' (' . $acc->bank_name . ') - ' . $acc->currency . ' ' . number_format($acc->current_balance, 2)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" id="switch-account-btn" class="button button-secondary" style="margin: 0;">
                        <?php _e('Switch', 'hisab-financial-tracker'); ?>
                    </button>
                </div>
            </div>
        <?php endif; ?>
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
    
    <?php if (!$account && !$is_edit): ?>
        <!-- Bank Account Selector -->
        <div class="hisab-account-selector" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 30px; margin: 20px 0; text-align: center;">
            <h3 style="margin: 0 0 20px 0;"><?php _e('Select a Bank Account', 'hisab-financial-tracker'); ?></h3>
            <p style="margin: 0 0 20px 0; color: #666;"><?php _e('Choose a bank account to add a transaction to.', 'hisab-financial-tracker'); ?></p>
            
            <?php if (empty($all_accounts)): ?>
                <div class="hisab-no-accounts" style="color: #d63638; margin: 20px 0;">
                    <p><?php _e('No bank accounts found. Please create a bank account first.', 'hisab-financial-tracker'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=hisab-add-bank-account'); ?>" class="button button-primary">
                        <?php _e('Create Bank Account', 'hisab-financial-tracker'); ?>
                    </a>
                </div>
            <?php else: ?>
                <form method="get" style="display: inline-block;">
                    <input type="hidden" name="page" value="hisab-add-bank-transaction">
                    
                    <div style="display: flex; gap: 15px; align-items: center; justify-content: center; flex-wrap: wrap;">
                        <div>
                            <label for="account_select" style="display: block; margin-bottom: 5px; font-weight: 600;">
                                <?php _e('Bank Account:', 'hisab-financial-tracker'); ?>
                            </label>
                            <select name="account" id="account_select" style="min-width: 300px; padding: 8px 12px; border: 1px solid #8c8f94; border-radius: 3px; font-size: 14px;">
                                <option value=""><?php _e('Select an account...', 'hisab-financial-tracker'); ?></option>
                                <?php foreach ($all_accounts as $acc): ?>
                                    <option value="<?php echo $acc->id; ?>" <?php selected($account_id, $acc->id); ?>>
                                        <?php echo esc_html($acc->account_name . ' (' . $acc->bank_name . ') - ' . $acc->currency . ' ' . number_format($acc->current_balance, 2)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div style="margin-top: 20px;">
                            <input type="submit" class="button button-primary" value="<?php _e('Select Account', 'hisab-financial-tracker'); ?>">
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
        <form method="post" class="hisab-form">
            <?php wp_nonce_field('hisab_bank_transaction', '_wpnonce'); ?>
            <input type="hidden" name="account_id" value="<?php echo $account->id; ?>">
            
            <div class="hisab-form-row">
                <div class="hisab-form-group">
                    <label for="transaction_type"><?php _e('Transaction Type', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                    <select id="transaction_type" name="transaction_type" required>
                        <option value="deposit" <?php selected($form_data['transaction_type'], 'deposit'); ?>><?php _e('Deposit', 'hisab-financial-tracker'); ?></option>
                        <option value="withdrawal" <?php selected($form_data['transaction_type'], 'withdrawal'); ?>><?php _e('Withdrawal', 'hisab-financial-tracker'); ?></option>
                        <option value="phone_pay" <?php selected($form_data['transaction_type'], 'phone_pay'); ?>><?php _e('Phone Pay', 'hisab-financial-tracker'); ?></option>
                        <option value="transfer_in" <?php selected($form_data['transaction_type'], 'transfer_in'); ?>><?php _e('Transfer In', 'hisab-financial-tracker'); ?></option>
                        <option value="transfer_out" <?php selected($form_data['transaction_type'], 'transfer_out'); ?>><?php _e('Transfer Out', 'hisab-financial-tracker'); ?></option>
                    </select>
                </div>
                <div class="hisab-form-group">
                    <label for="amount"><?php _e('Amount', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                    <div style="display: flex; align-items: center;">
                        <span style="margin-right: 5px; font-weight: bold;"><?php echo $account->currency === 'NPR' ? '₹' : '$'; ?></span>
                        <input type="number" id="amount" name="amount" step="0.01" min="0" value="<?php echo esc_attr($form_data['amount']); ?>" required style="flex: 1;">
                    </div>
                </div>
            </div>
            
            <div class="hisab-form-row">
                <div class="hisab-form-group">
                    <label for="transaction_date"><?php _e('Transaction Date', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                    <input type="date" id="transaction_date" name="transaction_date" value="<?php echo esc_attr($form_data['transaction_date']); ?>" required>
                </div>
                <div class="hisab-form-group">
                    <label for="reference_number"><?php _e('Reference Number', 'hisab-financial-tracker'); ?></label>
                    <input type="text" id="reference_number" name="reference_number" value="<?php echo esc_attr($form_data['reference_number']); ?>" placeholder="<?php _e('Optional reference number', 'hisab-financial-tracker'); ?>">
                </div>
            </div>
            
            <div class="hisab-form-row" id="phone_pay_row" style="display: none;">
                <div class="hisab-form-group">
                    <label for="phone_pay_reference"><?php _e('Phone Pay Reference', 'hisab-financial-tracker'); ?></label>
                    <input type="text" id="phone_pay_reference" name="phone_pay_reference" value="<?php echo esc_attr($form_data['phone_pay_reference']); ?>" placeholder="<?php _e('Phone pay transaction ID', 'hisab-financial-tracker'); ?>">
                    <p class="description"><?php _e('Enter the phone pay transaction reference number', 'hisab-financial-tracker'); ?></p>
                </div>
            </div>
            
            <div class="hisab-form-row">
                <div class="hisab-form-group">
                    <label for="description"><?php _e('Description', 'hisab-financial-tracker'); ?></label>
                    <textarea id="description" name="description" rows="3" placeholder="<?php _e('Enter transaction description', 'hisab-financial-tracker'); ?>"><?php echo esc_textarea($form_data['description']); ?></textarea>
                </div>
            </div>
            
            <div class="hisab-form-actions">
                <input type="submit" name="submit_bank_transaction" class="button button-primary" value="<?php echo $is_edit ? __('Update Transaction', 'hisab-financial-tracker') : __('Add Transaction', 'hisab-financial-tracker'); ?>">
                <a href="<?php echo admin_url('admin.php?page=hisab-bank-transactions&account=' . $account->id); ?>" class="button"><?php _e('Cancel', 'hisab-financial-tracker'); ?></a>
            </div>
        </form>
    <?php endif; ?>
</div>


<script>
jQuery(document).ready(function($) {
    // Show/hide phone pay reference field based on transaction type
    function togglePhonePayField() {
        var transactionType = $('#transaction_type').val();
        if (transactionType === 'phone_pay') {
            $('#phone_pay_row').show();
            $('#phone_pay_reference').prop('required', true);
        } else {
            $('#phone_pay_row').hide();
            $('#phone_pay_reference').prop('required', false);
        }
    }
    
    // Initial call
    togglePhonePayField();
    
    // Bind to change event
    $('#transaction_type').on('change', togglePhonePayField);
    
    // Form validation
    $('form').on('submit', function(e) {
        var transactionType = $('#transaction_type').val();
        var amount = parseFloat($('#amount').val());
        var accountBalance = <?php echo $account ? $account->current_balance : 0; ?>;
        
        // Check for withdrawal/phone pay/transfer out with insufficient balance
        if (['withdrawal', 'phone_pay', 'transfer_out'].includes(transactionType) && amount > accountBalance) {
            e.preventDefault();
            alert('<?php _e('Insufficient balance for this transaction.', 'hisab-financial-tracker'); ?>');
            return false;
        }
        
        // Check for zero or negative amount
        if (amount <= 0) {
            e.preventDefault();
            alert('<?php _e('Amount must be greater than zero.', 'hisab-financial-tracker'); ?>');
            return false;
        }
    });
    
    // Account switcher functionality
    $('#switch-account-btn').on('click', function() {
        var selectedAccountId = $('#account-switcher').val();
        if (selectedAccountId) {
            // Redirect to the same page with the selected account
            var currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('account', selectedAccountId);
            window.location.href = currentUrl.toString();
        }
    });
});
</script>
