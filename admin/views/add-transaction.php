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
                <select id="transaction-category" name="category_id" required>
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
                <select id="transaction-owner" name="owner_id">
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
            <div class="hisab-form-group">
                <label for="date-calendar-type"><?php _e('Calendar Type', 'hisab-financial-tracker'); ?></label>
                <select id="date-calendar-type" name="calendar_type">
                    <?php $default_calendar = get_option('hisab_default_calendar', 'ad'); ?>
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

<script>
jQuery(document).ready(function($) {
    const incomeCategories = <?php echo json_encode($income_categories ?: []); ?>;
    const expenseCategories = <?php echo json_encode($expense_categories ?: []); ?>;
    const owners = <?php echo json_encode($owners ?: []); ?>;
    
    // Populate owners dropdown
    function populateOwners() {
        const ownerSelect = $('#transaction-owner');
        ownerSelect.empty();
        ownerSelect.append('<option value=""><?php _e('Select Owner (Optional)', 'hisab-financial-tracker'); ?></option>');
        
        if (Array.isArray(owners)) {
            owners.forEach(function(owner) {
                ownerSelect.append(`<option value="${owner.id}">${owner.name}</option>`);
            });
        }
    }
    
    // Initialize owners dropdown
    populateOwners();
    
    // Update categories based on transaction type
    function updateCategories(type, preserveSelection = false) {
        const categorySelect = $('#transaction-category');
        const currentValue = preserveSelection ? categorySelect.val() : '';
        
        categorySelect.empty();
        categorySelect.append('<option value=""><?php _e('Select Category', 'hisab-financial-tracker'); ?></option>');
        
        if (type === 'income') {
            if (Array.isArray(incomeCategories)) {
                incomeCategories.forEach(function(category) {
                    categorySelect.append(`<option value="${category.id}">${category.name}</option>`);
                });
            }
        } else if (type === 'expense') {
            if (Array.isArray(expenseCategories)) {
                expenseCategories.forEach(function(category) {
                    categorySelect.append(`<option value="${category.id}">${category.name}</option>`);
                });
            }
        }
        
        // Restore selection if preserving
        if (preserveSelection && currentValue) {
            categorySelect.val(currentValue);
        }
    }
    
    // Show/hide bank account fields based on payment method
    function toggleBankAccountFields() {
        const paymentMethod = $('#transaction-payment-method').val();
        const bankAccountRow = $('#bank-account-row');
        const phonePayReferenceRow = $('#phone-pay-reference-row');
        const bankAccountSelect = $('#bank-account-id');
        const phonePayReferenceInput = $('#phone-pay-reference');
        
        // Hide all bank-related fields first
        bankAccountRow.hide();
        phonePayReferenceRow.hide();
        bankAccountSelect.prop('required', false);
        phonePayReferenceInput.prop('required', false);
        
        // Show relevant fields based on payment method
        if (paymentMethod === 'bank_transfer' || paymentMethod === 'phone_pay') {
            bankAccountRow.show();
            bankAccountSelect.prop('required', true);
        }
        
        if (paymentMethod === 'phone_pay') {
            phonePayReferenceRow.show();
            phonePayReferenceInput.prop('required', true);
        }
    }
    
    // Bind payment method change event
    $('#transaction-payment-method').on('change', toggleBankAccountFields);
    
    // Initialize bank account fields visibility
    toggleBankAccountFields();
    
    // Initialize edit mode if editing
    <?php if ($edit_transaction): ?>
    // Set the correct calendar type based on BS date data
    if ($('#bs-year').val() && $('#bs-month').val() && $('#bs-day').val()) {
        $('#calendar-type').val('bs');
        $('#ad-date-row').hide();
        $('#bs-date-row').show();
    } else {
        $('#calendar-type').val('ad');
        $('#ad-date-row').show();
        $('#bs-date-row').hide();
    }
    
    // Update categories based on transaction type and select the correct one
    const type = $('#transaction-type').val();
    if (type) {
        updateCategories(type);
        // Select the correct category after updating
        setTimeout(function() {
            $('#transaction-category').val('<?php echo $edit_transaction ? $edit_transaction->category_id : ''; ?>');
        }, 100);
    }
    
    // Select the correct owner
    setTimeout(function() {
        $('#transaction-owner').val('<?php echo $edit_transaction ? $edit_transaction->owner_id : ''; ?>');
    }, 100);
    
    // Handle Update Transaction Details button
    $('#edit-transaction-details').on('click', function() {
        const transactionId = $(this).data('transaction-id');
        loadTransactionDetails(transactionId);
    });
    <?php endif; ?>
    
    // WordPress Media Uploader for bill image
    let mediaUploader;
    
    // Load existing image if in edit mode
    function loadExistingImage() {
        const imageId = $('#transaction-bill-image-id').val();
        if (imageId) {
            // Get attachment data from WordPress
            wp.media.attachment(imageId).fetch().then(function(attachment) {
                if (attachment) {
                    if (attachment.type === 'image') {
                        $('#bill-image-preview').html('<img src="' + attachment.sizes.medium.url + '" style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px;">');
                    } else {
                        $('#bill-image-preview').html('<div style="padding: 10px; background: #f0f0f0; border: 1px solid #ddd; border-radius: 4px;"><strong>' + attachment.filename + '</strong><br><small>' + attachment.mime + '</small></div>');
                    }
                    $('#upload-bill-image').hide();
                    $('#remove-bill-image').show();
                }
            });
        }
    }
    
    // Load existing image on page load
    loadExistingImage();
    
    $('#upload-bill-image').on('click', function(e) {
        e.preventDefault();
        
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        
        mediaUploader = wp.media({
            title: '<?php _e('Select Bill Image', 'hisab-financial-tracker'); ?>',
            button: {
                text: '<?php _e('Use This Image', 'hisab-financial-tracker'); ?>'
            },
            multiple: false,
            library: {
                type: ['image', 'application/pdf']
            }
        });
        
        mediaUploader.on('select', function() {
            const attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#transaction-bill-image-id').val(attachment.id);
            
            if (attachment.type === 'image') {
                $('#bill-image-preview').html('<img src="' + attachment.sizes.medium.url + '" style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px;">');
            } else {
                $('#bill-image-preview').html('<div style="padding: 10px; background: #f0f0f0; border: 1px solid #ddd; border-radius: 4px;"><strong>' + attachment.filename + '</strong><br><small>' + attachment.mime + '</small></div>');
            }
            
            $('#upload-bill-image').hide();
            $('#remove-bill-image').show();
        });
        
        mediaUploader.open();
    });
    
    $('#remove-bill-image').on('click', function(e) {
        e.preventDefault();
        $('#transaction-bill-image-id').val('');
        $('#bill-image-preview').empty();
        $('#upload-bill-image').show();
        $('#remove-bill-image').hide();
    });
    
    // Update categories based on transaction type
    $('#transaction-type').on('change', function() {
        const type = $(this).val();
        const isEditMode = $('#edit-transaction-id').val();
        updateCategories(type, isEditMode);
    });
    
    // Handle calendar type switching
    function switchCalendarType(calendarType) {
        const adDateRow = $('#ad-date-row');
        const bsDateRow = $('#bs-date-row');
        const adDateInput = $('#transaction-date');
        const bsYearSelect = $('#bs-year');
        const bsMonthSelect = $('#bs-month');
        const bsDaySelect = $('#bs-day');

        if (calendarType === 'bs') {
            adDateRow.hide();
            bsDateRow.show();
            adDateInput.prop('required', false);
            bsYearSelect.prop('required', true);
            bsMonthSelect.prop('required', true);
            bsDaySelect.prop('required', true);
        } else {
            adDateRow.show();
            bsDateRow.hide();
            adDateInput.prop('required', true);
            bsYearSelect.prop('required', false);
            bsMonthSelect.prop('required', false);
            bsDaySelect.prop('required', false);
            
            // Clear BS date fields when using AD calendar
            bsYearSelect.val('');
            bsMonthSelect.val('');
            bsDaySelect.val('');
        }
    }

    // Initialize with default calendar type
    const defaultCalendarType = '<?php echo get_option('hisab_default_calendar', 'ad'); ?>';
    switchCalendarType(defaultCalendarType);

    $('#date-calendar-type').on('change', function() {
        switchCalendarType($(this).val());
    });
    
    // Form submission
    $('#hisab-transaction-form').on('submit', function(e) {
        e.preventDefault();
        
        const messagesDiv = $('#hisab-form-messages');
        const calendarType = $('#date-calendar-type').val();
        const categoryId = $('#transaction-category').val();
        
        // Clear previous messages
        messagesDiv.empty();
        
        // Validate required fields
        if (!categoryId) {
            messagesDiv.html('<div class="notice notice-error"><p><?php _e('Please select a category.', 'hisab-financial-tracker'); ?></p></div>');
            return;
        }
        
        // Show loading
        messagesDiv.html('<div class="notice notice-info"><p><?php _e('Saving transaction...', 'hisab-financial-tracker'); ?></p></div>');
        
        // If BS calendar is selected, convert to AD first
        if (calendarType === 'bs') {
            const bsYear = $('#bs-year').val();
            const bsMonth = $('#bs-month').val();
            const bsDay = $('#bs-day').val();
            
            if (!bsYear || !bsMonth || !bsDay) {
                messagesDiv.html('<div class="notice notice-error"><p><?php _e('Please select BS year, month, and day', 'hisab-financial-tracker'); ?></p></div>');
                return;
            }
            
            // Convert BS to AD via AJAX
            $.ajax({
                url: hisab_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'hisab_convert_bs_to_ad',
                    bs_year: bsYear,
                    bs_month: bsMonth,
                    bs_day: bsDay,
                    nonce: hisab_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Set the AD date and submit form
                        $('#transaction-date').val(response.data.ad_date);
                        submitForm();
                    } else {
                        messagesDiv.html('<div class="notice notice-error"><p><?php _e('Date conversion failed. Please try again.', 'hisab-financial-tracker'); ?></p></div>');
                    }
                },
                error: function() {
                    messagesDiv.html('<div class="notice notice-error"><p><?php _e('Date conversion failed. Please try again.', 'hisab-financial-tracker'); ?></p></div>');
                }
            });
        } else {
            // AD calendar selected, submit directly
            submitForm();
        }
        
        function submitForm() {
            const formData = new FormData($('#hisab-transaction-form')[0]);
            
            // Check if we're in edit mode
            const editTransactionId = $('#edit-transaction-id').val();
            if (editTransactionId) {
                formData.append('action', 'hisab_update_transaction');
                formData.append('transaction_id', editTransactionId);
            } else {
                formData.append('action', 'hisab_save_transaction');
            }
            
            // Add bill image ID if selected
            const billImageId = $('#transaction-bill-image-id').val();
            if (billImageId) {
                formData.append('bill_image_id', billImageId);
            }
        
            $.ajax({
                url: hisab_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    messagesDiv.empty();
                    
                    if (response.success) {
                        const isEdit = $('#edit-transaction-id').val();
                        const message = isEdit ? 'Transaction updated successfully!' : response.message;
                        messagesDiv.html('<div class="notice notice-success"><p>' + message + '</p></div>');
                        
                        // Show add details button if transaction was saved successfully and not in edit mode
                        if (response.data && response.data.transaction_id && !isEdit) {
                            messagesDiv.append('<div style="margin-top: 10px;"><button type="button" class="button button-secondary" id="add-transaction-details" data-transaction-id="' + response.data.transaction_id + '"><?php _e('Add Itemized Details', 'hisab-financial-tracker'); ?></button></div>');
                        }
                        
                        // Reset form only if not in edit mode
                        if (!isEdit) {
                            $('#hisab-transaction-form')[0].reset();
                            $('#transaction-category').empty().append('<option value=""><?php _e('Select Category', 'hisab-financial-tracker'); ?></option>');
                            $('#bill-image-preview').empty();
                            $('#transaction-bill-image-id').val('');
                            $('#upload-bill-image').show();
                            $('#remove-bill-image').hide();
                            // Clear BS date fields
                            $('#bs-year').val('');
                            $('#bs-month').val('');
                            $('#bs-day').val('');
                            // Reset calendar type to default
                            switchCalendarType(defaultCalendarType);
                        }
                    } else {
                        messagesDiv.html('<div class="notice notice-error"><p>' + response.message + '</p></div>');
                    }
                },
                error: function() {
                    messagesDiv.empty();
                    messagesDiv.html('<div class="notice notice-error"><p><?php _e('An error occurred while saving the transaction.', 'hisab-financial-tracker'); ?></p></div>');
                }
            });
        }
    });
    
    // Transaction Details Modal functionality
    let currentTransactionId = null;
    let currentTransactionData = null;
    
    // Open modal when "Add Details" button is clicked
    $(document).on('click', '#add-transaction-details', function() {
        currentTransactionId = $(this).data('transaction-id');
        openDetailsModal();
    });
    
    // Close modal
    $('.hisab-modal-close, #cancel-details').on('click', function() {
        closeDetailsModal();
    });
    
    // Close modal when clicking outside
    $(document).on('click', '.hisab-modal', function(e) {
        if (e.target === this) {
            closeDetailsModal();
        }
    });
    
    // Add detail item
    $('#add-detail-item').on('click', function() {
        addDetailItem();
    });
    
    // Remove detail item
    $(document).on('click', '.remove-detail-item', function() {
        $(this).closest('.detail-item').remove();
        updateSummary();
    });
    
    // Calculate item total when rate or quantity changes
    $(document).on('input', '.detail-rate, .detail-quantity', function() {
        const row = $(this).closest('.detail-item');
        const rate = parseFloat(row.find('.detail-rate').val()) || 0;
        const quantity = parseFloat(row.find('.detail-quantity').val()) || 0;
        const total = rate * quantity;
        row.find('.detail-total').val(total.toFixed(2));
        updateSummary();
    });
    
    // Save details
    $('#save-details').on('click', function() {
        saveTransactionDetails();
    });
    
    function openDetailsModal() {
        // Load transaction data
        $.ajax({
            url: hisab_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'hisab_get_transaction',
                transaction_id: currentTransactionId,
                nonce: hisab_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    currentTransactionData = response.data;
                    displayTransactionInfo();
                    loadExistingDetails();
                    $('#transaction-details-modal').show();
                }
            }
        });
    }
    
    function closeDetailsModal() {
        $('#transaction-details-modal').hide();
        currentTransactionId = null;
        currentTransactionData = null;
        $('#details-items').empty();
        updateSummary();
    }
    
    function displayTransactionInfo() {
        if (!currentTransactionData) return;
        
        // Format the date properly
        let formattedDate = 'N/A';
        if (currentTransactionData.transaction_date && currentTransactionData.transaction_date !== '0000-00-00') {
            const date = new Date(currentTransactionData.transaction_date);
            formattedDate = date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
        
        const info = `
            <div class="transaction-summary">
                <h4>${currentTransactionData.description || 'No Description'}</h4>
                <p><strong>Amount:</strong> <?php echo HISAB_CURRENCY_SYMBOL; ?>${parseFloat(currentTransactionData.amount).toFixed(2)}</p>
                <p><strong>Date:</strong> ${formattedDate}</p>
                <p><strong>Tax:</strong> <?php echo HISAB_CURRENCY_SYMBOL; ?>${parseFloat(currentTransactionData.transaction_tax || 0).toFixed(2)}</p>
                <p><strong>Discount:</strong> <?php echo HISAB_CURRENCY_SYMBOL; ?>${parseFloat(currentTransactionData.transaction_discount || 0).toFixed(2)}</p>
            </div>
        `;
        $('#transaction-info').html(info);
        
        // Update summary with transaction data
        $('#details-tax').text(parseFloat(currentTransactionData.transaction_tax || 0).toFixed(2));
        $('#details-discount').text(parseFloat(currentTransactionData.transaction_discount || 0).toFixed(2));
        updateSummary();
    }
    
    function loadExistingDetails() {
        $.ajax({
            url: hisab_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'hisab_get_transaction_details',
                transaction_id: currentTransactionId,
                nonce: hisab_ajax.nonce
            },
            success: function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    response.data.forEach(function(item) {
                        addDetailItem(item);
                    });
                } else {
                    addDetailItem(); // Add one empty item
                }
                updateSummary();
            },
            error: function() {
                addDetailItem(); // Add one empty item on error
                updateSummary();
            }
        });
    }
    
    function addDetailItem(data = null) {
        const item = data || {
            item_name: '',
            rate: 0,
            quantity: 1,
            item_total: 0
        };
        
        const itemHtml = `
            <div class="detail-item">
                <div class="detail-row">
                    <input type="text" class="detail-name" placeholder="Item name" value="${item.item_name}">
                    <input type="number" class="detail-rate" placeholder="Rate" step="0.01" min="0" value="${item.rate}">
                    <input type="number" class="detail-quantity" placeholder="Qty" step="0.01" min="0" value="${item.quantity}">
                    <input type="number" class="detail-total" placeholder="Total" step="0.01" min="0" value="${item.item_total}" readonly>
                    <button type="button" class="remove-detail-item" title="Remove item">×</button>
                </div>
            </div>
        `;
        
        $('#details-items').append(itemHtml);
        
        // Add event listeners for the new item
        const newItem = $('#details-items .detail-item').last();
        newItem.find('.detail-rate, .detail-quantity').on('input', calculateItemTotal);
        newItem.find('.remove-detail-item').on('click', function() {
            $(this).closest('.detail-item').remove();
            updateSummary();
        });
    }
    
    function calculateItemTotal() {
        const $row = $(this).closest('.detail-item');
        const rate = parseFloat($row.find('.detail-rate').val()) || 0;
        const quantity = parseFloat($row.find('.detail-quantity').val()) || 0;
        const total = rate * quantity;
        
        $row.find('.detail-total').val(total.toFixed(2));
        updateSummary();
    }
    
    function updateSummary() {
        let subtotal = 0;
        
        $('.detail-item').each(function() {
            const total = parseFloat($(this).find('.detail-total').val()) || 0;
            subtotal += total;
        });
        
        const tax = parseFloat($('#details-tax').text()) || 0;
        const discount = parseFloat($('#details-discount').text()) || 0;
        const grandTotal = subtotal + tax - discount;
        
        $('#details-subtotal').text(subtotal.toFixed(2));
        $('#details-grand-total').text(grandTotal.toFixed(2));
        
        // Highlight if totals don't match
        const mainAmount = parseFloat(currentTransactionData.amount) || 0;
        const difference = Math.abs(grandTotal - mainAmount);
        
        if (difference > 0.01) {
            $('#details-grand-total').addClass('mismatch');
        } else {
            $('#details-grand-total').removeClass('mismatch');
        }
    }
    
    function saveTransactionDetails() {
        const details = [];
        
        $('.detail-item').each(function() {
            const name = $(this).find('.detail-name').val().trim();
            const rate = parseFloat($(this).find('.detail-rate').val()) || 0;
            const quantity = parseFloat($(this).find('.detail-quantity').val()) || 0;
            const total = parseFloat($(this).find('.detail-total').val()) || 0;
            
            if (name && rate > 0 && quantity > 0) {
                details.push({
                    item_name: name,
                    rate: rate,
                    quantity: quantity,
                    item_total: total
                });
            }
        });
        
        if (details.length === 0) {
            $('#details-messages').html('<div class="notice notice-error"><p><?php _e('Please add at least one item.', 'hisab-financial-tracker'); ?></p></div>');
            return;
        }
        
        $.ajax({
            url: hisab_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'hisab_save_transaction_details',
                transaction_id: currentTransactionId,
                details: details,
                nonce: hisab_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#details-messages').html('<div class="notice notice-success"><p>' + response.message + '</p></div>');
                    setTimeout(function() {
                        closeDetailsModal();
                    }, 1500);
                } else {
                    $('#details-messages').html('<div class="notice notice-error"><p>' + response.message + '</p></div>');
                }
            },
            error: function() {
                $('#details-messages').html('<div class="notice notice-error"><p><?php _e('An error occurred while saving details.', 'hisab-financial-tracker'); ?></p></div>');
            }
        });
    }
    
    // Transaction Details Modal functionality
    function loadTransactionDetails(transactionId) {
        currentTransactionId = transactionId; // Set the transaction ID
        $.ajax({
            url: hisab_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'hisab_get_transaction',
                transaction_id: transactionId,
                nonce: hisab_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    currentTransactionData = response.data;
                    displayTransactionInfo();
                    loadExistingDetails();
                    $('#transaction-details-modal').show();
                } else {
                    alert('Error loading transaction details: ' + response.message);
                }
            },
            error: function() {
                alert('Error loading transaction details');
            }
        });
    }
    
    function displayTransactionInfo() {
        if (!currentTransactionData) return;
        
        let formattedDate = 'N/A';
        if (currentTransactionData.transaction_date && currentTransactionData.transaction_date !== '0000-00-00') {
            formattedDate = new Date(currentTransactionData.transaction_date).toLocaleDateString();
        }
        
        const infoHtml = `
            <div class="transaction-summary">
                <h4>${currentTransactionData.type === 'income' ? 'Income' : 'Expense'}: <?php echo HISAB_CURRENCY_SYMBOL; ?>${parseFloat(currentTransactionData.amount).toFixed(2)}</h4>
                <p><strong>Date:</strong> ${formattedDate}</p>
                <p><strong>Description:</strong> ${currentTransactionData.description || 'N/A'}</p>
            </div>
        `;
        
        $('#transaction-info').html(infoHtml);
    }
    
    
    function updateSummary() {
        let subtotal = 0;
        
        $('.detail-total').each(function() {
            const total = parseFloat($(this).val()) || 0;
            subtotal += total;
        });
        
        const tax = parseFloat($('#details-tax').val()) || 0;
        const discount = parseFloat($('#details-discount').val()) || 0;
        const grandTotal = subtotal + tax - discount;
        
        $('#details-subtotal').text(subtotal.toFixed(2));
        $('#details-grand-total').text(grandTotal.toFixed(2));
    }
    
    // Close modal
    $('.hisab-modal-close, #cancel-details').on('click', function() {
        $('#transaction-details-modal').hide();
    });
    
    // Add detail item
    $(document).on('click', '#add-detail-item', function() {
        addDetailItem();
    });
    
    // Remove detail item
    $(document).on('click', '.remove-detail-item', function() {
        $(this).closest('.detail-item').remove();
        updateSummary();
    });
    
    // Calculate totals
    $(document).on('input', '.detail-rate, .detail-quantity', function() {
        const $row = $(this).closest('.detail-item');
        const rate = parseFloat($row.find('.detail-rate').val()) || 0;
        const quantity = parseFloat($row.find('.detail-quantity').val()) || 0;
        const total = rate * quantity;
        
        $row.find('.detail-total').val(total.toFixed(2));
        updateSummary();
    });
    
    // Update summary on tax/discount change
    $(document).on('input', '#details-tax, #details-discount', function() {
        updateSummary();
    });
    
    // Save details
    $('#save-details').on('click', function() {
        const details = [];
        
        $('.detail-item').each(function() {
            const $item = $(this);
            const name = $item.find('.detail-name').val().trim();
            const rate = parseFloat($item.find('.detail-rate').val()) || 0;
            const quantity = parseFloat($item.find('.detail-quantity').val()) || 0;
            const total = parseFloat($item.find('.detail-total').val()) || 0;
            
            if (name) {
                details.push({
                    item_name: name,
                    rate: rate,
                    quantity: quantity,
                    item_total: total
                });
            }
        });
        
        if (details.length === 0) {
            $('#details-messages').html('<div class="notice notice-error"><p><?php _e('Please add at least one item.', 'hisab-financial-tracker'); ?></p></div>');
            return;
        }
        
        $.ajax({
            url: hisab_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'hisab_save_transaction_details',
                transaction_id: currentTransactionId,
                details: details,
                nonce: hisab_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#details-messages').html('<div class="notice notice-success"><p>' + response.message + '</p></div>');
                    setTimeout(function() {
                        $('#transaction-details-modal').hide();
                    }, 1500);
                } else {
                    $('#details-messages').html('<div class="notice notice-error"><p>' + response.message + '</p></div>');
                }
            },
            error: function() {
                $('#details-messages').html('<div class="notice notice-error"><p><?php _e('An error occurred while saving details.', 'hisab-financial-tracker'); ?></p></div>');
            }
        });
    });
});
</script>
