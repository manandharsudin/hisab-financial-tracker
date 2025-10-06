<?php
/**
 * Add Transaction view template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo $edit_transaction ? __('Edit Transaction', 'hisab-financial-tracker') : __('Add New Transaction', 'hisab-financial-tracker'); ?></h1>
    
    <form id="hisab-transaction-form" class="hisab-form">
        <?php wp_nonce_field('hisab_transaction', 'nonce'); ?>
        <?php if ($edit_transaction): ?>
            <input type="hidden" id="edit-transaction-id" name="edit_transaction_id" value="<?php echo $edit_transaction->id; ?>">
        <?php endif; ?>
        
        <div class="hisab-form-row">
            <div class="hisab-form-group">
                <label for="transaction-type"><?php _e('Transaction Type', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                <select id="transaction-type" name="type" required>
                    <option value=""><?php _e('Select Type', 'hisab-financial-tracker'); ?></option>
                    <option value="income" <?php selected($edit_transaction ? $edit_transaction->type : '', 'income'); ?>><?php _e('Income', 'hisab-financial-tracker'); ?></option>
                    <option value="expense" <?php selected($edit_transaction ? $edit_transaction->type : '', 'expense'); ?>><?php _e('Expense', 'hisab-financial-tracker'); ?></option>
                </select>
            </div>

            <div class="hisab-form-group">
                <label for="transaction-category"><?php _e('Category', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                <select id="transaction-category" name="category_id" required 
                        data-income-categories="<?php echo esc_attr(json_encode($income_categories ?: [])); ?>"
                        data-expense-categories="<?php echo esc_attr(json_encode($expense_categories ?: [])); ?>"
                        data-selected-category="<?php echo $edit_transaction ? esc_attr($edit_transaction->category_id) : ''; ?>">
                    <option value=""><?php _e('Select Transaction Type First', 'hisab-financial-tracker'); ?></option>
                </select>
            </div>
            
            
        </div>
        
        <div class="hisab-form-row">
            <div class="hisab-form-group">
                <label for="transaction-amount"><?php _e('Amount', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                <input type="number" id="transaction-amount" name="amount" step="0.01" min="0" value="<?php echo $edit_transaction ? esc_attr($edit_transaction->amount) : ''; ?>" required>
            </div>
            <div class="hisab-form-group">
                <label for="transaction-owner"><?php _e('Owner', 'hisab-financial-tracker'); ?></label>
                <select id="transaction-owner" name="owner_id" 
                        data-owners="<?php echo esc_attr(json_encode($owners ?: [])); ?>"
                        data-selected-owner="<?php echo $edit_transaction ? esc_attr($edit_transaction->owner_id) : ''; ?>">
                    <option value=""><?php _e('Select Owner (Optional)', 'hisab-financial-tracker'); ?></option>
                    <?php foreach ($owners as $owner): ?>
                        <option value="<?php echo $owner->id; ?>" <?php selected($edit_transaction ? $edit_transaction->owner_id : '', $owner->id); ?>>
                            <?php echo esc_html($owner->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="hisab-form-row">
            <div class="hisab-form-group">
                <label for="transaction-payment-method"><?php _e('Payment Method', 'hisab-financial-tracker'); ?></label>
                <select id="transaction-payment-method" name="payment_method">
                    <option value=""><?php _e('Select Payment Method', 'hisab-financial-tracker'); ?></option>
                    <option value="cash" <?php selected($edit_transaction ? $edit_transaction->payment_method : '', 'cash'); ?>><?php _e('Cash', 'hisab-financial-tracker'); ?></option>
                    <option value="credit_card" <?php selected($edit_transaction ? $edit_transaction->payment_method : '', 'credit_card'); ?>><?php _e('Credit Card', 'hisab-financial-tracker'); ?></option>
                    <option value="debit_card" <?php selected($edit_transaction ? $edit_transaction->payment_method : '', 'debit_card'); ?>><?php _e('Debit Card', 'hisab-financial-tracker'); ?></option>
                    <option value="bank_transfer" <?php selected($edit_transaction ? $edit_transaction->payment_method : '', 'bank_transfer'); ?>><?php _e('Bank Transfer', 'hisab-financial-tracker'); ?></option>
                    <option value="phone_pay" <?php selected($edit_transaction ? $edit_transaction->payment_method : '', 'phone_pay'); ?>><?php _e('Phone Pay', 'hisab-financial-tracker'); ?></option>
                    <option value="check" <?php selected($edit_transaction ? $edit_transaction->payment_method : '', 'check'); ?>><?php _e('Check', 'hisab-financial-tracker'); ?></option>
                    <option value="digital_wallet" <?php selected($edit_transaction ? $edit_transaction->payment_method : '', 'digital_wallet'); ?>><?php _e('Digital Wallet', 'hisab-financial-tracker'); ?></option>
                    <option value="other" <?php selected($edit_transaction ? $edit_transaction->payment_method : '', 'other'); ?>><?php _e('Other', 'hisab-financial-tracker'); ?></option>
                </select>
            </div>
            <div class="hisab-form-group">
                <label for="transaction-bill-image"><?php _e('Bill Image', 'hisab-financial-tracker'); ?></label>
                <div class="hisab-media-uploader">
                    <input type="hidden" id="transaction-bill-image-id" name="bill_image_id" value="<?php echo $edit_transaction ? esc_attr($edit_transaction->bill_image_id) : ''; ?>">
                    <button type="button" class="button" id="upload-bill-image">
                        <?php _e('Select Bill Image', 'hisab-financial-tracker'); ?>
                    </button>
                    <button type="button" class="button" id="remove-bill-image" style="display: none;">
                        <?php _e('Remove Image', 'hisab-financial-tracker'); ?>
                    </button>
                    <div id="bill-image-preview" style="margin-top: 10px;"></div>
                </div>
            </div>
        </div>
        
        <!-- Bank Account Selection (shown for bank_transfer and phone_pay) -->
        <div class="hisab-form-row" id="bank-account-row" style="display: none;">
            <div class="hisab-form-group">
                <label for="bank-account-id"><?php _e('Bank Account', 'hisab-financial-tracker'); ?></label>
                <select id="bank-account-id" name="bank_account_id">
                    <option value=""><?php _e('Select Bank Account', 'hisab-financial-tracker'); ?></option>
                    <?php if (!empty($bank_accounts_npr)): ?>
                        <optgroup label="<?php _e('NPR Accounts', 'hisab-financial-tracker'); ?>">
                            <?php foreach ($bank_accounts_npr as $account): ?>
                                <option value="<?php echo $account->id; ?>" data-currency="NPR" <?php selected($edit_transaction ? $edit_transaction->bank_account_id : '', $account->id); ?>>
                                    <?php echo esc_html($account->account_name . ' (' . $account->bank_name . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                    <?php if (!empty($bank_accounts_usd)): ?>
                        <optgroup label="<?php _e('USD Accounts', 'hisab-financial-tracker'); ?>">
                            <?php foreach ($bank_accounts_usd as $account): ?>
                                <option value="<?php echo $account->id; ?>" data-currency="USD" <?php selected($edit_transaction ? $edit_transaction->bank_account_id : '', $account->id); ?>>
                                    <?php echo esc_html($account->account_name . ' (' . $account->bank_name . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                </select>
                <p class="description"><?php _e('Select the bank account for this transaction', 'hisab-financial-tracker'); ?></p>
            </div>
        </div>
        
        <!-- Phone Pay Reference (shown for phone_pay only) -->
        <div class="hisab-form-row" id="phone-pay-reference-row" style="display: none;">
            <div class="hisab-form-group">
                <label for="phone-pay-reference"><?php _e('Phone Pay Reference', 'hisab-financial-tracker'); ?></label>
                <input type="text" id="phone-pay-reference" name="phone_pay_reference" value="<?php echo $edit_transaction ? esc_attr($edit_transaction->phone_pay_reference) : ''; ?>" placeholder="<?php _e('Enter phone pay transaction reference', 'hisab-financial-tracker'); ?>">
                <p class="description"><?php _e('Enter the phone pay transaction reference number', 'hisab-financial-tracker'); ?></p>
            </div>
        </div>
        
        <div class="hisab-form-row">
            <div class="hisab-form-group">
                <label for="transaction-tax"><?php _e('Tax Amount', 'hisab-financial-tracker'); ?></label>
                <input type="number" id="transaction-tax" name="transaction_tax" step="0.01" min="0" placeholder="0.00" value="<?php echo $edit_transaction ? esc_attr($edit_transaction->transaction_tax) : ''; ?>">
            </div>
            <div class="hisab-form-group">
                <label for="transaction-discount"><?php _e('Discount Amount', 'hisab-financial-tracker'); ?></label>
                <input type="number" id="transaction-discount" name="transaction_discount" step="0.01" min="0" placeholder="0.00" value="<?php echo $edit_transaction ? esc_attr($edit_transaction->transaction_discount) : ''; ?>">
            </div>
        </div>
        
        <div class="hisab-form-row">
            <div class="hisab-form-group">
                <label for="transaction-description"><?php _e('Description', 'hisab-financial-tracker'); ?></label>
                <textarea id="transaction-description" name="description" rows="3" placeholder="<?php _e('Enter transaction description', 'hisab-financial-tracker'); ?>"><?php echo $edit_transaction ? esc_textarea($edit_transaction->description) : ''; ?></textarea>
            </div>
        </div>
        
        <div class="hisab-form-row">
            <?php $default_calendar = get_option('hisab_default_calendar', 'ad'); ?>
            <div class="hisab-form-group">
                <label for="date-calendar-type"><?php _e('Calendar Type', 'hisab-financial-tracker'); ?></label>
                <select id="date-calendar-type" name="calendar_type" data-default="<?php echo esc_attr($default_calendar); ?>">
                    <option value="ad" <?php selected($default_calendar, 'ad'); ?>><?php _e('AD (Gregorian)', 'hisab-financial-tracker'); ?></option>
                    <option value="bs" <?php selected($default_calendar, 'bs'); ?>><?php _e('BS (Bikram Sambat)', 'hisab-financial-tracker'); ?></option>
                </select>
            </div>

            <div class="hisab-form-group" id="ad-date-row">
                <label for="transaction-date"><?php _e('Transaction Date (AD)', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                <input type="date" id="transaction-date" name="transaction_date" value="<?php echo $edit_transaction ? esc_attr($edit_transaction->transaction_date) : date('Y-m-d'); ?>" required>
            </div>
        
            <div class="hisab-form-group" id="bs-date-row" style="display: none;">
                <label><?php _e('Transaction Date (BS)', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                <div class="bs-date-inputs">
                    <select id="bs-year" name="bs_year">
                        <option value=""><?php _e('Year', 'hisab-financial-tracker'); ?></option>
                        <?php
                            $current_bs = HisabNepaliDate::get_current_bs_date();
                            if ($current_bs) {
                                $bs_years = HisabNepaliDate::get_bs_year_range($current_bs['year'], 5);
                                foreach ($bs_years as $year) {
                                    $selected = '';
                                    if ($edit_transaction && $edit_transaction->bs_year) {
                                        $selected = ($year == $edit_transaction->bs_year) ? 'selected' : '';
                                    } elseif (!$edit_transaction) {
                                        $selected = ($year == $current_bs['year']) ? 'selected' : '';
                                    }
                                    echo '<option value="' . $year . '" ' . $selected . '>' . $year . '</option>';
                                }
                            } else {
                                for ($year = 2075; $year <= 2085; $year++) {
                                    $selected = '';
                                    if ($edit_transaction && $edit_transaction->bs_year) {
                                        $selected = ($year == $edit_transaction->bs_year) ? 'selected' : '';
                                    } elseif (!$edit_transaction) {
                                        $selected = ($year == 2081) ? 'selected' : '';
                                    }
                                    echo '<option value="' . $year . '" ' . $selected . '>' . $year . '</option>';
                                }
                            }
                        ?>
                    </select>
                    <select id="bs-month" name="bs_month">
                        <option value=""><?php _e('Month', 'hisab-financial-tracker'); ?></option>
                        <?php
                            $bs_months = HisabNepaliDate::get_bs_months();
                            $current_bs = HisabNepaliDate::get_current_bs_date();
                            foreach ($bs_months as $month) {
                                $selected = '';
                                if ($edit_transaction && $edit_transaction->bs_month) {
                                    $selected = ($month['number'] == $edit_transaction->bs_month) ? 'selected' : '';
                                } elseif (!$edit_transaction && $current_bs) {
                                    $selected = ($month['number'] == $current_bs['month']) ? 'selected' : '';
                                }
                                echo '<option value="' . $month['number'] . '" ' . $selected . '>' . $month['number'] . ' - ' . $month['name_en'] . '</option>';
                            }
                        ?>
                    </select>
                    <select id="bs-day" name="bs_day">
                        <option value=""><?php _e('Day', 'hisab-financial-tracker'); ?></option>
                        <?php
                            $current_bs = HisabNepaliDate::get_current_bs_date();
                            for ($day = 1; $day <= 32; $day++) {
                                $selected = '';
                                if ($edit_transaction && $edit_transaction->bs_day) {
                                    $selected = ($day == $edit_transaction->bs_day) ? 'selected' : '';
                                } elseif (!$edit_transaction && $current_bs) {
                                    $selected = ($day == $current_bs['day']) ? 'selected' : '';
                                }
                                echo '<option value="' . $day . '" ' . $selected . '>' . $day . '</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="hisab-form-actions">
            <button type="submit" class="button button-primary">
                <?php echo $edit_transaction ? __('Update Transaction', 'hisab-financial-tracker') : __('Save Transaction', 'hisab-financial-tracker'); ?>
            </button>
            <?php if (!$edit_transaction): ?>
            <button type="button" class="button" onclick="document.getElementById('hisab-transaction-form').reset();">
                <?php _e('Reset Form', 'hisab-financial-tracker'); ?>
            </button>
            <?php endif; ?>
        </div>
    </form>
    
    <?php if ($edit_transaction): ?>
    <?php 
    // Check if transaction has details
    $database = new HisabDatabase();
    $transaction_details = $database->get_transaction_details($edit_transaction->id);
    if (!empty($transaction_details)): 
    ?>
    <div class="hisab-edit-details-section" style="margin-top: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
        <h3><?php _e('Transaction Details', 'hisab-financial-tracker'); ?></h3>
        <p><?php _e('This transaction has itemized details. You can view or update them.', 'hisab-financial-tracker'); ?></p>
        <button type="button" class="button button-secondary" id="edit-transaction-details" data-transaction-id="<?php echo $edit_transaction->id; ?>">
            <?php _e('Update Transaction Details', 'hisab-financial-tracker'); ?>
        </button>
    </div>
    <?php endif; ?>
    <?php endif; ?>
    
    <div id="hisab-form-messages"></div>
</div>

<!-- Transaction Details Modal -->
<div id="transaction-details-modal" class="hisab-modal" style="display: none;">
    <div class="hisab-modal-content">
        <div class="hisab-modal-header">
            <h3 id="modal-title"><?php _e('Transaction Details', 'hisab-financial-tracker'); ?></h3>
            <span class="hisab-modal-close">&times;</span>
        </div>
        <div class="hisab-modal-body">
            <div id="transaction-info" class="hisab-transaction-info">
                <!-- Transaction summary will be loaded here -->
            </div>
            
            <div class="hisab-details-form">
                <h4><?php _e('Itemized Details', 'hisab-financial-tracker'); ?></h4>
                <div id="details-items">
                    <!-- Dynamic items will be added here -->
                </div>
                <button type="button" class="button button-secondary" id="add-detail-item">
                    <?php _e('+ Add Item', 'hisab-financial-tracker'); ?>
                </button>
            </div>
            
            <div class="hisab-details-summary">
                <div class="summary-row">
                    <span><?php _e('Subtotal:', 'hisab-financial-tracker'); ?></span>
                    <span id="details-subtotal">0.00</span>
                </div>
                <div class="summary-row">
                    <span><?php _e('Tax:', 'hisab-financial-tracker'); ?></span>
                    <span id="details-tax">0.00</span>
                </div>
                <div class="summary-row">
                    <span><?php _e('Discount:', 'hisab-financial-tracker'); ?></span>
                    <span id="details-discount">0.00</span>
                </div>
                <div class="summary-row total-row">
                    <span><?php _e('Grand Total:', 'hisab-financial-tracker'); ?></span>
                    <span id="details-grand-total">0.00</span>
                </div>
            </div>
            
            <div id="details-messages"></div>
        </div>
        <div class="hisab-modal-footer">
            <button type="button" class="button button-primary" id="save-details">
                <?php _e('Save Details', 'hisab-financial-tracker'); ?>
            </button>
            <button type="button" class="button" id="cancel-details">
                <?php _e('Cancel', 'hisab-financial-tracker'); ?>
            </button>
        </div>
    </div>
</div>