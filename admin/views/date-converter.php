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