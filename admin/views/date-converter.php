<?php
/**
 * Date Converter view template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Date Converter', 'hisab-financial-tracker'); ?></h1>
        
    <div class="hisab-date-converter-container">
        <!-- Current Date Display -->
        <div class="hisab-current-date-section">
            <h2><?php _e('Current Date', 'hisab-financial-tracker'); ?></h2>
            <div class="hisab-current-date-display">
                <div class="current-ad-date">
                    <strong><?php _e('AD (Gregorian):', 'hisab-financial-tracker'); ?></strong>
                    <span><?php echo date('F j, Y'); ?></span>
                </div>
                <div class="current-bs-date">
                    <strong><?php _e('BS (Bikram Sambat):', 'hisab-financial-tracker'); ?></strong>
                    <span>
                        <?php 
                        $current_bs = HisabNepaliDate::get_current_bs_date();
                        if ($current_bs) {
                            $bs_month_name = HisabNepaliDate::get_bs_months($current_bs['month']);
                            echo $bs_month_name . ' ' . $current_bs['day'] . ', ' . $current_bs['year'];
                        } else {
                            echo 'Unable to convert current date';
                        }
                        ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="hisab-converter-row">
            <!-- AD to BS Converter -->
            <div class="hisab-converter-section">
                <h2><?php _e('AD to BS Converter', 'hisab-financial-tracker'); ?></h2>
                <form id="ad-to-bs-form" class="hisab-converter-form" action="#" method="post">
                    <div class="hisab-form-group">
                        <label for="ad-date-input"><?php _e('AD Date', 'hisab-financial-tracker'); ?></label>
                        <input type="date" id="ad-date-input" name="ad_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <button type="submit" class="button button-primary"><?php _e('Convert to BS', 'hisab-financial-tracker'); ?></button>
                </form>
                <div id="ad-to-bs-result" class="hisab-conversion-result"></div>
            </div>
            
            <!-- BS to AD Converter -->
            <div class="hisab-converter-section">
                <h2><?php _e('BS to AD Converter', 'hisab-financial-tracker'); ?></h2>
                <form id="bs-to-ad-form" class="hisab-converter-form" action="#" method="post">
                    <div class="hisab-form-group">
                        <label><?php _e('BS Date', 'hisab-financial-tracker'); ?></label>
                        <div class="bs-date-inputs">
                            <select id="bs-year-input" name="bs_year" required>
                                <option value=""><?php _e('Year', 'hisab-financial-tracker'); ?></option>
                                <?php
                                    $bs_years = HisabNepaliDate::get_bs_year_range( null, 20 );
                                    foreach ($bs_years as $year) {
                                        echo '<option value="' . $year . '">' . $year . '</option>';
                                    }
                                ?>
                            </select>
                            <select id="bs-month-input" name="bs_month" required>
                                <option value=""><?php _e('Month', 'hisab-financial-tracker'); ?></option>
                                <?php
                                    $bs_months = HisabNepaliDate::get_bs_months();
                                    foreach ($bs_months as $month) {
                                        echo '<option value="' . $month['number'] . '">' . $month['name_en'] . '</option>';
                                    }
                                ?>
                            </select>
                            <select id="bs-day-input" name="bs_day" required>
                                <option value=""><?php _e('Day', 'hisab-financial-tracker'); ?></option>
                                <?php
                                    for ($day = 1; $day <= 32; $day++) {
                                        echo '<option value="' . $day . '">' . $day . '</option>';
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="button button-primary"><?php _e('Convert to AD', 'hisab-financial-tracker'); ?></button>
                </form>
                <div id="bs-to-ad-result" class="hisab-conversion-result"></div>
            </div>
        </div>
        
        
        <!-- Conversion History -->
        <div class="hisab-conversion-history">
            <h2><?php _e('Recent Conversions', 'hisab-financial-tracker'); ?></h2>
            <div id="conversion-history" class="hisab-history-list">
                <!-- History will be populated by JavaScript -->
            </div>
            <button type="button" id="clear-history" class="button"><?php _e('Clear History', 'hisab-financial-tracker'); ?></button>
        </div>
    </div>
</div>

<script>
// Define AJAX configuration
var hisab_ajax = {
    ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
    nonce: '<?php echo wp_create_nonce('hisab_transaction'); ?>'
};

jQuery(document).ready(function($) {
    
    // Load conversion history from localStorage
    loadConversionHistory();
    
    // AD to BS conversion
    $('#ad-to-bs-form').on('submit', function(e) {
        e.preventDefault();
        
        const adDate = $('#ad-date-input').val();
        
        if (!adDate) {
            alert('<?php echo esc_js(__('Please select an AD date', 'hisab-financial-tracker')); ?>');
            return;
        }
        
        
        $.ajax({
            url: hisab_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'hisab_convert_ad_to_bs',
                ad_date: adDate,
                hisab_nonce: hisab_ajax.nonce
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
                    $('#ad-to-bs-result').html('<div class="hisab-result-error"><?php echo esc_js(__('Conversion failed', 'hisab-financial-tracker')); ?></div>');
                }
            },
            error: function() {
                $('#ad-to-bs-result').html('<div class="hisab-result-error"><?php echo esc_js(__('Error converting date', 'hisab-financial-tracker')); ?></div>');
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
            alert('<?php echo esc_js(__('Please select BS year, month, and day', 'hisab-financial-tracker')); ?>');
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
                hisab_nonce: hisab_ajax.nonce
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
                    $('#bs-to-ad-result').html('<div class="hisab-result-error"><?php echo esc_js(__('Conversion failed', 'hisab-financial-tracker')); ?></div>');
                }
            },
            error: function() {
                $('#bs-to-ad-result').html('<div class="hisab-result-error"><?php echo esc_js(__('Error converting date', 'hisab-financial-tracker')); ?></div>');
            }
        });
    });
    
    // Clear history
    $('#clear-history').on('click', function() {
        if (confirm('<?php echo esc_js(__('Are you sure you want to clear the conversion history?', 'hisab-financial-tracker')); ?>')) {
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
        
        $('#conversion-history').html(historyHtml || '<p><?php echo esc_js(__('No conversion history yet', 'hisab-financial-tracker')); ?></p>');
    }
});
</script>
