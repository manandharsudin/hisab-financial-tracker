/**
 * Hisab Financial Tracker - Admin JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // ========================================
    // UTILITY FUNCTIONS
    // ========================================
    
    // Centralized AJAX handler
    function makeAjaxCall(action, data, successCallback, errorCallback) {
        const defaultData = {
            action: action,
            nonce: hisab_ajax.nonce
        };
        
        $.ajax({
            url: hisab_ajax.ajax_url,
            type: 'POST',
            data: Object.assign(defaultData, data),
            success: function(response) {
                if (response.success) {
                    if (successCallback) successCallback(response.data);
                } else {
                    if (errorCallback) errorCallback(response.message);
                    else showError(response.message);
                }
            },
            error: function() {
                if (errorCallback) errorCallback('Network error occurred');
                else showError('Network error occurred');
            }
        });
    }
    
    // Centralized message display
    function showMessage(container, type, message, autoHide = true) {
        const messageClass = type === 'success' ? 'notice-success' : 'notice-error';
        const messageHtml = `<div class="notice ${messageClass} is-dismissible"><p>${message}</p></div>`;
        
        if (typeof container === 'string') {
            $(container).html(messageHtml);
        } else {
            container.html(messageHtml);
        }
        
        if (autoHide) {
            setTimeout(function() {
                $(container).find('.notice').fadeOut();
            }, 5000);
        }
    }
    
    function showSuccess(message, container = null) {
        if (container) {
            showMessage(container, 'success', message);
        } else {
            alert(message);
        }
    }
    
    function showError(message, container = null) {
        if (container) {
            showMessage(container, 'error', message);
        } else {
            alert(message);
        }
    }
    
    function showConfirm(message, callback) {
        if (confirm(message)) {
            callback();
        }
    }
    
    // Centralized form validation
    function validateForm(form, rules) {
        let isValid = true;
        const errors = [];
        
        for (const rule of rules) {
            const field = $(rule.selector);
            const value = field.val();
            
            if (rule.required && (!value || value.trim() === '')) {
                errors.push(rule.message || `${rule.selector} is required`);
                isValid = false;
            }
            
            if (rule.min && parseFloat(value) < rule.min) {
                errors.push(rule.message || `${rule.selector} must be at least ${rule.min}`);
                isValid = false;
            }
            
            if (rule.max && parseFloat(value) > rule.max) {
                errors.push(rule.message || `${rule.selector} must be at most ${rule.max}`);
                isValid = false;
            }
        }
        
        if (!isValid && errors.length > 0) {
            showError(errors.join('\n'));
        }
        
        return isValid;
    }
    
    // Centralized loading state management
    function setLoadingState(button, isLoading, loadingText = 'Saving...') {
        if (isLoading) {
            button.data('original-text', button.text());
            button.prop('disabled', true).text(loadingText);
        } else {
            button.prop('disabled', false).text(button.data('original-text') || 'Save');
        }
    }
    
    // Centralized transaction details functions
    function createDetailItem(data = {}, isReadOnly = false) {
        const item = Object.assign({
            item_name: '',
            rate: 0,
            quantity: 1,
            item_total: 0
        }, data);
        
        const readonlyAttr = isReadOnly ? 'readonly' : '';
        const removeButton = isReadOnly ? '' : '<button type="button" class="remove-detail-item" title="Remove item">×</button>';
        
        const itemHtml = `
            <div class="detail-item">
                <div class="detail-row">
                    <input type="text" class="detail-name" placeholder="${hisab_ajax.item_name || 'Item name'}" value="${item.item_name}" ${readonlyAttr}>
                    <input type="number" class="detail-rate" placeholder="${hisab_ajax.rate || 'Rate'}" step="0.01" min="0" value="${item.rate}" ${readonlyAttr}>
                    <input type="number" class="detail-quantity" placeholder="${hisab_ajax.quantity || 'Qty'}" step="0.01" min="0" value="${item.quantity}" ${readonlyAttr}>
                    <input type="number" class="detail-total" placeholder="${hisab_ajax.total || 'Total'}" step="0.01" min="0" value="${item.item_total}" readonly>
                    ${removeButton}
                </div>
            </div>
        `;
        
        return itemHtml;
    }
    
    function calculateItemTotal($row) {
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
        const total = subtotal + tax - discount;
        
        const currency = hisab_ajax.currency || '$';
        $('#details-subtotal').text(currency + subtotal.toFixed(2));
        $('#details-total').text(currency + total.toFixed(2));
        
        // Check for mismatch with main transaction amount
        const mainAmount = parseFloat($('#transaction-amount').val()) || 0;
        const difference = Math.abs(total - mainAmount);
        
        if (difference > 0.01) {
            $('#details-total').addClass('mismatch');
        } else {
            $('#details-total').removeClass('mismatch');
        }
    }
    
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
        
        // Initialize date pickers - only for form inputs, not filter inputs
        $('input[type="date"]').not('[name="start_date"], [name="end_date"]').each(function() {
            if (!$(this).val()) {
                $(this).val(getCurrentDate());
            }
        });
        
    }
    
    function refreshDashboardData() {
        // Refresh dashboard data without page reload
        makeAjaxCall('hisab_get_dashboard_data', {}, function(data) {
            updateDashboardCards(data);
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
        
        // Get export type from select if available
        const exportType = $('#export-type').val() || type;
        
        exportData(format, exportType);
    });
    
    function exportData(format, type) {
        const $btn = $('.hisab-export-btn');
        const originalText = $btn.html();
        
        // Show loading state
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update"></span> Exporting...');
        
        console.log('Starting export with format:', format, 'type:', type);
        
        makeAjaxCall('hisab_export_data', {
            format: format,
            export_type: type
        }, function(data) {
            // makeAjaxCall passes response.data to success callback
            console.log('Export response data:', data);
            if (data) {
                try {
                    // Create and download JSON file
                    const dataStr = JSON.stringify(data, null, 2);
                    const dataBlob = new Blob([dataStr], {
                        type: 'application/json;charset=utf-8'
                    });
                    
                    const currentDate = new Date().toISOString().slice(0,10);
                    const fileName = 'hisab-export-' + type + '-' + currentDate + '.json';
                    
                    // Create download link
                    const link = document.createElement('a');
                    const url = URL.createObjectURL(dataBlob);
                    link.href = url;
                    link.download = fileName;
                    link.style.display = 'none';
                    
                    // Add to DOM, click, and remove
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    
                    // Clean up
                    setTimeout(function() {
                        URL.revokeObjectURL(url);
                    }, 1000);
                    
                    // Show success message
                    showImportExportResult('success', 'Data exported successfully!');
                } catch (e) {
                    console.error('Download error:', e);
                    showImportExportResult('error', 'Download failed: ' + e.message);
                }
            } else {
                showImportExportResult('error', 'Export failed: No data received');
            }
        }, function(message) {
            showImportExportResult('error', 'Export failed: ' + message);
        });
        
        // Reset button
        $btn.prop('disabled', false).html(originalText);
    }
    
    // Import functionality
    $('#hisab-import-form').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $btn = $form.find('.hisab-import-btn');
        const originalText = $btn.html();
        
        // Show loading state
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update"></span> Importing...');
        
        const formData = new FormData();
        formData.append('action', 'hisab_import_data');
        formData.append('nonce', hisab_ajax.nonce);
        formData.append('import_file', $('#import-file')[0].files[0]);
        
        // Add checkboxes
        $form.find('input[type="checkbox"]:checked').each(function() {
            formData.append($(this).attr('name'), $(this).val());
        });
        
        $.ajax({
            url: hisab_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showImportResults(response);
                } else {
                    showImportExportResult('error', 'Import failed: ' + response.message);
                }
            },
            error: function() {
                showImportExportResult('error', 'Import failed due to network error.');
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    function showImportExportResult(type, message) {
        const $results = $('#hisab-import-export-results');
        const $content = $('#hisab-results-content');
        
        $content.html('<div class="hisab-result-item ' + type + '">' + message + '</div>');
        $results.show();
    }
    
    function showImportResults(response) {
        const $results = $('#hisab-import-export-results');
        const $content = $('#hisab-results-content');
        
        let html = '<div class="hisab-result-item success">Import completed successfully!</div>';
        
        // Show statistics
        if (response.imported) {
            html += '<div class="hisab-result-stats">';
            for (const type in response.imported) {
                if (response.imported[type] > 0) {
                    html += '<div class="hisab-stat-card">';
                    html += '<div class="hisab-stat-number">' + response.imported[type] + '</div>';
                    html += '<div class="hisab-stat-label">' + type + ' imported</div>';
                    html += '</div>';
                }
            }
            html += '</div>';
        }
        
        // Show errors
        if (response.errors) {
            for (const type in response.errors) {
                if (response.errors[type].length > 0) {
                    html += '<div class="hisab-result-item warning">';
                    html += '<strong>' + type + ' errors:</strong><br>';
                    html += response.errors[type].join('<br>');
                    html += '</div>';
                }
            }
        }
        
        $content.html(html);
        $results.show();
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
        
        // Form validation - only for bank transaction forms
        $('form').has('#transaction_type').has('#amount').on('submit', function(e) {
            var $form = $(this);
            var transactionType = $('#transaction_type').val();
            var amount = parseFloat($('#amount').val());
            var accountBalance = parseFloat($('#account-balance').data('balance') || 0);
            
            // Check if this is an edit operation
            var isEdit = $form.data('is-edit') === 'true' || $form.data('is-edit') === true;
            var transactionId = $form.find('input[name="transaction_id"]').val();
            
            // Check for zero or negative amount first
            if (amount <= 0) {
                e.preventDefault();
                alert(hisab_ajax.amount_required);
                return false;
            }
            
            // Check for withdrawal/phone pay/transfer out with insufficient balance
            if (['withdrawal', 'phone_pay', 'transfer_out'].includes(transactionType)) {
                // For edit operations, use effective balance
                if (isEdit && transactionId) {
                    var effectiveBalance = parseFloat($form.data('effective-balance') || 0);
                    if (amount > effectiveBalance) {
                        e.preventDefault();
                        alert('Insufficient balance for this transaction. Available: ' + effectiveBalance.toFixed(2) + ', Required: ' + amount.toFixed(2));
                        return false;
                    }
                } else {
                    // For new transactions, use current balance
                    if (amount > accountBalance) {
                        e.preventDefault();
                        alert(hisab_ajax.insufficient_balance);
                        return false;
                    }
                }
            }
        });
    }
    
    // Initialize bank transaction form if on the add bank transaction page
    if ($('#transaction_type').length && $('#amount').length) {
        initBankTransactionForm();
    }
    
    // Bank Transactions Page Functionality
    function initBankTransactionsPage() {
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
    
    // Initialize bank transactions page if on the bank transactions page
    if ($('#account-switcher').length && $('#switch-account-btn').length) {
        initBankTransactionsPage();
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
        
        function convertDateOnSwitch(calendarType) {
            const adDateInput = $('#transaction-date');
            const bsYearSelect = $('#bs-year');
            const bsMonthSelect = $('#bs-month');
            const bsDaySelect = $('#bs-day');
            
            if (calendarType === 'bs') {
                // Converting from AD to BS
                const adDate = adDateInput.val();
                if (adDate) {
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
                                bsYearSelect.val(response.data.year);
                                bsMonthSelect.val(response.data.month);
                                bsDaySelect.val(response.data.day);
                            }
                        },
                        error: function() {
                            console.log('Date conversion failed');
                        }
                    });
                }
            } else {
                // Converting from BS to AD
                const bsYear = bsYearSelect.val();
                const bsMonth = bsMonthSelect.val();
                const bsDay = bsDaySelect.val();
                
                if (bsYear && bsMonth && bsDay) {
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
                                adDateInput.val(response.data.ad_date);
                            }
                        },
                        error: function() {
                            console.log('Date conversion failed');
                        }
                    });
                }
            }
        }
        
        // Initialize owners dropdown
        populateOwners();
        
        
        // Handle calendar type switching
        $('#date-calendar-type').on('change', function() {
            const calendarType = $(this).val();
            switchCalendarType(calendarType);
            
            // Convert dates when switching calendar types
            convertDateOnSwitch(calendarType);
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
        let currentAction = 'add'; // 'add' or 'update'
        
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
            // Set modal title based on action
            const modalTitle = currentAction === 'add' ? 
                'Add Transaction Details' : 'Update Transaction Details';
            $('#modal-title').text(modalTitle);
            
            // Load transaction data
            makeAjaxCall('hisab_get_transaction', {
                transaction_id: currentTransactionId
            }, function(data) {
                currentTransactionData = data;
                displayTransactionInfo();
                loadExistingDetails();
                $('#transaction-details-modal').show();
            });
        }
        
        function closeDetailsModal() {
            $('#transaction-details-modal').hide();
            currentTransactionId = null;
            currentTransactionData = null;
            currentAction = 'add';
            $('#details-items').empty();
            updateSummary();
        }
        
        function updateTransactionDetailsSection() {
            // Find the transaction details section
            const detailsSection = $('.hisab-edit-details-section');
            
            if (detailsSection.length > 0) {
                // Update the text and button
                detailsSection.find('p').text('This transaction has itemized details. You can view or update them.');
                
                // Change the button from "Add" to "Update"
                const button = detailsSection.find('button');
                button.removeClass('button-primary').addClass('button-secondary');
                button.attr('id', 'edit-transaction-details');
                button.text('Update Transaction Details');
            }
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
            makeAjaxCall('hisab_get_transaction_details', {
                transaction_id: currentTransactionId
            }, function(data) {
                if (data && data.length > 0) {
                    data.forEach(function(item) {
                        addDetailItem(item);
                    });
                } else {
                    addDetailItem(); // Add one empty item
                }
                updateSummary();
            }, function() {
                addDetailItem(); // Add one empty item on error
                updateSummary();
            });
        }
        
        function addDetailItem(data = null) {
            const itemHtml = createDetailItem(data || {}, false);
            $('#details-items').append(itemHtml);
            
            // Add event listeners for the new item
            const newItem = $('#details-items .detail-item').last();
            newItem.find('.detail-rate, .detail-quantity').on('input', function() {
                calculateItemTotal($(this).closest('.detail-item'));
            });
            newItem.find('.remove-detail-item').on('click', function() {
                $(this).closest('.detail-item').remove();
                updateSummary();
            });
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
            
            makeAjaxCall('hisab_save_transaction_details', {
                transaction_id: currentTransactionId,
                details: details
            }, function(data) {
                showMessage('#details-messages', 'success', 'Details saved successfully');
                
                // Update the transaction details section to show "Update" instead of "Add"
                setTimeout(function() {
                    updateTransactionDetailsSection();
                }, 500);
                
                setTimeout(function() {
                    closeDetailsModal();
                }, 1500);
            }, function(message) {
                showMessage('#details-messages', 'error', message || hisab_ajax.error_saving_details);
            });
        }
        
        // Handle Update Transaction Details button
        $(document).on('click', '#edit-transaction-details', function() {
            const transactionId = $(this).data('transaction-id');
            currentTransactionId = transactionId;
            currentAction = 'update';
            openDetailsModal();
        });
        
        // Handle Add Transaction Details button
        $(document).on('click', '#add-transaction-details', function() {
            const transactionId = $(this).data('transaction-id');
            currentTransactionId = transactionId;
            currentAction = 'add';
            openDetailsModal();
        });
        
        // Initialize with default calendar type first
        const defaultCalendarType = $('#date-calendar-type').data('default') || 'ad';
        $('#date-calendar-type').val(defaultCalendarType);
        switchCalendarType(defaultCalendarType);
        
        // Initialize edit mode if editing
        const editTransactionId = $('#edit-transaction-id').val();
        if (editTransactionId) {
            // Check if we have BS date data and respect default calendar type
            const hasBSDate = $('#bs-year').val() && $('#bs-month').val() && $('#bs-day').val();
            const hasADDate = $('#transaction-date').val();
            
            // If default is BS and we have BS data, use BS
            // If default is AD or we don't have BS data, use AD
            if (defaultCalendarType === 'bs' && hasBSDate) {
                $('#date-calendar-type').val('bs');
                switchCalendarType('bs');
            } else {
                $('#date-calendar-type').val('ad');
                switchCalendarType('ad');
                
                // If we have BS data but default is AD, convert BS to AD
                if (hasBSDate && !hasADDate) {
                    const bsYear = $('#bs-year').val();
                    const bsMonth = $('#bs-month').val();
                    const bsDay = $('#bs-day').val();
                    
                    if (bsYear && bsMonth && bsDay) {
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
                                    $('#transaction-date').val(response.data.ad_date);
                                }
                            }
                        });
                    }
                }
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
            showConfirm(hisab_ajax.confirm_delete_transaction, function() {
                const transactionId = $(this).data('transaction-id');
                deleteTransaction(transactionId);
            }.bind(this));
        });
        
        // Modal close
        $('.hisab-modal-close, .hisab-modal').on('click', function(e) {
            if (e.target === this) {
                $('.hisab-modal').hide();
            }
        });
        
        function loadTransactionDetails(transactionId) {
            makeAjaxCall('hisab_get_transaction', {
                transaction_id: transactionId
            }, function(data) {
                currentTransactionData = data;
                displayTransactionInfo();
                loadExistingDetails();
                $('#transaction-details-modal').show();
            }, function(message) {
                showError(hisab_ajax.error_loading_details + ': ' + message);
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
            
            makeAjaxCall('hisab_get_transaction_details', {
                transaction_id: currentTransactionData.id
            }, function(data) {
                $('#transaction-details-list').empty();
                if (data && data.length > 0) {
                    data.forEach(function(item) {
                        const itemHtml = createDetailItem(item, true);
                        $('#transaction-details-list').append(itemHtml);
                    });
                } else {
                    $('#transaction-details-list').html('<p style="color: #666; text-align: center; padding: 20px;">' + hisab_ajax.no_itemized_details + '</p>');
                }
                updateSummary();
            }, function() {
                $('#transaction-details-list').html('<p style="color: #d63638; text-align: center; padding: 20px;">' + hisab_ajax.error_loading_details + '</p>');
            });
        }
        
        
        function deleteTransaction(transactionId) {
            makeAjaxCall('hisab_delete_transaction', {
                transaction_id: transactionId
            }, function() {
                location.reload();
            }, function(message) {
                showError(hisab_ajax.error_deleting_transaction + ': ' + message);
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
    
    // Dashboard Charts Functionality
    function initDashboardCharts() {
        // Initialize trend chart
        const trendCtx = document.getElementById('hisab-trend-chart');
        if (trendCtx) {
            const incomeData = JSON.parse(trendCtx.dataset.incomeData || '[]');
            const expenseData = JSON.parse(trendCtx.dataset.expenseData || '[]');
            const labels = JSON.parse(trendCtx.dataset.labels || '[]');
            
            new Chart(trendCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: hisab_ajax.income || 'Income',
                        data: incomeData,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4
                    }, {
                        label: hisab_ajax.expenses || 'Expenses',
                        data: expenseData,
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
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
    }
    
    // Initialize dashboard charts if on the dashboard page
    if ($('#hisab-trend-chart').length) {
        initDashboardCharts();
    }
});