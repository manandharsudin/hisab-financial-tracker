<?php
/**
 * Transfer Between Accounts Page
 */

if (!defined('ABSPATH')) {
    exit;
}

// Initialize classes
$bank_account = new HisabBankAccount();
$bank_transaction = new HisabBankTransaction();

// Handle form submission
if (isset($_POST['submit_transfer'])) {
    $nonce = sanitize_text_field($_POST['_wpnonce']);
    if (!wp_verify_nonce($nonce, 'hisab_transfer')) {
        $error_message = __('Security check failed. Please try again.', 'hisab-financial-tracker');
    } else {
        $from_account_id = intval($_POST['from_account_id']);
        $to_account_id = intval($_POST['to_account_id']);
        $amount = floatval($_POST['amount']);
        $description = sanitize_textarea_field($_POST['description']);
        $transaction_date = sanitize_text_field($_POST['transaction_date']);
        
        // Validate accounts
        if ($from_account_id === $to_account_id) {
            $error_message = __('Cannot transfer to the same account.', 'hisab-financial-tracker');
        } else {
            // Get source account
            $from_account = $bank_account->get_account($from_account_id);
            $to_account = $bank_account->get_account($to_account_id);
            
            if (!$from_account || !$to_account) {
                $error_message = __('One or both accounts not found.', 'hisab-financial-tracker');
            } elseif ($from_account->currency !== $to_account->currency) {
                $error_message = __('Cannot transfer between accounts with different currencies.', 'hisab-financial-tracker');
            } elseif ($from_account->current_balance < $amount) {
                $error_message = __('Insufficient balance in source account.', 'hisab-financial-tracker');
            } else {
                // Create transfer
                $result = $bank_transaction->create_transfer(
                    $from_account_id,
                    $to_account_id,
                    $amount,
                    $description,
                    $transaction_date
                );
                
                if (is_wp_error($result)) {
                    $error_message = $result->get_error_message();
                } else {
                    $success_message = __('Transfer completed successfully.', 'hisab-financial-tracker');
                    // Clear form data
                    $_POST = array();
                }
            }
        }
    }
}

// Get all active bank accounts
$all_accounts = $bank_account->get_all_accounts(array('is_active' => 1));
$npr_accounts = array_filter($all_accounts, function($account) {
    return $account->currency === 'NPR';
});
$usd_accounts = array_filter($all_accounts, function($account) {
    return $account->currency === 'USD';
});

// Set default values
$defaults = array(
    'from_account_id' => '',
    'to_account_id' => '',
    'amount' => 0.00,
    'description' => '',
    'transaction_date' => current_time('Y-m-d')
);

$form_data = array(
    'from_account_id' => isset($_POST['from_account_id']) ? intval($_POST['from_account_id']) : $defaults['from_account_id'],
    'to_account_id' => isset($_POST['to_account_id']) ? intval($_POST['to_account_id']) : $defaults['to_account_id'],
    'amount' => isset($_POST['amount']) ? floatval($_POST['amount']) : $defaults['amount'],
    'description' => isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : $defaults['description'],
    'transaction_date' => isset($_POST['transaction_date']) ? sanitize_text_field($_POST['transaction_date']) : $defaults['transaction_date']
);
?>

