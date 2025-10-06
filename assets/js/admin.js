/**
 * Hisab Financial Tracker - Admin JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Initialize tooltips and other admin features
    initAdminFeatures();
    
    function initAdminFeatures() {
        // Add loading states to forms
        $('.hisab-form').on('submit', function() {
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.text();
            submitBtn.prop('disabled', true).text('Saving...');
            
            // Re-enable button after 3 seconds as fallback
            setTimeout(function() {
                submitBtn.prop('disabled', false).text(originalText);
            }, 3000);
        });
        
        // Auto-refresh dashboard data every 5 minutes
        if ($('#hisab-trend-chart').length) {
            setInterval(refreshDashboardData, 300000); // 5 minutes
        }
        
        // Initialize date pickers
        $('input[type="date"]').each(function() {
            if (!$(this).val()) {
                $(this).val(getCurrentDate());
            }
        });
        
        // Add confirmation dialogs for destructive actions
        $('.hisab-delete-transaction').on('click', function(e) {
            if (!confirm('Are you sure you want to delete this transaction? This action cannot be undone.')) {
                e.preventDefault();
                return false;
            }
        });
    }
    
    function refreshDashboardData() {
        // Refresh dashboard data without page reload
        $.ajax({
            url: hisab_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'hisab_get_dashboard_data',
                nonce: hisab_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateDashboardCards(response.data);
                }
            }
        });
    }
    
    function updateDashboardCards(data) {
        // Update summary cards with new data
        $('.hisab-summary-item.income .hisab-amount').text(formatCurrency(data.income));
        $('.hisab-summary-item.expense .hisab-amount').text(formatCurrency(data.expense));
        $('.hisab-summary-item.net .hisab-amount').text(formatCurrency(data.net));
        
        // Update net amount color
        const netElement = $('.hisab-summary-item.net .hisab-amount');
        netElement.removeClass('positive negative');
        netElement.addClass(data.net >= 0 ? 'positive' : 'negative');
    }
    
    function formatCurrency(amount) {
        return parseFloat(amount).toFixed(2);
    }
    
    function getCurrentDate() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    // Chart utility functions
    window.hisabChartUtils = {
        createLineChart: function(canvasId, data, options = {}) {
            const ctx = document.getElementById(canvasId).getContext('2d');
            const defaultOptions = {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            };
            
            return new Chart(ctx, {
                type: 'line',
                data: data,
                options: Object.assign(defaultOptions, options)
            });
        },
        
        createBarChart: function(canvasId, data, options = {}) {
            const ctx = document.getElementById(canvasId).getContext('2d');
            const defaultOptions = {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            };
            
            return new Chart(ctx, {
                type: 'bar',
                data: data,
                options: Object.assign(defaultOptions, options)
            });
        },
        
        createDoughnutChart: function(canvasId, data, options = {}) {
            const ctx = document.getElementById(canvasId).getContext('2d');
            const defaultOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            };
            
            return new Chart(ctx, {
                type: 'doughnut',
                data: data,
                options: Object.assign(defaultOptions, options)
            });
        }
    };
    
    // Export data functionality
    $('.hisab-export-btn').on('click', function(e) {
        e.preventDefault();
        const format = $(this).data('format');
        const type = $(this).data('type');
        
        exportData(format, type);
    });
    
    function exportData(format, type) {
        $.ajax({
            url: hisab_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'hisab_export_data',
                format: format,
                type: type,
                nonce: hisab_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Create download link
                    const link = document.createElement('a');
                    link.href = response.data.url;
                    link.download = response.data.filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                } else {
                    alert('Export failed: ' + response.message);
                }
            }
        });
    }
    
    // Print functionality
    $('.hisab-print-btn').on('click', function(e) {
        e.preventDefault();
        window.print();
    });
    
    // Search and filter functionality
    $('.hisab-search-input').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('.hisab-table tbody tr').each(function() {
            const rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.includes(searchTerm));
        });
    });
    
    // Date range picker functionality
    $('.hisab-date-range').on('change', function() {
        const startDate = $('#start-date').val();
        const endDate = $('#end-date').val();
        
        if (startDate && endDate) {
            filterTransactionsByDate(startDate, endDate);
        }
    });
    
    function filterTransactionsByDate(startDate, endDate) {
        $.ajax({
            url: hisab_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'hisab_filter_transactions',
                start_date: startDate,
                end_date: endDate,
                nonce: hisab_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateTransactionsTable(response.data);
                }
            }
        });
    }
    
    function updateTransactionsTable(transactions) {
        const tbody = $('.hisab-table tbody');
        tbody.empty();
        
        if (transactions.length === 0) {
            tbody.append('<tr><td colspan="6" class="hisab-no-data">No transactions found for the selected date range.</td></tr>');
            return;
        }
        
        transactions.forEach(function(transaction) {
            const row = createTransactionRow(transaction);
            tbody.append(row);
        });
    }
    
    function createTransactionRow(transaction) {
        const date = new Date(transaction.transaction_date).toLocaleDateString();
        const typeClass = transaction.type;
        const typeBadge = `<span class="hisab-type-badge ${typeClass}">${transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1)}</span>`;
        const categoryBadge = transaction.category_name ? 
            `<span class="hisab-category-badge" style="background-color: ${transaction.category_color}">${transaction.category_name}</span>` :
            '<span class="hisab-category-badge">Uncategorized</span>';
        const amountClass = transaction.type;
        const amount = parseFloat(transaction.amount).toFixed(2);
        
        return `
            <tr>
                <td>${date}</td>
                <td>${typeBadge}</td>
                <td>${transaction.description}</td>
                <td>${categoryBadge}</td>
                <td class="hisab-amount ${amountClass}">${amount}</td>
                <td>
                    <button class="button button-small hisab-delete-transaction" data-id="${transaction.id}">
                        Delete
                    </button>
                </td>
            </tr>
        `;
    }
    
    // Default Categories functionality
    $('#insert-default-categories-btn').on('click', function() {
        const button = $(this);
        const originalText = button.text();
        
        if (!confirm('Are you sure you want to insert default categories? This will add pre-defined income and expense categories.')) {
            return;
        }
        
        button.prop('disabled', true).text('Inserting...');
        
        $.ajax({
            url: hisab_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'hisab_insert_default_categories',
                nonce: hisab_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showMessage('#hisab-default-categories-messages', 'success', response.data.message);
                    // Refresh the page to show new categories
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showMessage('#hisab-default-categories-messages', 'error', response.data.message || 'Failed to insert default categories');
                }
            },
            error: function() {
                showMessage('#hisab-default-categories-messages', 'error', 'An error occurred while inserting default categories');
            },
            complete: function() {
                button.prop('disabled', false).text(originalText);
            }
        });
    });
    
    function showMessage(container, type, message) {
        const messageClass = type === 'success' ? 'notice-success' : 'notice-error';
        const messageHtml = `<div class="notice ${messageClass} is-dismissible"><p>${message}</p></div>`;
        $(container).html(messageHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $(container).find('.notice').fadeOut();
        }, 5000);
    }
    
    // Bank Transaction Form Functionality
    function initBankTransactionForm() {
        // Show/hide phone pay reference field based on transaction type
        function togglePhonePayField() {
            var transactionType = $('#transaction_type').val();
            if (transactionType === 'phone_pay') {
                $('#phone_pay_row').show();
                $('#phone_pay_reference').prop('required', true);
            } else {
                $('#phone_pay_row').hide();
                $('#phone_pay_reference').prop('required', false);
            }
        }
        
        // Initial call
        togglePhonePayField();
        
        // Bind to change event
        $('#transaction_type').on('change', togglePhonePayField);
        
        // Form validation
        $('form').on('submit', function(e) {
            var transactionType = $('#transaction_type').val();
            var amount = parseFloat($('#amount').val());
            var accountBalance = parseFloat($('#account-balance').data('balance') || 0);
            
            // Check for withdrawal/phone pay/transfer out with insufficient balance
            if (['withdrawal', 'phone_pay', 'transfer_out'].includes(transactionType) && amount > accountBalance) {
                e.preventDefault();
                alert(hisab_ajax.insufficient_balance);
                return false;
            }
            
            // Check for zero or negative amount
            if (amount <= 0) {
                e.preventDefault();
                alert(hisab_ajax.amount_required);
                return false;
            }
        });
        
        // Account switcher functionality
        $('#switch-account-btn').on('click', function() {
            var selectedAccountId = $('#account-switcher').val();
            if (selectedAccountId) {
                // Redirect to the same page with the selected account
                var currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('account', selectedAccountId);
                window.location.href = currentUrl.toString();
            }
        });
    }
    
    // Initialize bank transaction form if on the add bank transaction page
    if ($('#transaction_type').length && $('#amount').length) {
        initBankTransactionForm();
    }
    
    // Transaction Form Functionality
    function initTransactionForm() {
        // Populate owners dropdown
        function populateOwners() {
            const ownerSelect = $('#transaction-owner');
            ownerSelect.empty();
            ownerSelect.append('<option value="">' + hisab_ajax.select_owner_optional + '</option>');
            
            // Owners will be populated via data attributes
            const ownersData = $('#transaction-owner').data('owners');
            if (Array.isArray(ownersData)) {
                ownersData.forEach(function(owner) {
                    ownerSelect.append(`<option value="${owner.id}">${owner.name}</option>`);
                });
            }
        }
        
        // Update categories based on transaction type
        function updateCategories(type, preserveSelection = false) {
            const categorySelect = $('#transaction-category');
            const currentValue = preserveSelection ? categorySelect.val() : '';
            
            categorySelect.empty();
            categorySelect.append('<option value="">' + hisab_ajax.select_category_placeholder + '</option>');
            
            const incomeCategories = $('#transaction-category').data('income-categories') || [];
            const expenseCategories = $('#transaction-category').data('expense-categories') || [];
            
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
        
        // Initialize owners dropdown
        populateOwners();
        
        // Bind payment method change event
        $('#transaction-payment-method').on('change', toggleBankAccountFields);
        
        // Initialize bank account fields visibility
        toggleBankAccountFields();
        
        // Handle calendar type switching
        $('#date-calendar-type').on('change', function() {
            switchCalendarType($(this).val());
        });
        
        // Update categories based on transaction type
        $('#transaction-type').on('change', function() {
            const type = $(this).val();
            const isEditMode = $('#edit-transaction-id').val();
            updateCategories(type, isEditMode);
        });
        
        // WordPress Media Uploader for bill image
        let mediaUploader;
        
        // Load existing image if in edit mode
        function loadExistingImage() {
            const imageId = $('#transaction-bill-image-id').val();
            if (imageId) {
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
                title: hisab_ajax.select_bill_image,
                button: {
                    text: hisab_ajax.use_this_image
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
                messagesDiv.html('<div class="notice notice-error"><p>' + hisab_ajax.select_category + '</p></div>');
                return;
            }
            
            // Show loading
            messagesDiv.html('<div class="notice notice-info"><p>' + hisab_ajax.saving_transaction + '</p></div>');
            
            // If BS calendar is selected, convert to AD first
            if (calendarType === 'bs') {
                const bsYear = $('#bs-year').val();
                const bsMonth = $('#bs-month').val();
                const bsDay = $('#bs-day').val();
                
                if (!bsYear || !bsMonth || !bsDay) {
                    messagesDiv.html('<div class="notice notice-error"><p>' + hisab_ajax.select_bs_date + '</p></div>');
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
                            messagesDiv.html('<div class="notice notice-error"><p>' + hisab_ajax.date_conversion_failed + '</p></div>');
                        }
                    },
                    error: function() {
                        messagesDiv.html('<div class="notice notice-error"><p>' + hisab_ajax.date_conversion_failed + '</p></div>');
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
                            const message = isEdit ? hisab_ajax.transaction_updated : response.message;
                            messagesDiv.html('<div class="notice notice-success"><p>' + message + '</p></div>');
                            
                            // Show add details button if transaction was saved successfully and not in edit mode
                            if (response.data && response.data.transaction_id && !isEdit) {
                                messagesDiv.append('<div style="margin-top: 10px;"><button type="button" class="button button-secondary" id="add-transaction-details" data-transaction-id="' + response.data.transaction_id + '">' + hisab_ajax.add_itemized_details + '</button></div>');
                            }
                            
                            // Reset form only if not in edit mode
                            if (!isEdit) {
                                $('#hisab-transaction-form')[0].reset();
                                $('#transaction-category').empty().append('<option value="">' + hisab_ajax.select_category_placeholder + '</option>');
                                $('#bill-image-preview').empty();
                                $('#transaction-bill-image-id').val('');
                                $('#upload-bill-image').show();
                                $('#remove-bill-image').hide();
                                // Clear BS date fields
                                $('#bs-year').val('');
                                $('#bs-month').val('');
                                $('#bs-day').val('');
                                // Reset calendar type to default
                                const defaultCalendarType = $('#date-calendar-type').data('default') || 'ad';
                                switchCalendarType(defaultCalendarType);
                            }
                        } else {
                            messagesDiv.html('<div class="notice notice-error"><p>' + response.message + '</p></div>');
                        }
                    },
                    error: function() {
                        messagesDiv.empty();
                        messagesDiv.html('<div class="notice notice-error"><p>' + hisab_ajax.error_saving + '</p></div>');
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
                    <p><strong>Amount:</strong> ${hisab_ajax.currency}${parseFloat(currentTransactionData.amount).toFixed(2)}</p>
                    <p><strong>Date:</strong> ${formattedDate}</p>
                    <p><strong>Tax:</strong> ${hisab_ajax.currency}${parseFloat(currentTransactionData.transaction_tax || 0).toFixed(2)}</p>
                    <p><strong>Discount:</strong> ${hisab_ajax.currency}${parseFloat(currentTransactionData.transaction_discount || 0).toFixed(2)}</p>
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
                $('#details-messages').html('<div class="notice notice-error"><p>' + hisab_ajax.add_at_least_one_item + '</p></div>');
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
                    $('#details-messages').html('<div class="notice notice-error"><p>' + hisab_ajax.error_saving_details + '</p></div>');
                }
            });
        }
        
        // Handle Update Transaction Details button
        $(document).on('click', '#edit-transaction-details', function() {
            const transactionId = $(this).data('transaction-id');
            currentTransactionId = transactionId;
            openDetailsModal();
        });
        
        // Initialize edit mode if editing
        const editTransactionId = $('#edit-transaction-id').val();
        if (editTransactionId) {
            // Set the correct calendar type based on BS date data
            if ($('#bs-year').val() && $('#bs-month').val() && $('#bs-day').val()) {
                $('#date-calendar-type').val('bs');
                $('#ad-date-row').hide();
                $('#bs-date-row').show();
            } else {
                $('#date-calendar-type').val('ad');
                $('#ad-date-row').show();
                $('#bs-date-row').hide();
            }
            
            // Update categories based on transaction type and select the correct one
            const type = $('#transaction-type').val();
            if (type) {
                updateCategories(type);
                // Select the correct category after updating
                setTimeout(function() {
                    const categoryId = $('#transaction-category').data('selected-category');
                    if (categoryId) {
                        $('#transaction-category').val(categoryId);
                    }
                }, 100);
            }
            
            // Select the correct owner
            setTimeout(function() {
                const ownerId = $('#transaction-owner').data('selected-owner');
                if (ownerId) {
                    $('#transaction-owner').val(ownerId);
                }
            }, 100);
        }
        
        // Initialize with default calendar type
        const defaultCalendarType = $('#date-calendar-type').data('default') || 'ad';
        switchCalendarType(defaultCalendarType);
    }
    
    // Initialize transaction form if on the add transaction page
    if ($('#hisab-transaction-form').length) {
        initTransactionForm();
    }
    
    // Analytics Charts Functionality
    function initAnalyticsCharts() {
        // Yearly chart
        const yearlyCtx = document.getElementById('hisab-yearly-chart');
        if (yearlyCtx) {
            const yearlyData = $('#hisab-yearly-chart').data('yearly-data') || {};
            
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const incomeData = [];
            const expenseData = [];
            
            for (let i = 1; i <= 12; i++) {
                incomeData.push(yearlyData[i] ? yearlyData[i].income : 0);
                expenseData.push(yearlyData[i] ? yearlyData[i].expense : 0);
            }
            
            new Chart(yearlyCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [{
                        label: hisab_ajax.income,
                        data: incomeData,
                        backgroundColor: 'rgba(40, 167, 69, 0.8)',
                        borderColor: '#28a745',
                        borderWidth: 1
                    }, {
                        label: hisab_ajax.expenses,
                        data: expenseData,
                        backgroundColor: 'rgba(220, 53, 69, 0.8)',
                        borderColor: '#dc3545',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        // Income categories pie chart
        const incomeCtx = document.getElementById('hisab-income-categories');
        if (incomeCtx) {
            const incomeCategories = $('#hisab-income-categories').data('income-categories') || [];
            
            new Chart(incomeCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: incomeCategories.map(cat => cat.category_name),
                    datasets: [{
                        data: incomeCategories.map(cat => cat.total),
                        backgroundColor: incomeCategories.map(cat => cat.category_color),
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
        
        // Expense categories pie chart
        const expenseCtx = document.getElementById('hisab-expense-categories');
        if (expenseCtx) {
            const expenseCategories = $('#hisab-expense-categories').data('expense-categories') || [];
            
            new Chart(expenseCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: expenseCategories.map(cat => cat.category_name),
                    datasets: [{
                        data: expenseCategories.map(cat => cat.total),
                        backgroundColor: expenseCategories.map(cat => cat.category_color),
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    }
    
    // Initialize analytics charts if on the analytics page
    if ($('#hisab-yearly-chart').length || $('#hisab-income-categories').length || $('#hisab-expense-categories').length) {
        initAnalyticsCharts();
    }
    
    // Categories Management Functionality
    function initCategoriesManagement() {
        // Form submission
        $('#hisab-category-form').on('submit', function(e) {
            e.preventDefault();
            
            const formData = $(this).serialize();
            const messagesDiv = $('#hisab-category-messages');
            const saveBtn = $('#save-category-btn');
            const cancelBtn = $('#cancel-edit-btn');
            
            messagesDiv.empty();
            saveBtn.prop('disabled', true).text(hisab_ajax.saving);
            
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
                        saveBtn.text(hisab_ajax.save_category);
                        cancelBtn.hide();
                        loadCategories();
                    } else {
                        messagesDiv.html('<div class="notice notice-error"><p>' + response.message + '</p></div>');
                        saveBtn.text(hisab_ajax.save_category);
                    }
                },
                error: function() {
                    messagesDiv.empty();
                    messagesDiv.html('<div class="notice notice-error"><p>' + hisab_ajax.error_saving_category + '</p></div>');
                    saveBtn.prop('disabled', false).text(hisab_ajax.save_category);
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
                    nonce: hisab_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const category = response.data;
                        $('#category-id').val(category.id);
                        $('#category-name').val(category.name);
                        $('#category-type').val(category.type);
                        $('#category-color').val(category.color);
                        $('#save-category-btn').text(hisab_ajax.update_category);
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
            $('#save-category-btn').text(hisab_ajax.save_category);
            $(this).hide();
        });
        
        // Delete category
        $(document).on('click', '.hisab-delete-category', function() {
            if (confirm(hisab_ajax.confirm_delete_category)) {
                const categoryId = $(this).data('id');
                
                $.ajax({
                    url: hisab_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'hisab_delete_category',
                        id: categoryId,
                        nonce: hisab_ajax.nonce
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
    }
    
    // Initialize categories management if on the categories page
    if ($('#hisab-category-form').length) {
        initCategoriesManagement();
    }
    
    // Date Converter Functionality
    function initDateConverter() {
        // Load conversion history from localStorage
        loadConversionHistory();
        
        // AD to BS conversion
        $('#ad-to-bs-form').on('submit', function(e) {
            e.preventDefault();
            
            const adDate = $('#ad-date-input').val();
            
            if (!adDate) {
                alert(hisab_ajax.select_ad_date);
                return;
            }
            
            $.ajax({
                url: hisab_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'hisab_convert_ad_to_bs',
                    ad_date: adDate,
                    nonce: hisab_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const bsDate = response.data;
                        const bsMonthName = getBSMonthName(bsDate.month);
                        const result = `${bsMonthName} ${bsDate.day}, ${bsDate.year}`;
                        $('#ad-to-bs-result').html(`<div class="hisab-result-success"><strong>${result}</strong></div>`);
                        
                        // Add to history
                        addToHistory('AD', adDate, 'BS', result);
                    } else {
                        $('#ad-to-bs-result').html('<div class="hisab-result-error">' + hisab_ajax.conversion_failed + '</div>');
                    }
                },
                error: function() {
                    $('#ad-to-bs-result').html('<div class="hisab-result-error">' + hisab_ajax.error_converting_date + '</div>');
                }
            });
        });
        
        // BS to AD conversion
        $('#bs-to-ad-form').on('submit', function(e) {
            e.preventDefault();
            const bsYear = $('#bs-year-input').val();
            const bsMonth = $('#bs-month-input').val();
            const bsDay = $('#bs-day-input').val();
            
            if (!bsYear || !bsMonth || !bsDay) {
                alert(hisab_ajax.select_bs_date_components);
                return;
            }
            
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
                        const adDate = response.data.ad_date;
                        const formattedDate = new Date(adDate).toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });
                        $('#bs-to-ad-result').html(`<div class="hisab-result-success"><strong>${formattedDate}</strong></div>`);
                        
                        // Add to history
                        const bsMonthName = getBSMonthName(bsMonth);
                        const bsDateString = `${bsMonthName} ${bsDay}, ${bsYear}`;
                        addToHistory('BS', bsDateString, 'AD', formattedDate);
                    } else {
                        $('#bs-to-ad-result').html('<div class="hisab-result-error">' + hisab_ajax.conversion_failed + '</div>');
                    }
                },
                error: function() {
                    $('#bs-to-ad-result').html('<div class="hisab-result-error">' + hisab_ajax.error_converting_date + '</div>');
                }
            });
        });
        
        // Clear history
        $('#clear-history').on('click', function() {
            if (confirm(hisab_ajax.confirm_clear_history)) {
                localStorage.removeItem('hisab_conversion_history');
                loadConversionHistory();
            }
        });
        
        // Helper function to get BS month name
        function getBSMonthName(month) {
            const monthNames = ['', 'Baisakh', 'Jestha', 'Ashadh', 'Shrawan', 'Bhadra', 'Ashwin', 'Kartik', 'Mangsir', 'Poush', 'Magh', 'Falgun', 'Chaitra'];
            return monthNames[month] || month;
        }
        
        // Add conversion to history
        function addToHistory(fromType, fromDate, toType, toDate) {
            let history = JSON.parse(localStorage.getItem('hisab_conversion_history') || '[]');
            
            history.unshift({
                fromType: fromType,
                fromDate: fromDate,
                toType: toType,
                toDate: toDate,
                timestamp: new Date().toLocaleString()
            });
            
            // Keep only last 10 conversions
            history = history.slice(0, 10);
            
            localStorage.setItem('hisab_conversion_history', JSON.stringify(history));
            loadConversionHistory();
        }
        
        // Load conversion history
        function loadConversionHistory() {
            const history = JSON.parse(localStorage.getItem('hisab_conversion_history') || '[]');
            const historyHtml = history.map(item => `
                <div class="hisab-history-item">
                    <span class="hisab-history-from">${item.fromType}: ${item.fromDate}</span>
                    <span class="hisab-history-arrow">→</span>
                    <span class="hisab-history-to">${item.toType}: ${item.toDate}</span>
                    <span class="hisab-history-time">${item.timestamp}</span>
                </div>
            `).join('');
            
            $('#conversion-history').html(historyHtml || '<p>' + hisab_ajax.no_conversion_history + '</p>');
        }
    }
    
    // Initialize date converter if on the date converter page
    if ($('#ad-to-bs-form').length || $('#bs-to-ad-form').length) {
        initDateConverter();
    }
    
    // Owners Management Functionality
    function initOwnersManagement() {
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
            saveBtn.prop('disabled', true).text(hisab_ajax.saving);
            
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
                        $('#save-owner-btn').text(hisab_ajax.save_owner);
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
                    messagesDiv.html('<div class="notice notice-error"><p>' + hisab_ajax.error_saving_owner + '</p></div>');
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
            $('#save-owner-btn').text(hisab_ajax.update_owner);
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
            $('#save-owner-btn').text(hisab_ajax.save_owner);
            $(this).hide();
        });
        
        // Delete owner
        $('.delete-owner').on('click', function() {
            const ownerId = $(this).data('owner-id');
            const ownerName = $(this).data('owner-name');
            
            if (!confirm(hisab_ajax.confirm_delete_owner + ' "' + ownerName + '"?')) {
                return;
            }
            
            const button = $(this);
            const originalText = button.text();
            
            button.prop('disabled', true).text(hisab_ajax.deleting);
            
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
                    alert(hisab_ajax.error_deleting_owner);
                },
                complete: function() {
                    button.prop('disabled', false).text(originalText);
                }
            });
        });
    }
    
    // Initialize owners management if on the owners page
    if ($('#hisab-owner-form').length) {
        initOwnersManagement();
    }
    
    // Projections Functionality
    function initProjections() {
        // Projections chart
        const projectionsCtx = document.getElementById('hisab-projections-chart');
        if (projectionsCtx) {
            const projections = JSON.parse(projectionsCtx.dataset.projections || '[]');
            
            const labels = projections.map(p => p.month_name);
            const incomeData = projections.map(p => p.projected_income);
            const expenseData = projections.map(p => p.projected_expense);
            const netData = projections.map(p => p.projected_net);
            
            new Chart(projectionsCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: hisab_ajax.projected_income,
                        data: incomeData,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4
                    }, {
                        label: hisab_ajax.projected_expenses,
                        data: expenseData,
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.4
                    }, {
                        label: hisab_ajax.projected_net,
                        data: netData,
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        // Savings calculator
        $('#hisab-savings-form').on('submit', function(e) {
            e.preventDefault();
            
            const targetAmount = parseFloat($('#target-amount').val());
            const monthsToTarget = parseInt($('#months-to-target').val());
            
            if (!targetAmount || !monthsToTarget) {
                alert(hisab_ajax.enter_target_and_months);
                return;
            }
            
            $.ajax({
                url: hisab_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'hisab_calculate_savings',
                    target_amount: targetAmount,
                    months_to_target: monthsToTarget,
                    nonce: hisab_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const result = response.data;
                        let html = '<div class="hisab-savings-analysis">';
                        
                        if (result.achievable) {
                            html += '<div class="notice notice-success">';
                            html += '<p><strong>' + hisab_ajax.goal_achievable + '</strong></p>';
                            html += '<p>' + hisab_ajax.required_monthly_savings + ' <strong>' + result.required_monthly_savings.toFixed(2) + '</strong></p>';
                            html += '<p>' + hisab_ajax.current_monthly_savings + ' <strong>' + result.current_monthly_savings.toFixed(2) + '</strong></p>';
                            html += '</div>';
                        } else {
                            html += '<div class="notice notice-error">';
                            html += '<p><strong>' + hisab_ajax.goal_difficult + '</strong></p>';
                            html += '<p>' + hisab_ajax.required_monthly_savings + ' <strong>' + result.required_monthly_savings.toFixed(2) + '</strong></p>';
                            html += '<p>' + hisab_ajax.current_monthly_savings + ' <strong>' + result.current_monthly_savings.toFixed(2) + '</strong></p>';
                            html += '<p>' + hisab_ajax.increase_savings_by + ' <strong>' + (result.required_monthly_savings - result.current_monthly_savings).toFixed(2) + '</strong></p>';
                            html += '</div>';
                        }
                        
                        html += '</div>';
                        $('#hisab-savings-result').html(html);
                    } else {
                        $('#hisab-savings-result').html('<div class="notice notice-error"><p>' + response.message + '</p></div>');
                    }
                },
                error: function() {
                    $('#hisab-savings-result').html('<div class="notice notice-error"><p>' + hisab_ajax.error_calculating_savings + '</p></div>');
                }
            });
        });
    }
    
    // Initialize projections if on the projections page
    if ($('#hisab-projections-chart').length || $('#hisab-savings-form').length) {
        initProjections();
    }
    
    // Transactions Management Functionality
    function initTransactionsManagement() {
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
            if (confirm(hisab_ajax.confirm_delete_transaction)) {
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
                        alert(hisab_ajax.error_loading_details + ': ' + response.message);
                    }
                },
                error: function() {
                    alert(hisab_ajax.error_loading_details);
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
                    <h4>${currentTransactionData.description || hisab_ajax.no_description}</h4>
                    <p><strong>${hisab_ajax.amount}:</strong> ${hisab_ajax.currency}${parseFloat(currentTransactionData.amount).toFixed(2)}</p>
                    <p><strong>${hisab_ajax.date}:</strong> ${formattedDate}</p>
                    <p><strong>${hisab_ajax.tax}:</strong> ${hisab_ajax.currency}${parseFloat(currentTransactionData.transaction_tax || 0).toFixed(2)}</p>
                    <p><strong>${hisab_ajax.discount}:</strong> ${hisab_ajax.currency}${parseFloat(currentTransactionData.transaction_discount || 0).toFixed(2)}</p>
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
                            $('#transaction-details-list').html('<p style="color: #666; text-align: center; padding: 20px;">' + hisab_ajax.no_itemized_details + '</p>');
                        }
                        updateSummary();
                    }
                },
                error: function() {
                    $('#transaction-details-list').html('<p style="color: #d63638; text-align: center; padding: 20px;">' + hisab_ajax.error_loading_details + '</p>');
                }
            });
        }
        
        function addDetailItem(item = {}) {
            const itemHtml = `
                <div class="detail-item">
                    <div class="detail-row">
                        <input type="text" class="detail-name" placeholder="${hisab_ajax.item_name}" value="${item.item_name || ''}" readonly>
                        <input type="number" class="detail-rate" placeholder="${hisab_ajax.rate}" step="0.01" min="0" value="${item.rate || ''}" readonly>
                        <input type="number" class="detail-quantity" placeholder="${hisab_ajax.quantity}" step="0.01" min="0" value="${item.quantity || ''}" readonly>
                        <input type="number" class="detail-total" placeholder="${hisab_ajax.total}" step="0.01" min="0" value="${item.item_total || ''}" readonly>
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
            
            $('#details-subtotal').text(hisab_ajax.currency + subtotal.toFixed(2));
            $('#details-total').text(hisab_ajax.currency + total.toFixed(2));
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
                        alert(hisab_ajax.error_deleting_transaction + ': ' + response.message);
                    }
                },
                error: function() {
                    alert(hisab_ajax.error_deleting_transaction);
                }
            });
        }
    }
    
    // Initialize transactions management if on the transactions page
    if ($('.hisab-view-details').length || $('.hisab-delete-transaction').length) {
        initTransactionsManagement();
    }
    
    // Transfer Between Accounts Functionality
    function initTransferBetweenAccounts() {
        // Update currency symbol and account balances when from account changes
        $('#from_account_id').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const currency = selectedOption.data('currency');
            const balance = selectedOption.data('balance');
            
            // Update currency symbol
            $('#currency-symbol').text(currency === 'NPR' ? '₹' : '$');
            
            // Update from account balance display
            if (balance !== undefined) {
                $('#from-account-balance').text(hisab_ajax.available_balance + ': ' + (currency === 'NPR' ? '₹' : '$') + parseFloat(balance).toFixed(2));
            } else {
                $('#from-account-balance').text('');
            }
            
            // Filter to account options to same currency
            filterToAccountOptions(currency);
            
            // Clear to account selection
            $('#to_account_id').val('');
            $('#to-account-balance').text('');
            
            // Validate amount
            validateAmount();
        });
        
        // Update to account balance when to account changes
        $('#to_account_id').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const balance = selectedOption.data('balance');
            const currency = selectedOption.data('currency');
            
            if (balance !== undefined) {
                $('#to-account-balance').text(hisab_ajax.current_balance + ': ' + (currency === 'NPR' ? '₹' : '$') + parseFloat(balance).toFixed(2));
            } else {
                $('#to-account-balance').text('');
            }
        });
        
        // Validate amount on input
        $('#amount').on('input', validateAmount);
        
        function filterToAccountOptions(currency) {
            const toAccountSelect = $('#to_account_id');
            const currentValue = toAccountSelect.val();
            
            toAccountSelect.find('option').each(function() {
                const option = $(this);
                const optionCurrency = option.data('currency');
                
                if (option.val() === '') {
                    option.show(); // Always show the placeholder
                } else if (optionCurrency === currency) {
                    option.show();
                } else {
                    option.hide();
                }
            });
            
            // Restore selection if it's still valid
            if (currentValue && toAccountSelect.find('option[value="' + currentValue + '"]').is(':visible')) {
                toAccountSelect.val(currentValue);
            } else {
                toAccountSelect.val('');
            }
        }
        
        function validateAmount() {
            const fromAccountId = $('#from_account_id').val();
            const amount = parseFloat($('#amount').val()) || 0;
            const validationDiv = $('#amount-validation');
            
            if (fromAccountId && amount > 0) {
                const selectedOption = $('#from_account_id').find('option:selected');
                const balance = selectedOption.data('balance');
                
                if (amount > balance) {
                    validationDiv.text(hisab_ajax.insufficient_balance_text + ' ' + (selectedOption.data('currency') === 'NPR' ? '₹' : '$') + parseFloat(balance).toFixed(2));
                    validationDiv.show();
                } else {
                    validationDiv.text('');
                    validationDiv.hide();
                }
            } else {
                validationDiv.text('');
                validationDiv.hide();
            }
        }
        
        // Form submission validation
        $('.hisab-form').has('#from_account_id').on('submit', function(e) {
            const fromAccountId = $('#from_account_id').val();
            const toAccountId = $('#to_account_id').val();
            const amount = parseFloat($('#amount').val()) || 0;
            
            if (fromAccountId === toAccountId) {
                e.preventDefault();
                alert(hisab_ajax.cannot_transfer_same_account);
                return false;
            }
            
            if (amount <= 0) {
                e.preventDefault();
                alert(hisab_ajax.amount_must_be_greater_than_zero);
                return false;
            }
            
            const selectedOption = $('#from_account_id').find('option:selected');
            const balance = selectedOption.data('balance');
            
            if (amount > balance) {
                e.preventDefault();
                alert(hisab_ajax.insufficient_balance_for_transfer);
                return false;
            }
        });
    }
    
    // Initialize transfer between accounts if on the transfer page
    if ($('#from_account_id').length && $('#to_account_id').length) {
        initTransferBetweenAccounts();
    }
});