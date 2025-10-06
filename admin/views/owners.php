<?php
/**
 * Owners management view template
 */

if (!defined('ABSPATH')) {
    exit;
}

$owners = $this->database->get_owners();
?>

<div class="wrap">
    <h1><?php _e('Manage Owners', 'hisab-financial-tracker'); ?></h1>
    
    <!-- Add Owner Form -->
    <div class="hisab-add-owner-section">
        <h2><?php _e('Add New Owner', 'hisab-financial-tracker'); ?></h2>
        <form id="hisab-owner-form" class="hisab-form">
            <?php wp_nonce_field('hisab_transaction', 'nonce'); ?>
            <input type="hidden" id="owner-id" name="id" value="">
            
            <div class="hisab-form-row">
                <div class="hisab-form-group">
                    <label for="owner-name"><?php _e('Owner Name', 'hisab-financial-tracker'); ?> <span class="required">*</span></label>
                    <input type="text" id="owner-name" name="name" required>
                </div>
                
                <div class="hisab-form-group">
                    <label for="owner-color"><?php _e('Color', 'hisab-financial-tracker'); ?></label>
                    <input type="color" id="owner-color" name="color" value="#007cba">
                </div>
            </div>
            
            <div class="hisab-form-actions">
                <button type="submit" class="button button-primary" id="save-owner-btn">
                    <?php _e('Save Owner', 'hisab-financial-tracker'); ?>
                </button>
                <button type="button" class="button" id="cancel-owner-btn" style="display: none;">
                    <?php _e('Cancel', 'hisab-financial-tracker'); ?>
                </button>
            </div>
        </form>
        
        <div id="hisab-owner-messages"></div>
    </div>
    
    <!-- Owners List -->
    <div class="hisab-owners-list">
        <h2><?php _e('Existing Owners', 'hisab-financial-tracker'); ?></h2>
        
        <?php if (empty($owners)): ?>
            <p><?php _e('No owners found. Add your first owner above.', 'hisab-financial-tracker'); ?></p>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Name', 'hisab-financial-tracker'); ?></th>
                        <th><?php _e('Color', 'hisab-financial-tracker'); ?></th>
                        <th><?php _e('Created', 'hisab-financial-tracker'); ?></th>
                        <th><?php _e('Actions', 'hisab-financial-tracker'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($owners as $owner): ?>
                        <tr data-owner-id="<?php echo $owner->id; ?>">
                            <td>
                                <span class="hisab-owner-badge" style="background-color: <?php echo esc_attr($owner->color); ?>">
                                    <?php echo esc_html($owner->name); ?>
                                </span>
                            </td>
                            <td>
                                <span class="hisab-color-preview" style="background-color: <?php echo esc_attr($owner->color); ?>"></span>
                                <?php echo esc_html($owner->color); ?>
                            </td>
                            <td><?php echo date(HISAB_DATE_FORMAT, strtotime($owner->created_at)); ?></td>
                            <td>
                                <button class="button button-small edit-owner" data-owner-id="<?php echo $owner->id; ?>" data-owner-name="<?php echo esc_attr($owner->name); ?>" data-owner-color="<?php echo esc_attr($owner->color); ?>">
                                    <?php _e('Edit', 'hisab-financial-tracker'); ?>
                                </button>
                                <button class="button button-small button-link-delete delete-owner" data-owner-id="<?php echo $owner->id; ?>" data-owner-name="<?php echo esc_attr($owner->name); ?>">
                                    <?php _e('Delete', 'hisab-financial-tracker'); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>