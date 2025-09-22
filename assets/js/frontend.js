/**
 * Hisab Financial Tracker - Frontend JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Initialize frontend features
    initFrontendFeatures();
    
    function initFrontendFeatures() {
        // Add smooth scrolling for anchor links
        $('a[href^="#"]').on('click', function(e) {
            e.preventDefault();
            const target = $(this.getAttribute('href'));
            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 500);
            }
        });
        
        // Add loading states to interactive elements
        $('.hisab-interactive').on('click', function() {
            const $this = $(this);
            const originalText = $this.text();
            $this.prop('disabled', true).text('Loading...');
            
            // Re-enable after 2 seconds as fallback
            setTimeout(function() {
                $this.prop('disabled', false).text(originalText);
            }, 2000);
        });
        
        // Initialize responsive tables
        initResponsiveTables();
        
        // Initialize chart animations
        initChartAnimations();
    }
    
    function initResponsiveTables() {
        // Add responsive wrapper to tables
        $('.hisab-table').wrap('<div class="hisab-table-responsive"></div>');
        
        // Add horizontal scroll indicator
        $('.hisab-table-responsive').each(function() {
            const $wrapper = $(this);
            const $table = $wrapper.find('.hisab-table');
            
            if ($table[0].scrollWidth > $wrapper[0].clientWidth) {
                $wrapper.addClass('scrollable');
            }
        });
    }
    
    function initChartAnimations() {
        // Add animation delay to charts for better visual effect
        $('.hisab-chart-container').each(function(index) {
            $(this).css('animation-delay', (index * 0.2) + 's');
        });
    }
    
    // Chart utility functions for frontend
    window.hisabFrontendUtils = {
        createChart: function(canvasId, type, data, options = {}) {
            const ctx = document.getElementById(canvasId).getContext('2d');
            const defaultOptions = {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                }
            };
            
            const chartOptions = Object.assign(defaultOptions, options);
            
            return new Chart(ctx, {
                type: type,
                data: data,
                options: chartOptions
            });
        },
        
        formatCurrency: function(amount, currency = 'USD') {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: currency
            }).format(amount);
        },
        
        formatNumber: function(number, decimals = 2) {
            return parseFloat(number).toFixed(decimals);
        }
    };
    
    // Add CSS animations
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .hisab-card {
                animation: fadeInUp 0.6s ease-out;
            }
            
            .hisab-chart-container {
                animation: fadeIn 0.8s ease-out;
            }
            
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            @keyframes fadeIn {
                from {
                    opacity: 0;
                }
                to {
                    opacity: 1;
                }
            }
            
            .hisab-table-responsive.scrollable::after {
                content: '← Scroll to see more →';
                position: absolute;
                top: 50%;
                right: 10px;
                transform: translateY(-50%);
                background: rgba(0,0,0,0.7);
                color: white;
                padding: 5px 10px;
                border-radius: 3px;
                font-size: 12px;
                pointer-events: none;
                opacity: 0.8;
            }
            
            @media (max-width: 768px) {
                .hisab-table-responsive.scrollable::after {
                    display: none;
                }
            }
        `)
        .appendTo('head');
    
    // Add mobile-friendly interactions
    if (window.innerWidth <= 768) {
        // Add touch-friendly hover effects
        $('.hisab-card').on('touchstart', function() {
            $(this).addClass('touched');
        }).on('touchend', function() {
            setTimeout(() => {
                $(this).removeClass('touched');
            }, 150);
        });
        
        // Add CSS for touch effects
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                .hisab-card.touched {
                    transform: scale(0.98);
                    transition: transform 0.1s ease;
                }
            `)
            .appendTo('head');
    }
    
    // Add print styles
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            @media print {
                .hisab-card {
                    break-inside: avoid;
                    box-shadow: none;
                    border: 1px solid #000;
                }
                
                .hisab-table {
                    font-size: 12px;
                }
                
                .hisab-chart-container {
                    break-inside: avoid;
                }
            }
        `)
        .appendTo('head');
    
    // Add accessibility improvements
    $('.hisab-card').attr('role', 'region');
    $('.hisab-table').attr('role', 'table');
    $('.hisab-chart-container').attr('role', 'img');
    
    // Add keyboard navigation support
    $('.hisab-interactive').on('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            $(this).click();
        }
    });
    
    // Add focus management
    $('.hisab-card').on('focus', function() {
        $(this).addClass('focused');
    }).on('blur', function() {
        $(this).removeClass('focused');
    });
    
    // Add CSS for focus styles
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .hisab-card.focused {
                outline: 2px solid #007cba;
                outline-offset: 2px;
            }
        `)
        .appendTo('head');
});
