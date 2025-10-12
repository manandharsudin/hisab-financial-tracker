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

// Form is now handled via AJAX - no server-side processing needed

// Get all active bank accounts
$all_accounts = $bank_account->get_all_accounts(array('is_active' => 1));
$npr_accounts = array_filter($all_accounts, function($account) {
    return $account->currency === 'NPR';
});
$usd_accounts = array_filter($all_accounts, function($account) {
    return $account->currency === 'USD';
});

// Set default values for form
$default_date = current_time('Y-m-d');
?>

<div class="wrap">
    <h1><?php _e('Transfer Between Accounts', 'hisab-financial-tracker'); ?></h1>
    
    <!-- Success/Error messages are now handled via AJAX -->
    
    <?php if (empty($all_accounts)): ?>
        <div class="hisab-no-accounts" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 40px; text-align: center; color: #666;">
            <h3><?php _e('No Bank Accounts Found', 'hisab-financial-tracker'); ?></h3>
            <p><?php _e('You need at least two active bank accounts to make transfers.', 'hisab-financial-tracker'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=hisab-add-bank-account'); ?>" class="button button-primary"><?php _e('Add Bank Account', 'hisab-financial-tracker'); ?></a>
        </div>
    <?php else: ?>
        <form method="post" class="hisab-form">
            <?php wp_nonce_field('hisab_transaction', '_wpnonce'); ?>
            
            <div class="hisab-form-row">
                <div class="hisab-form-group">
                    <label for="from_account_id"><?php _e('From Account', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                    <select id="from_account_id" name="from_account_id" required>
                        <option value=""><?php _e('Select Source Account', 'hisab-financial-tracker'); ?></option>
                        <?php if (!empty($npr_accounts)): ?>
                            <optgroup label="<?php _e('NPR Accounts', 'hisab-financial-tracker'); ?>">
                                <?php foreach ($npr_accounts as $account): ?>
                                    <option value="<?php echo $account->id; ?>" data-currency="NPR" data-balance="<?php echo $account->current_balance; ?>">
                                        <?php echo esc_html($account->account_name . ' (' . $account->bank_name . ') - ₹' . number_format($account->current_balance, 2)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                        <?php if (!empty($usd_accounts)): ?>
                            <optgroup label="<?php _e('USD Accounts', 'hisab-financial-tracker'); ?>">
                                <?php foreach ($usd_accounts as $account): ?>
                                    <option value="<?php echo $account->id; ?>" data-currency="USD" data-balance="<?php echo $account->current_balance; ?>">
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
                                    <option value="<?php echo $account->id; ?>" data-currency="NPR" data-balance="<?php echo $account->current_balance; ?>">
                                        <?php echo esc_html($account->account_name . ' (' . $account->bank_name . ') - ₹' . number_format($account->current_balance, 2)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                        <?php if (!empty($usd_accounts)): ?>
                            <optgroup label="<?php _e('USD Accounts', 'hisab-financial-tracker'); ?>">
                                <?php foreach ($usd_accounts as $account): ?>
                                    <option value="<?php echo $account->id; ?>" data-currency="USD" data-balance="<?php echo $account->current_balance; ?>">
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
                        <input type="number" id="amount" name="amount" step="0.01" min="0" value="" required style="flex: 1;">
                    </div>
                    <div id="amount-validation" style="margin-top: 5px; font-size: 12px; color: #d63638;"></div>
                </div>
                <div class="hisab-form-group">
                    <label for="transfer-date-calendar-type"><?php _e('Calendar Type', 'hisab-financial-tracker'); ?></label>
                    <select id="transfer-date-calendar-type" name="calendar_type" data-default="<?php echo esc_attr(get_option('hisab_default_calendar', 'ad')); ?>">
                        <option value="ad" <?php selected(get_option('hisab_default_calendar', 'ad'), 'ad'); ?>><?php _e('AD (Gregorian)', 'hisab-financial-tracker'); ?></option>
                        <option value="bs" <?php selected(get_option('hisab_default_calendar', 'ad'), 'bs'); ?>><?php _e('BS (Bikram Sambat)', 'hisab-financial-tracker'); ?></option>
                    </select>
                </div>
            </div>
            
            <div class="hisab-form-row">
                <div class="hisab-form-group" id="transfer-ad-date-row">
                    <label for="transaction_date"><?php _e('Transfer Date (AD)', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                    <input type="date" id="transaction_date" name="transaction_date" value="<?php echo esc_attr($default_date); ?>" required>
                </div>
        
                <div class="hisab-form-group" id="transfer-bs-date-row" style="display: none;">
                    <label><?php _e('Transfer Date (BS)', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                    <div class="bs-date-inputs">
                        <select id="transfer-bs-year" name="bs_year">
                            <option value=""><?php _e('Year', 'hisab-financial-tracker'); ?></option>
                            <?php
                                $bs_years = HisabNepaliDate::get_bs_year_range(null, 20);
                                foreach ($bs_years as $year) {
                                    echo '<option value="' . $year . '">' . $year . '</option>';
                                }
                            ?>
                        </select>
                        <select id="transfer-bs-month" name="bs_month">
                            <option value=""><?php _e('Month', 'hisab-financial-tracker'); ?></option>
                            <?php
                                $bs_months = HisabNepaliDate::get_bs_months();
                                foreach ($bs_months as $month) {
                                    echo '<option value="' . $month['number'] . '">' . $month['name_en'] . '</option>';
                                }
                            ?>
                        </select>
                        <select id="transfer-bs-day" name="bs_day">
                            <option value=""><?php _e('Day', 'hisab-financial-tracker'); ?></option>
                            <?php for ($day = 1; $day <= 32; $day++): ?>
                                <option value="<?php echo $day; ?>"><?php echo $day; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="hisab-form-row">
                <div class="hisab-form-group">
                    <label for="description"><?php _e('Transfer Description', 'hisab-financial-tracker'); ?></label>
                    <textarea id="description" name="description" rows="3" placeholder="<?php _e('Enter transfer description', 'hisab-financial-tracker'); ?>"></textarea>
                </div>
            </div>
            
            <div class="hisab-form-actions">
                <input type="submit" name="submit_transfer" class="button button-primary" value="<?php _e('Transfer Money', 'hisab-financial-tracker'); ?>">
                <a href="<?php echo admin_url('admin.php?page=hisab-bank-accounts'); ?>" class="button"><?php _e('Cancel', 'hisab-financial-tracker'); ?></a>
            </div>
        </form>
    <?php endif; ?>
</div>


