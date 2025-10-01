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
});
