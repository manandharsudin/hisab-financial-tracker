<?php
/**
 * Add Transaction view template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Add New Transaction', 'hisab-financial-tracker'); ?></h1>
    
    <form id="hisab-transaction-form" class="hisab-form">
        <?php wp_nonce_field('hisab_transaction', 'hisab_nonce'); ?>
        
        <div class="hisab-form-row">
            <div class="hisab-form-group">
                <label for="transaction-type"><?php _e('Transaction Type', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                <select id="transaction-type" name="type" required>
                    <option value=""><?php _e('Select Type', 'hisab-financial-tracker'); ?></option>
                    <option value="income"><?php _e('Income', 'hisab-financial-tracker'); ?></option>
                    <option value="expense"><?php _e('Expense', 'hisab-financial-tracker'); ?></option>
                </select>
            </div>

            <div class="hisab-form-group">
                <label for="transaction-category"><?php _e('Category', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                <select id="transaction-category" name="category_id" required>
                    <option value=""><?php _e('Select Category', 'hisab-financial-tracker'); ?></option>
                </select>
            </div>
            
            
        </div>
        
        <div class="hisab-form-row">
            <div class="hisab-form-group">
                <label for="transaction-amount"><?php _e('Amount', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                <input type="number" id="transaction-amount" name="amount" step="0.01" min="0" required>
            </div>
            <div class="hisab-form-group">
                <label for="transaction-owner"><?php _e('Owner', 'hisab-financial-tracker'); ?></label>
                <select id="transaction-owner" name="owner_id">
                    <option value=""><?php _e('Select Owner (Optional)', 'hisab-financial-tracker'); ?></option>
                </select>
            </div>
        </div>
        
        <div class="hisab-form-row">
            <div class="hisab-form-group">
                <label for="transaction-payment-method"><?php _e('Payment Method', 'hisab-financial-tracker'); ?></label>
                <select id="transaction-payment-method" name="payment_method">
                    <option value=""><?php _e('Select Payment Method', 'hisab-financial-tracker'); ?></option>
                    <option value="cash"><?php _e('Cash', 'hisab-financial-tracker'); ?></option>
                    <option value="credit_card"><?php _e('Credit Card', 'hisab-financial-tracker'); ?></option>
                    <option value="debit_card"><?php _e('Debit Card', 'hisab-financial-tracker'); ?></option>
                    <option value="bank_transfer"><?php _e('Bank Transfer', 'hisab-financial-tracker'); ?></option>
                    <option value="check"><?php _e('Check', 'hisab-financial-tracker'); ?></option>
                    <option value="digital_wallet"><?php _e('Digital Wallet', 'hisab-financial-tracker'); ?></option>
                    <option value="other"><?php _e('Other', 'hisab-financial-tracker'); ?></option>
                </select>
            </div>
            <div class="hisab-form-group">
                <label for="transaction-bill-image"><?php _e('Bill Image', 'hisab-financial-tracker'); ?></label>
                <input type="file" id="transaction-bill-image" name="bill_image" accept="image/*,.pdf">
                <div id="bill-image-preview" style="margin-top: 10px;"></div>
            </div>
        </div>
        
        <div class="hisab-form-row">
            <div class="hisab-form-group">
                <label for="transaction-tax"><?php _e('Tax Amount', 'hisab-financial-tracker'); ?></label>
                <input type="number" id="transaction-tax" name="transaction_tax" step="0.01" min="0" placeholder="0.00">
            </div>
            <div class="hisab-form-group">
                <label for="transaction-discount"><?php _e('Discount Amount', 'hisab-financial-tracker'); ?></label>
                <input type="number" id="transaction-discount" name="transaction_discount" step="0.01" min="0" placeholder="0.00">
            </div>
        </div>
        
        <div class="hisab-form-row">
            <div class="hisab-form-group">
                <label for="transaction-description"><?php _e('Description', 'hisab-financial-tracker'); ?></label>
                <textarea id="transaction-description" name="description" rows="3" placeholder="<?php _e('Enter transaction description', 'hisab-financial-tracker'); ?>"></textarea>
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
                <input type="date" id="transaction-date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required>
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
                                    $selected = ($year == $current_bs['year']) ? 'selected' : '';
                                    echo '<option value="' . $year . '" ' . $selected . '>' . $year . '</option>';
                                }
                            } else {
                                for ($year = 2075; $year <= 2085; $year++) {
                                    $selected = ($year == 2081) ? 'selected' : '';
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
                                $selected = ($current_bs && $month['number'] == $current_bs['month']) ? 'selected' : '';
                                echo '<option value="' . $month['number'] . '" ' . $selected . '>' . $month['number'] . ' - ' . $month['name_en'] . '</option>';
                            }
                        ?>
                    </select>
                    <select id="bs-day" name="bs_day">
                        <option value=""><?php _e('Day', 'hisab-financial-tracker'); ?></option>
                        <?php
                            $current_bs = HisabNepaliDate::get_current_bs_date();
                            for ($day = 1; $day <= 32; $day++) {
                                $selected = ($current_bs && $day == $current_bs['day']) ? 'selected' : '';
                                echo '<option value="' . $day . '" ' . $selected . '>' . $day . '</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="hisab-form-actions">
            <button type="submit" class="button button-primary">
                <?php _e('Save Transaction', 'hisab-financial-tracker'); ?>
            </button>
            <button type="button" class="button" onclick="document.getElementById('hisab-transaction-form').reset();">
                <?php _e('Reset Form', 'hisab-financial-tracker'); ?>
            </button>
        </div>
    </form>
    
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
    const incomeCategories = <?php echo json_encode($income_categories); ?>;
    const expenseCategories = <?php echo json_encode($expense_categories); ?>;
    const owners = <?php echo json_encode($owners); ?>;
    
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
    
    // File upload preview
    $('#transaction-bill-image').on('change', function(e) {
        const file = e.target.files[0];
        const preview = $('#bill-image-preview');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (file.type.startsWith('image/')) {
                    preview.html('<img src="' + e.target.result + '" style="max-width: 200px; max-height: 150px; border: 1px solid #ddd; border-radius: 4px;">');
                } else {
                    preview.html('<div style="padding: 10px; background: #f0f0f0; border-radius: 4px;">PDF: ' + file.name + '</div>');
                }
            };
            reader.readAsDataURL(file);
        } else {
            preview.empty();
        }
    });
    
    // Update categories based on transaction type
    $('#transaction-type').on('change', function() {
        const type = $(this).val();
        const categorySelect = $('#transaction-category');
        
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
                    hisab_nonce: hisab_ajax.nonce
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
            formData.append('action', 'hisab_save_transaction');
        
            $.ajax({
                url: hisab_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    messagesDiv.empty();
                    
                    if (response.success) {
                        messagesDiv.html('<div class="notice notice-success"><p>' + response.message + '</p></div>');
                        
                        // Show add details button if transaction was saved successfully
                        if (response.data && response.data.transaction_id) {
                            messagesDiv.append('<div style="margin-top: 10px;"><button type="button" class="button button-secondary" id="add-transaction-details" data-transaction-id="' + response.data.transaction_id + '"><?php _e('Add Itemized Details', 'hisab-financial-tracker'); ?></button></div>');
                        }
                        
                        $('#hisab-transaction-form')[0].reset();
                        $('#transaction-category').empty().append('<option value=""><?php _e('Select Category', 'hisab-financial-tracker'); ?></option>');
                        $('#bill-image-preview').empty();
                        // Reset calendar type to default
                        switchCalendarType(defaultCalendarType);
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
                hisab_nonce: hisab_ajax.nonce
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
                <p><strong>Amount:</strong> ₹${parseFloat(currentTransactionData.amount).toFixed(2)}</p>
                <p><strong>Date:</strong> ${formattedDate}</p>
                <p><strong>Tax:</strong> ₹${parseFloat(currentTransactionData.transaction_tax || 0).toFixed(2)}</p>
                <p><strong>Discount:</strong> ₹${parseFloat(currentTransactionData.transaction_discount || 0).toFixed(2)}</p>
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
                hisab_nonce: hisab_ajax.nonce
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
                hisab_nonce: hisab_ajax.nonce
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
});
</script>
