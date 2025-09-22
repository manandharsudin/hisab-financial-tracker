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
                <label for="transaction-date"><?php _e('Transaction Date', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                <input type="date" id="transaction-date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required>
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
    
    // Update categories based on transaction type
    $('#transaction-type').on('change', function() {
        const type = $(this).val();
        const categorySelect = $('#transaction-category');
        
        categorySelect.empty();
        categorySelect.append('<option value=""><?php _e('Select Category', 'hisab-financial-tracker'); ?></option>');
        
        if (type === 'income') {
            incomeCategories.forEach(function(category) {
                categorySelect.append(`<option value="${category.id}">${category.name}</option>`);
            });
        } else if (type === 'expense') {
            expenseCategories.forEach(function(category) {
                categorySelect.append(`<option value="${category.id}">${category.name}</option>`);
            });
        }
    });
    
    // Form submission
    $('#hisab-transaction-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const messagesDiv = $('#hisab-form-messages');
        
        // Clear previous messages
        messagesDiv.empty();
        
        // Show loading
        messagesDiv.html('<div class="notice notice-info"><p><?php _e('Saving transaction...', 'hisab-financial-tracker'); ?></p></div>');
        
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
                } else {
                    messagesDiv.html('<div class="notice notice-error"><p>' + response.message + '</p></div>');
                }
            },
            error: function() {
                messagesDiv.empty();
                messagesDiv.html('<div class="notice notice-error"><p><?php _e('An error occurred while saving the transaction.', 'hisab-financial-tracker'); ?></p></div>');
            }
        });
    });
});
</script>
