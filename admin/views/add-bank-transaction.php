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
} else {
    $error_message = __('No account specified.', 'hisab-financial-tracker');
}

// Handle form submission
if (isset($_POST['submit_bank_transaction']) && $account) {
    $nonce = sanitize_text_field($_POST['_wpnonce']);
    if (!wp_verify_nonce($nonce, 'hisab_bank_transaction')) {
        $error_message = __('Security check failed. Please try again.', 'hisab-financial-tracker');
    } else {
        $data = array(
            'account_id' => $account->id,
            'transaction_type' => sanitize_text_field($_POST['transaction_type']),
            'amount' => floatval($_POST['amount']),
            'currency' => $account->currency, // Use account currency
            'description' => sanitize_textarea_field($_POST['description']),
            'reference_number' => sanitize_text_field($_POST['reference_number']),
            'phone_pay_reference' => sanitize_text_field($_POST['phone_pay_reference']),
            'transaction_date' => sanitize_text_field($_POST['transaction_date'])
        );
        
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
    <?php if ($account): ?>
        <h1><?php echo $is_edit ? __('Edit Bank Transaction', 'hisab-financial-tracker') : __('Add Bank Transaction', 'hisab-financial-tracker'); ?></h1>
        <p class="hisab-account-info" style="background: #f0f6fc; border: 1px solid #c3c4c7; border-radius: 4px; padding: 12px; margin: 20px 0;">
            <strong><?php _e('Account:', 'hisab-financial-tracker'); ?></strong> <?php echo esc_html($account->account_name); ?> 
            (<?php echo esc_html($account->bank_name); ?>) - 
            <strong><?php _e('Balance:', 'hisab-financial-tracker'); ?></strong> 
            <?php echo $account->currency === 'NPR' ? '₹' : '$'; ?><?php echo number_format($account->current_balance, 2); ?>
        </p>
    <?php else: ?>
        <h1><?php echo $is_edit ? __('Edit Bank Transaction', 'hisab-financial-tracker') : __('Add Bank Transaction', 'hisab-financial-tracker'); ?></h1>
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
    
    <?php if ($account): ?>
        <form method="post" class="hisab-form">
            <?php wp_nonce_field('hisab_bank_transaction', '_wpnonce'); ?>
            
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
    <?php else: ?>
        <div class="hisab-error" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 40px; text-align: center; color: #666;">
            <h3><?php _e('Account Not Found', 'hisab-financial-tracker'); ?></h3>
            <p><?php _e('The specified bank account could not be found.', 'hisab-financial-tracker'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=hisab-bank-accounts'); ?>" class="button button-primary"><?php _e('Back to Accounts', 'hisab-financial-tracker'); ?></a>
        </div>
    <?php endif; ?>
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
.hisab-form-group input[type="date"],
.hisab-form-group select,
.hisab-form-group textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #8c8f94;
    border-radius: 3px;
    font-size: 14px;
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

.hisab-account-info {
    font-size: 14px;
}
</style>

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
});
</script>