<div class="wrap">
    <h1><?php _e('Transfer Between Accounts', 'hisab-financial-tracker'); ?></h1>
    
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
    
    <?php if (empty($all_accounts)): ?>
        <div class="hisab-no-accounts" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 40px; text-align: center; color: #666;">
            <h3><?php _e('No Bank Accounts Found', 'hisab-financial-tracker'); ?></h3>
            <p><?php _e('You need at least two active bank accounts to make transfers.', 'hisab-financial-tracker'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=hisab-add-bank-account'); ?>" class="button button-primary"><?php _e('Add Bank Account', 'hisab-financial-tracker'); ?></a>
        </div>
    <?php else: ?>
        <form method="post" class="hisab-form">
            <?php wp_nonce_field('hisab_transfer', '_wpnonce'); ?>
            
            <div class="hisab-form-row">
                <div class="hisab-form-group">
                    <label for="from_account_id"><?php _e('From Account', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                    <select id="from_account_id" name="from_account_id" required>
                        <option value=""><?php _e('Select Source Account', 'hisab-financial-tracker'); ?></option>
                        <?php if (!empty($npr_accounts)): ?>
                            <optgroup label="<?php _e('NPR Accounts', 'hisab-financial-tracker'); ?>">
                                <?php foreach ($npr_accounts as $account): ?>
                                    <option value="<?php echo $account->id; ?>" data-currency="NPR" data-balance="<?php echo $account->current_balance; ?>" <?php selected($form_data['from_account_id'], $account->id); ?>>
                                        <?php echo esc_html($account->account_name . ' (' . $account->bank_name . ') - ₹' . number_format($account->current_balance, 2)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                        <?php if (!empty($usd_accounts)): ?>
                            <optgroup label="<?php _e('USD Accounts', 'hisab-financial-tracker'); ?>">
                                <?php foreach ($usd_accounts as $account): ?>
                                    <option value="<?php echo $account->id; ?>" data-currency="USD" data-balance="<?php echo $account->current_balance; ?>" <?php selected($form_data['from_account_id'], $account->id); ?>>
                                        <?php echo esc_html($account->account_name . ' (' . $account->bank_name . ') - $' . number_format($account->current_balance, 2)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                    </select>
                    <div id="from-account-balance" style="margin-top: 5px; font-size: 12px; color: #666;"></div>
                </div>
                <div class="hisab-form-group">
                    <label for="to_account_id"><?php _e('To Account', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                    <select id="to_account_id" name="to_account_id" required>
                        <option value=""><?php _e('Select Destination Account', 'hisab-financial-tracker'); ?></option>
                        <?php if (!empty($npr_accounts)): ?>
                            <optgroup label="<?php _e('NPR Accounts', 'hisab-financial-tracker'); ?>">
                                <?php foreach ($npr_accounts as $account): ?>
                                    <option value="<?php echo $account->id; ?>" data-currency="NPR" data-balance="<?php echo $account->current_balance; ?>" <?php selected($form_data['to_account_id'], $account->id); ?>>
                                        <?php echo esc_html($account->account_name . ' (' . $account->bank_name . ') - ₹' . number_format($account->current_balance, 2)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                        <?php if (!empty($usd_accounts)): ?>
                            <optgroup label="<?php _e('USD Accounts', 'hisab-financial-tracker'); ?>">
                                <?php foreach ($usd_accounts as $account): ?>
                                    <option value="<?php echo $account->id; ?>" data-currency="USD" data-balance="<?php echo $account->current_balance; ?>" <?php selected($form_data['to_account_id'], $account->id); ?>>
                                        <?php echo esc_html($account->account_name . ' (' . $account->bank_name . ') - $' . number_format($account->current_balance, 2)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                    </select>
                    <div id="to-account-balance" style="margin-top: 5px; font-size: 12px; color: #666;"></div>
                </div>
            </div>
            
            <div class="hisab-form-row">
                <div class="hisab-form-group">
                    <label for="amount"><?php _e('Transfer Amount', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                    <div style="display: flex; align-items: center;">
                        <span id="currency-symbol" style="margin-right: 5px; font-weight: bold;">₹</span>
                        <input type="number" id="amount" name="amount" step="0.01" min="0" value="<?php echo esc_attr($form_data['amount']); ?>" required style="flex: 1;">
                    </div>
                    <div id="amount-validation" style="margin-top: 5px; font-size: 12px; color: #d63638;"></div>
                </div>
                <div class="hisab-form-group">
                    <label for="transaction_date"><?php _e('Transfer Date', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                    <input type="date" id="transaction_date" name="transaction_date" value="<?php echo esc_attr($form_data['transaction_date']); ?>" required>
                </div>
            </div>
            
            <div class="hisab-form-row">
                <div class="hisab-form-group">
                    <label for="description"><?php _e('Transfer Description', 'hisab-financial-tracker'); ?></label>
                    <textarea id="description" name="description" rows="3" placeholder="<?php _e('Enter transfer description', 'hisab-financial-tracker'); ?>"><?php echo esc_textarea($form_data['description']); ?></textarea>
                </div>
            </div>
            
            <div class="hisab-form-actions">
                <input type="submit" name="submit_transfer" class="button button-primary" value="<?php _e('Transfer Money', 'hisab-financial-tracker'); ?>">
                <a href="<?php echo admin_url('admin.php?page=hisab-bank-accounts'); ?>" class="button"><?php _e('Cancel', 'hisab-financial-tracker'); ?></a>
            </div>
        </form>
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

<script>
jQuery(document).ready(function($) {
    // Update currency symbol and account balances when from account changes
    $('#from_account_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const currency = selectedOption.data('currency');
        const balance = selectedOption.data('balance');
        
        // Update currency symbol
        $('#currency-symbol').text(currency === 'NPR' ? '₹' : '$');
        
        // Update from account balance display
        if (balance !== undefined) {
            $('#from-account-balance').text('Available Balance: ' + (currency === 'NPR' ? '₹' : '$') + parseFloat(balance).toFixed(2));
        } else {
            $('#from-account-balance').text('');
        }
        
        // Filter to account options to same currency
        filterToAccountOptions(currency);
        
        // Clear to account selection
        $('#to_account_id').val('');
        $('#to-account-balance').text('');
        
        // Validate amount
        validateAmount();
    });
    
    // Update to account balance when to account changes
    $('#to_account_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const balance = selectedOption.data('balance');
        const currency = selectedOption.data('currency');
        
        if (balance !== undefined) {
            $('#to-account-balance').text('Current Balance: ' + (currency === 'NPR' ? '₹' : '$') + parseFloat(balance).toFixed(2));
        } else {
            $('#to-account-balance').text('');
        }
    });
    
    // Validate amount on input
    $('#amount').on('input', validateAmount);
    
    function filterToAccountOptions(currency) {
        const toAccountSelect = $('#to_account_id');
        const currentValue = toAccountSelect.val();
        
        toAccountSelect.find('option').each(function() {
            const option = $(this);
            const optionCurrency = option.data('currency');
            
            if (option.val() === '') {
                option.show(); // Always show the placeholder
            } else if (optionCurrency === currency) {
                option.show();
            } else {
                option.hide();
            }
        });
        
        // Restore selection if it's still valid
        if (currentValue && toAccountSelect.find('option[value="' + currentValue + '"]').is(':visible')) {
            toAccountSelect.val(currentValue);
        } else {
            toAccountSelect.val('');
        }
    }
    
    function validateAmount() {
        const fromAccountId = $('#from_account_id').val();
        const amount = parseFloat($('#amount').val()) || 0;
        const validationDiv = $('#amount-validation');
        
        if (fromAccountId && amount > 0) {
            const selectedOption = $('#from_account_id').find('option:selected');
            const balance = selectedOption.data('balance');
            
            if (amount > balance) {
                validationDiv.text('Insufficient balance. Available: ' + (selectedOption.data('currency') === 'NPR' ? '₹' : '$') + parseFloat(balance).toFixed(2));
                validationDiv.show();
            } else {
                validationDiv.text('');
                validationDiv.hide();
            }
        } else {
            validationDiv.text('');
            validationDiv.hide();
        }
    }
    
    // Form submission validation
    $('form').on('submit', function(e) {
        const fromAccountId = $('#from_account_id').val();
        const toAccountId = $('#to_account_id').val();
        const amount = parseFloat($('#amount').val()) || 0;
        
        if (fromAccountId === toAccountId) {
            e.preventDefault();
            alert('<?php _e('Cannot transfer to the same account.', 'hisab-financial-tracker'); ?>');
            return false;
        }
        
        if (amount <= 0) {
            e.preventDefault();
            alert('<?php _e('Amount must be greater than zero.', 'hisab-financial-tracker'); ?>');
            return false;
        }
        
        const selectedOption = $('#from_account_id').find('option:selected');
        const balance = selectedOption.data('balance');
        
        if (amount > balance) {
            e.preventDefault();
            alert('<?php _e('Insufficient balance for this transfer.', 'hisab-financial-tracker'); ?>');
            return false;
        }
    });
});
</script>
