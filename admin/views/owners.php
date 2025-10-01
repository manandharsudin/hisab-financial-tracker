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
                            <td><?php echo date('M j, Y', strtotime($owner->created_at)); ?></td>
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

<style>
.hisab-owner-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    color: white;
    font-weight: 500;
    font-size: 12px;
}

.hisab-color-preview {
    display: inline-block;
    width: 20px;
    height: 20px;
    border-radius: 3px;
    margin-right: 8px;
    vertical-align: middle;
    border: 1px solid #ddd;
}

.hisab-add-owner-section {
    background: #fff;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
}

.hisab-owners-list {
    background: #fff;
    padding: 20px;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
}

.hisab-form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
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
.hisab-form-group input[type="color"] {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.hisab-form-group input[type="color"] {
    width: 60px;
    height: 40px;
    padding: 0;
    border: none;
    border-radius: 4px;
}

.hisab-form-actions {
    margin-top: 15px;
}

.required {
    color: #d63638;
}

@media (max-width: 768px) {
    .hisab-form-row {
        flex-direction: column;
        gap: 15px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Save owner
    $('#hisab-owner-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const messagesDiv = $('#hisab-owner-messages');
        const saveBtn = $('#save-owner-btn');
        const originalText = saveBtn.text();
        
        // Clear previous messages
        messagesDiv.empty();
        
        // Show loading
        saveBtn.prop('disabled', true).text('<?php _e('Saving...', 'hisab-financial-tracker'); ?>');
        
        $.ajax({
            url: hisab_ajax.ajax_url,
            type: 'POST',
            data: formData + '&action=hisab_save_owner',
            success: function(response) {
                messagesDiv.empty();
                
                if (response.success) {
                    messagesDiv.html('<div class="notice notice-success"><p>' + response.message + '</p></div>');
                    $('#hisab-owner-form')[0].reset();
                    $('#owner-id').val('');
                    $('#save-owner-btn').text('<?php _e('Save Owner', 'hisab-financial-tracker'); ?>');
                    $('#cancel-owner-btn').hide();
                    
                    // Reload page to show updated list
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    messagesDiv.html('<div class="notice notice-error"><p>' + response.message + '</p></div>');
                }
            },
            error: function() {
                messagesDiv.html('<div class="notice notice-error"><p><?php _e('An error occurred while saving the owner.', 'hisab-financial-tracker'); ?></p></div>');
            },
            complete: function() {
                saveBtn.prop('disabled', false).text(originalText);
            }
        });
    });
    
    // Edit owner
    $('.edit-owner').on('click', function() {
        const ownerId = $(this).data('owner-id');
        const ownerName = $(this).data('owner-name');
        const ownerColor = $(this).data('owner-color');
        
        $('#owner-id').val(ownerId);
        $('#owner-name').val(ownerName);
        $('#owner-color').val(ownerColor);
        $('#save-owner-btn').text('<?php _e('Update Owner', 'hisab-financial-tracker'); ?>');
        $('#cancel-owner-btn').show();
        
        // Scroll to form
        $('html, body').animate({
            scrollTop: $('.hisab-add-owner-section').offset().top - 100
        }, 500);
    });
    
    // Cancel edit
    $('#cancel-owner-btn').on('click', function() {
        $('#hisab-owner-form')[0].reset();
        $('#owner-id').val('');
        $('#save-owner-btn').text('<?php _e('Save Owner', 'hisab-financial-tracker'); ?>');
        $(this).hide();
    });
    
    // Delete owner
    $('.delete-owner').on('click', function() {
        const ownerId = $(this).data('owner-id');
        const ownerName = $(this).data('owner-name');
        
        if (!confirm('<?php _e('Are you sure you want to delete this owner?', 'hisab-financial-tracker'); ?> "' + ownerName + '"?')) {
            return;
        }
        
        const button = $(this);
        const originalText = button.text();
        
        button.prop('disabled', true).text('<?php _e('Deleting...', 'hisab-financial-tracker'); ?>');
        
        $.ajax({
            url: hisab_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'hisab_delete_owner',
                owner_id: ownerId,
                nonce: hisab_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('tr[data-owner-id="' + ownerId + '"]').fadeOut(300, function() {
                        $(this).remove();
                    });
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('<?php _e('An error occurred while deleting the owner.', 'hisab-financial-tracker'); ?>');
            },
            complete: function() {
                button.prop('disabled', false).text(originalText);
            }
        });
    });
});
</script>
