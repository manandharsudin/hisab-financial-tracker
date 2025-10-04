<?php
if (!defined('ABSPATH')) {
    exit;
}

$database = new HisabDatabase();

// Get current page and filters
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = 20;

$filters = array();
if (!empty($_GET['type'])) {
    $filters['type'] = sanitize_text_field($_GET['type']);
}
if (!empty($_GET['category_id'])) {
    $filters['category_id'] = intval($_GET['category_id']);
}
if (!empty($_GET['owner_id'])) {
    $filters['owner_id'] = intval($_GET['owner_id']);
}
if (!empty($_GET['date_from'])) {
    $filters['date_from'] = sanitize_text_field($_GET['date_from']);
}
if (!empty($_GET['date_to'])) {
    $filters['date_to'] = sanitize_text_field($_GET['date_to']);
}

// Get paginated transactions
$result = $database->get_transactions_paginated($current_page, $per_page, $filters);
$transactions = $result['transactions'];
$total_items = $result['total_items'];
$total_pages = $result['total_pages'];

// Get filter options
$categories = $database->get_categories();
$owners = $database->get_owners();
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('All Transactions', 'hisab-financial-tracker'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=hisab-add-transaction'); ?>" class="page-title-action"><?php _e('Add New', 'hisab-financial-tracker'); ?></a>
    <hr class="wp-header-end">

    <!-- Filters -->
    <div class="hisab-filters" style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccd0d4; border-radius: 4px;">
        <form method="get" action="">
            <input type="hidden" name="page" value="hisab-transactions">
            
            <div class="hisab-filter-row" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: end;">
                <div class="hisab-filter-group">
                    <label for="filter-type"><?php _e('Type:', 'hisab-financial-tracker'); ?></label>
                    <select id="filter-type" name="type" style="width: 120px;">
                        <option value=""><?php _e('All Types', 'hisab-financial-tracker'); ?></option>
                        <option value="income" <?php selected($filters['type'] ?? '', 'income'); ?>><?php _e('Income', 'hisab-financial-tracker'); ?></option>
                        <option value="expense" <?php selected($filters['type'] ?? '', 'expense'); ?>><?php _e('Expense', 'hisab-financial-tracker'); ?></option>
                    </select>
                </div>
                
                <div class="hisab-filter-group">
                    <label for="filter-category"><?php _e('Category:', 'hisab-financial-tracker'); ?></label>
                    <select id="filter-category" name="category_id" style="width: 150px;">
                        <option value=""><?php _e('All Categories', 'hisab-financial-tracker'); ?></option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category->id; ?>" <?php selected($filters['category_id'] ?? '', $category->id); ?>>
                                <?php echo esc_html($category->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="hisab-filter-group">
                    <label for="filter-owner"><?php _e('Owner:', 'hisab-financial-tracker'); ?></label>
                    <select id="filter-owner" name="owner_id" style="width: 150px;">
                        <option value=""><?php _e('All Owners', 'hisab-financial-tracker'); ?></option>
                        <?php foreach ($owners as $owner): ?>
                            <option value="<?php echo $owner->id; ?>" <?php selected($filters['owner_id'] ?? '', $owner->id); ?>>
                                <?php echo esc_html($owner->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="hisab-filter-group">
                    <label for="filter-date-from"><?php _e('From Date:', 'hisab-financial-tracker'); ?></label>
                    <input type="date" id="filter-date-from" name="date_from" value="<?php echo esc_attr($filters['date_from'] ?? ''); ?>" style="width: 150px;">
                </div>
                
                <div class="hisab-filter-group">
                    <label for="filter-date-to"><?php _e('To Date:', 'hisab-financial-tracker'); ?></label>
                    <input type="date" id="filter-date-to" name="date_to" value="<?php echo esc_attr($filters['date_to'] ?? ''); ?>" style="width: 150px;">
                </div>
                
                <div class="hisab-filter-group">
                    <button type="submit" class="button button-primary"><?php _e('Filter', 'hisab-financial-tracker'); ?></button>
                    <a href="<?php echo admin_url('admin.php?page=hisab-transactions'); ?>" class="button"><?php _e('Clear', 'hisab-financial-tracker'); ?></a>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Summary -->
    <div class="hisab-results-summary" style="margin: 20px 0;">
        <p>
            <?php 
            printf(
                __('Showing %d of %d transactions', 'hisab-financial-tracker'),
                count($transactions),
                $total_items
            );
            ?>
        </p>
    </div>

    <!-- Transactions Table -->
    <div class="hisab-transactions-table-container" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; overflow: hidden;">
        <?php if (empty($transactions)): ?>
            <div class="hisab-no-transactions" style="padding: 40px; text-align: center; color: #666;">
                <h3><?php _e('No transactions found', 'hisab-financial-tracker'); ?></h3>
                <p><?php _e('Try adjusting your filters or add a new transaction.', 'hisab-financial-tracker'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=hisab-add-transaction'); ?>" class="button button-primary"><?php _e('Add Transaction', 'hisab-financial-tracker'); ?></a>
            </div>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 60px;"><?php _e('ID', 'hisab-financial-tracker'); ?></th>
                        <th style="width: 100px;"><?php _e('Type', 'hisab-financial-tracker'); ?></th>
                        <th style="width: 120px;"><?php _e('Amount', 'hisab-financial-tracker'); ?></th>
                        <th><?php _e('Description', 'hisab-financial-tracker'); ?></th>
                        <th style="width: 120px;"><?php _e('Category', 'hisab-financial-tracker'); ?></th>
                        <th style="width: 100px;"><?php _e('Owner', 'hisab-financial-tracker'); ?></th>
                        <th style="width: 100px;"><?php _e('Date', 'hisab-financial-tracker'); ?></th>
                        <th style="width: 100px;"><?php _e('Payment', 'hisab-financial-tracker'); ?></th>
                        <th style="width: 80px;"><?php _e('Bill', 'hisab-financial-tracker'); ?></th>
                        <th style="width: 120px;"><?php _e('Actions', 'hisab-financial-tracker'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?php echo $transaction->id; ?></td>
                            <td>
                                <span class="hisab-type-badge hisab-type-<?php echo $transaction->type; ?>">
                                    <?php echo ucfirst($transaction->type); ?>
                                </span>
                            </td>
                            <td>
                                <strong style="color: <?php echo $transaction->type === 'income' ? '#00a32a' : '#d63638'; ?>;">
                                    <?php echo $transaction->type === 'income' ? '+' : '-'; ?>₹<?php echo number_format($transaction->amount, 2); ?>
                                </strong>
                            </td>
                            <td>
                                <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo esc_attr($transaction->description); ?>">
                                    <?php echo esc_html($transaction->description); ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($transaction->category_name): ?>
                                    <span class="hisab-category-badge"><?php echo esc_html($transaction->category_name); ?></span>
                                <?php else: ?>
                                    <span style="color: #999;">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($transaction->owner_name): ?>
                                    <span class="hisab-owner-badge"><?php echo esc_html($transaction->owner_name); ?></span>
                                <?php else: ?>
                                    <span style="color: #999;">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo date('M j, Y', strtotime($transaction->transaction_date)); ?>
                                <?php if ($transaction->bs_year && $transaction->bs_month && $transaction->bs_day): ?>
                                    <br><small style="color: #666;">BS: <?php echo $transaction->bs_year . '-' . sprintf('%02d', $transaction->bs_month) . '-' . sprintf('%02d', $transaction->bs_day); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($transaction->payment_method): ?>
                                    <span class="hisab-payment-badge"><?php echo esc_html(ucfirst(str_replace('_', ' ', $transaction->payment_method))); ?></span>
                                <?php else: ?>
                                    <span style="color: #999;">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($transaction->bill_image_url): ?>
                                    <button type="button" class="button button-small hisab-view-bill" data-image-url="<?php echo esc_url($transaction->bill_image_url); ?>" data-image-title="<?php echo esc_attr($transaction->bill_image_title); ?>">
                                        <?php _e('View', 'hisab-financial-tracker'); ?>
                                    </button>
                                <?php else: ?>
                                    <span style="color: #999;">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="hisab-transaction-actions">
                                    <a href="<?php echo admin_url('admin.php?page=hisab-add-transaction&edit=' . $transaction->id); ?>" class="button button-small">
                                        <?php _e('Edit', 'hisab-financial-tracker'); ?>
                                    </a>
                                    <button type="button" class="button button-small button-link-delete hisab-delete-transaction" data-transaction-id="<?php echo $transaction->id; ?>">
                                        <?php _e('Delete', 'hisab-financial-tracker'); ?>
                                    </button>
                                    <?php 
                                    // Check if transaction has details
                                    $database = new HisabDatabase();
                                    $transaction_details = $database->get_transaction_details($transaction->id);
                                    if (!empty($transaction_details)): 
                                    ?>
                                    <button type="button" class="button button-small hisab-view-details" data-transaction-id="<?php echo $transaction->id; ?>">
                                        <?php _e('Details', 'hisab-financial-tracker'); ?>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="hisab-pagination" style="margin: 20px 0; text-align: center;">
            <?php
            $base_url = admin_url('admin.php?page=hisab-transactions');
            $query_params = $_GET;
            unset($query_params['paged']);
            
            if (!empty($query_params)) {
                $base_url .= '&' . http_build_query($query_params);
            }
            
            // Previous page
            if ($current_page > 1):
                $prev_url = $base_url . '&paged=' . ($current_page - 1);
            ?>
                <a href="<?php echo $prev_url; ?>" class="button"><?php _e('← Previous', 'hisab-financial-tracker'); ?></a>
            <?php endif; ?>
            
            <!-- Page numbers -->
            <span class="hisab-pagination-info" style="margin: 0 20px; padding: 8px 16px; background: #f0f0f1; border-radius: 4px;">
                <?php printf(__('Page %d of %d', 'hisab-financial-tracker'), $current_page, $total_pages); ?>
            </span>
            
            <!-- Next page -->
            <?php if ($current_page < $total_pages):
                $next_url = $base_url . '&paged=' . ($current_page + 1);
            ?>
                <a href="<?php echo $next_url; ?>" class="button"><?php _e('Next →', 'hisab-financial-tracker'); ?></a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>


<!-- Transaction Details Modal -->
<div id="transaction-details-modal" class="hisab-modal" style="display: none;">
    <div class="hisab-modal-content">
        <div class="hisab-modal-header">
            <h3><?php _e('Transaction Details', 'hisab-financial-tracker'); ?></h3>
            <span class="hisab-modal-close">&times;</span>
        </div>
        <div class="hisab-modal-body">
            <div id="transaction-info"></div>
            <div id="transaction-details-container">
                <h4><?php _e('Itemized Details', 'hisab-financial-tracker'); ?></h4>
                <div id="transaction-details-list"></div>
                <div class="hisab-details-summary">
                    <div class="summary-row">
                        <span><?php _e('Subtotal:', 'hisab-financial-tracker'); ?></span>
                        <span id="details-subtotal">₹0.00</span>
                    </div>
                    <div class="summary-row">
                        <span><?php _e('Tax:', 'hisab-financial-tracker'); ?></span>
                        <span id="details-tax">₹0.00</span>
                    </div>
                    <div class="summary-row">
                        <span><?php _e('Discount:', 'hisab-financial-tracker'); ?></span>
                        <span id="details-discount">₹0.00</span>
                    </div>
                    <div class="summary-row total">
                        <span><?php _e('Total:', 'hisab-financial-tracker'); ?></span>
                        <span id="details-total">₹0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bill Image Modal -->
<div id="bill-image-modal" class="hisab-modal" style="display: none;">
    <div class="hisab-modal-content" style="max-width: 80%; max-height: 80%;">
        <div class="hisab-modal-header">
            <h3><?php _e('Bill Image', 'hisab-financial-tracker'); ?></h3>
            <span class="hisab-modal-close">&times;</span>
        </div>
        <div class="hisab-modal-body" style="text-align: center;">
            <img id="bill-image-preview" style="max-width: 100%; max-height: 70vh; object-fit: contain;">
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let currentTransactionData = null;
    
    
    // View transaction details
    $('.hisab-view-details').on('click', function() {
        const transactionId = $(this).data('transaction-id');
        loadTransactionDetails(transactionId);
    });
    
    // View bill image
    $('.hisab-view-bill').on('click', function() {
        const imageUrl = $(this).data('image-url');
        const imageTitle = $(this).data('image-title');
        $('#bill-image-preview').attr('src', imageUrl).attr('alt', imageTitle);
        $('#bill-image-modal').show();
    });
    
    // Delete transaction
    $('.hisab-delete-transaction').on('click', function() {
        if (confirm('<?php _e('Are you sure you want to delete this transaction?', 'hisab-financial-tracker'); ?>')) {
            const transactionId = $(this).data('transaction-id');
            deleteTransaction(transactionId);
        }
    });
    
    
    // Modal close
    $('.hisab-modal-close, .hisab-modal').on('click', function(e) {
        if (e.target === this) {
            $('.hisab-modal').hide();
        }
    });
    
    
    function loadTransactionDetails(transactionId) {
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
        
        $('#details-tax').text(parseFloat(currentTransactionData.transaction_tax || 0).toFixed(2));
        $('#details-discount').text(parseFloat(currentTransactionData.transaction_discount || 0).toFixed(2));
        updateSummary();
    }
    
    function loadExistingDetails() {
        if (!currentTransactionData) return;
        
        $.ajax({
            url: hisab_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'hisab_get_transaction_details',
                transaction_id: currentTransactionData.id,
                nonce: hisab_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#transaction-details-list').empty();
                    if (response.data && response.data.length > 0) {
                        response.data.forEach(function(item) {
                            addDetailItem(item);
                        });
                    } else {
                        $('#transaction-details-list').html('<p style="color: #666; text-align: center; padding: 20px;">No itemized details available</p>');
                    }
                    updateSummary();
                }
            },
            error: function() {
                $('#transaction-details-list').html('<p style="color: #d63638; text-align: center; padding: 20px;">Error loading details</p>');
            }
        });
    }
    
    function addDetailItem(item = {}) {
        const itemHtml = `
            <div class="detail-item">
                <div class="detail-row">
                    <input type="text" class="detail-name" placeholder="Item name" value="${item.item_name || ''}" readonly>
                    <input type="number" class="detail-rate" placeholder="Rate" step="0.01" min="0" value="${item.rate || ''}" readonly>
                    <input type="number" class="detail-quantity" placeholder="Qty" step="0.01" min="0" value="${item.quantity || ''}" readonly>
                    <input type="number" class="detail-total" placeholder="Total" step="0.01" min="0" value="${item.item_total || ''}" readonly>
                </div>
            </div>
        `;
        $('#transaction-details-list').append(itemHtml);
    }
    
    function updateSummary() {
        let subtotal = 0;
        $('.detail-total').each(function() {
            const total = parseFloat($(this).val()) || 0;
            subtotal += total;
        });
        
        const tax = parseFloat($('#details-tax').text()) || 0;
        const discount = parseFloat($('#details-discount').text()) || 0;
        const total = subtotal + tax - discount;
        
        $('#details-subtotal').text('₹' + subtotal.toFixed(2));
        $('#details-total').text('₹' + total.toFixed(2));
    }
    
    function deleteTransaction(transactionId) {
        $.ajax({
            url: hisab_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'hisab_delete_transaction',
                transaction_id: transactionId,
                nonce: hisab_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error deleting transaction: ' + response.message);
                }
            },
            error: function() {
                alert('Error deleting transaction');
            }
        });
    }
});
</script>
