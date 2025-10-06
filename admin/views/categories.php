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
            <?php wp_nonce_field('hisab_transaction', 'nonce'); ?>
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
    
    <!-- Default Categories Section -->
    <div class="hisab-default-categories-section">
        <h2><?php _e('Default Categories', 'hisab-financial-tracker'); ?></h2>
        <p><?php _e('Add pre-defined default categories for income and expenses.', 'hisab-financial-tracker'); ?></p>
        <button type="button" class="button button-secondary" id="insert-default-categories-btn">
            <?php _e('Insert Default Categories', 'hisab-financial-tracker'); ?>
        </button>
        <div id="hisab-default-categories-messages"></div>
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

