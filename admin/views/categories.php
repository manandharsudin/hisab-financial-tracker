<?php
/**
 * Categories view template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Category Management', 'hisab-financial-tracker'); ?></h1>
    
    <!-- Add Category Form -->
    <div class="hisab-category-form-section">
        <h2><?php _e('Add New Category', 'hisab-financial-tracker'); ?></h2>
        <form id="hisab-category-form" class="hisab-form">
            <?php wp_nonce_field('hisab_transaction', 'hisab_nonce'); ?>
            <input type="hidden" id="category-id" name="id" value="">
            
            <div class="hisab-form-row">
                <div class="hisab-form-group">
                    <label for="category-name"><?php _e('Category Name', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                    <input type="text" id="category-name" name="name" required>
                </div>
                
                <div class="hisab-form-group">
                    <label for="category-type"><?php _e('Category Type', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                    <select id="category-type" name="type" required>
                        <option value=""><?php _e('Select Type', 'hisab-financial-tracker'); ?></option>
                        <option value="income"><?php _e('Income', 'hisab-financial-tracker'); ?></option>
                        <option value="expense"><?php _e('Expense', 'hisab-financial-tracker'); ?></option>
                    </select>
                </div>
                
                <div class="hisab-form-group">
                    <label for="category-color"><?php _e('Color', 'hisab-financial-tracker'); ?></label>
                    <input type="color" id="category-color" name="color" value="#007cba">
                </div>
            </div>
            
            <div class="hisab-form-actions">
                <button type="submit" class="button button-primary" id="save-category-btn">
                    <?php _e('Save Category', 'hisab-financial-tracker'); ?>
                </button>
                <button type="button" class="button" id="cancel-edit-btn" style="display: none;">
                    <?php _e('Cancel Edit', 'hisab-financial-tracker'); ?>
                </button>
            </div>
        </form>
        
        <div id="hisab-category-messages"></div>
    </div>
    
    <!-- Categories List -->
    <div class="hisab-categories-list">
        <h2><?php _e('Income Categories', 'hisab-financial-tracker'); ?></h2>
        <div class="hisab-categories-table">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Name', 'hisab-financial-tracker'); ?></th>
                        <th><?php _e('Color', 'hisab-financial-tracker'); ?></th>
                        <th><?php _e('Actions', 'hisab-financial-tracker'); ?></th>
                    </tr>
                </thead>
                <tbody id="income-categories-list">
                    <?php if (empty($income_categories)): ?>
                        <tr>
                            <td colspan="3" class="hisab-no-data">
                                <?php _e('No income categories found.', 'hisab-financial-tracker'); ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($income_categories as $category): ?>
                            <tr data-category-id="<?php echo $category->id; ?>">
                                <td>
                                    <span class="hisab-category-name"><?php echo esc_html($category->name); ?></span>
                                </td>
                                <td>
                                    <span class="hisab-category-color" style="background-color: <?php echo $category->color; ?>; color: white; padding: 4px 8px; border-radius: 3px;">
                                        <?php echo $category->color; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="button button-small hisab-edit-category" data-id="<?php echo $category->id; ?>">
                                        <?php _e('Edit', 'hisab-financial-tracker'); ?>
                                    </button>
                                    <button class="button button-small hisab-delete-category" data-id="<?php echo $category->id; ?>">
                                        <?php _e('Delete', 'hisab-financial-tracker'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <h2><?php _e('Expense Categories', 'hisab-financial-tracker'); ?></h2>
        <div class="hisab-categories-table">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Name', 'hisab-financial-tracker'); ?></th>
                        <th><?php _e('Color', 'hisab-financial-tracker'); ?></th>
                        <th><?php _e('Actions', 'hisab-financial-tracker'); ?></th>
                    </tr>
                </thead>
                <tbody id="expense-categories-list">
                    <?php if (empty($expense_categories)): ?>
                        <tr>
                            <td colspan="3" class="hisab-no-data">
                                <?php _e('No expense categories found.', 'hisab-financial-tracker'); ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($expense_categories as $category): ?>
                            <tr data-category-id="<?php echo $category->id; ?>">
                                <td>
                                    <span class="hisab-category-name"><?php echo esc_html($category->name); ?></span>
                                </td>
                                <td>
                                    <span class="hisab-category-color" style="background-color: <?php echo $category->color; ?>; color: white; padding: 4px 8px; border-radius: 3px;">
                                        <?php echo $category->color; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="button button-small hisab-edit-category" data-id="<?php echo $category->id; ?>">
                                        <?php _e('Edit', 'hisab-financial-tracker'); ?>
                                    </button>
                                    <button class="button button-small hisab-delete-category" data-id="<?php echo $category->id; ?>">
                                        <?php _e('Delete', 'hisab-financial-tracker'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Form submission
    $('#hisab-category-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const messagesDiv = $('#hisab-category-messages');
        const saveBtn = $('#save-category-btn');
        const cancelBtn = $('#cancel-edit-btn');
        
        messagesDiv.empty();
        saveBtn.prop('disabled', true).text('<?php _e('Saving...', 'hisab-financial-tracker'); ?>');
        
        $.ajax({
            url: hisab_ajax.ajax_url,
            type: 'POST',
            data: formData + '&action=hisab_save_category',
            success: function(response) {
                messagesDiv.empty();
                saveBtn.prop('disabled', false);
                
                if (response.success) {
                    messagesDiv.html('<div class="notice notice-success"><p>' + response.message + '</p></div>');
                    $('#hisab-category-form')[0].reset();
                    $('#category-id').val('');
                    saveBtn.text('<?php _e('Save Category', 'hisab-financial-tracker'); ?>');
                    cancelBtn.hide();
                    loadCategories();
                } else {
                    messagesDiv.html('<div class="notice notice-error"><p>' + response.message + '</p></div>');
                    saveBtn.text('<?php _e('Save Category', 'hisab-financial-tracker'); ?>');
                }
            },
            error: function() {
                messagesDiv.empty();
                messagesDiv.html('<div class="notice notice-error"><p><?php _e('An error occurred while saving the category.', 'hisab-financial-tracker'); ?></p></div>');
                saveBtn.prop('disabled', false).text('<?php _e('Save Category', 'hisab-financial-tracker'); ?>');
            }
        });
    });
    
    // Edit category
    $(document).on('click', '.hisab-edit-category', function() {
        const categoryId = $(this).data('id');
        
        $.ajax({
            url: hisab_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'hisab_get_category',
                id: categoryId,
                hisab_nonce: hisab_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    const category = response.data;
                    $('#category-id').val(category.id);
                    $('#category-name').val(category.name);
                    $('#category-type').val(category.type);
                    $('#category-color').val(category.color);
                    $('#save-category-btn').text('<?php _e('Update Category', 'hisab-financial-tracker'); ?>');
                    $('#cancel-edit-btn').show();
                    $('html, body').animate({
                        scrollTop: $('#hisab-category-form').offset().top - 100
                    }, 500);
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    });
    
    // Cancel edit
    $('#cancel-edit-btn').on('click', function() {
        $('#hisab-category-form')[0].reset();
        $('#category-id').val('');
        $('#save-category-btn').text('<?php _e('Save Category', 'hisab-financial-tracker'); ?>');
        $(this).hide();
    });
    
    // Delete category
    $(document).on('click', '.hisab-delete-category', function() {
        if (confirm('<?php _e('Are you sure you want to delete this category?', 'hisab-financial-tracker'); ?>')) {
            const categoryId = $(this).data('id');
            
            $.ajax({
                url: hisab_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'hisab_delete_category',
                    id: categoryId,
                    hisab_nonce: hisab_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        loadCategories();
                        alert(response.message);
                    } else {
                        alert('Error: ' + response.message);
                    }
                }
            });
        }
    });
    
    // Load categories (refresh the page)
    function loadCategories() {
        location.reload();
    }
});
</script>
