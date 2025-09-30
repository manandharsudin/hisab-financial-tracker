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
                <label for="transaction-amount"><?php _e('Amount', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                <input type="number" id="transaction-amount" name="amount" step="0.01" min="0" required>
            </div>
        </div>
        
        <div class="hisab-form-row">
            <div class="hisab-form-group">
                <label for="transaction-description"><?php _e('Description', 'hisab-financial-tracker'); ?></label>
                <input type="text" id="transaction-description" name="description" placeholder="<?php _e('Enter transaction description', 'hisab-financial-tracker'); ?>">
            </div>
            
            <div class="hisab-form-group">
                <label for="transaction-category"><?php _e('Category', 'hisab-financial-tracker'); ?></label>
                <select id="transaction-category" name="category_id">
                    <option value=""><?php _e('Select Category', 'hisab-financial-tracker'); ?></option>
                </select>
            </div>
        </div>
        
        <div class="hisab-form-row">
            <div class="hisab-form-group">
                <label for="transaction-owner"><?php _e('Owner', 'hisab-financial-tracker'); ?></label>
                <select id="transaction-owner" name="owner_id">
                    <option value=""><?php _e('Select Owner (Optional)', 'hisab-financial-tracker'); ?></option>
                </select>
            </div>
        </div>
        
        <div class="hisab-form-row">
            <div class="hisab-form-group">
                <label for="date-calendar-type"><?php _e('Calendar Type', 'hisab-financial-tracker'); ?></label>
                <select id="date-calendar-type" name="calendar_type">
                    <?php
                    $default_calendar = get_option('hisab_default_calendar', 'ad');
                    ?>
                    <option value="ad" <?php selected($default_calendar, 'ad'); ?>><?php _e('AD (Gregorian)', 'hisab-financial-tracker'); ?></option>
                    <option value="bs" <?php selected($default_calendar, 'bs'); ?>><?php _e('BS (Bikram Sambat)', 'hisab-financial-tracker'); ?></option>
                </select>
            </div>
        </div>
        
        <div class="hisab-form-row" id="ad-date-row">
            <div class="hisab-form-group">
                <label for="transaction-date"><?php _e('Transaction Date (AD)', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                <input type="date" id="transaction-date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
        </div>
        
        <div class="hisab-form-row" id="bs-date-row" style="display: none;">
            <div class="hisab-form-group">
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
        
        // Clear previous messages
        messagesDiv.empty();
        
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
            const formData = $('#hisab-transaction-form').serialize();
        
            $.ajax({
                url: hisab_ajax.ajax_url,
                type: 'POST',
                data: formData + '&action=hisab_save_transaction',
                success: function(response) {
                    messagesDiv.empty();
                    
                    if (response.success) {
                        messagesDiv.html('<div class="notice notice-success"><p>' + response.message + '</p></div>');
                        $('#hisab-transaction-form')[0].reset();
                        $('#transaction-category').empty().append('<option value=""><?php _e('Select Category', 'hisab-financial-tracker'); ?></option>');
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
});
</script>
