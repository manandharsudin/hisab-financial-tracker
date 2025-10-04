<?php
/**
 * Settings view template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Financial Tracker Settings', 'hisab-financial-tracker'); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('hisab_settings'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="default_calendar"><?php _e('Default Calendar', 'hisab-financial-tracker'); ?></label>
                </th>
                <td>
                    <select id="default_calendar" name="default_calendar">
                        <option value="ad" <?php selected($default_calendar, 'ad'); ?>><?php _e('AD (Gregorian)', 'hisab-financial-tracker'); ?></option>
                        <option value="bs" <?php selected($default_calendar, 'bs'); ?>><?php _e('BS (Bikram Sambat)', 'hisab-financial-tracker'); ?></option>
                    </select>
                    <p class="description"><?php _e('Choose the default calendar for transaction entry forms.', 'hisab-financial-tracker'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="show_dual_dates"><?php _e('Show Dual Dates', 'hisab-financial-tracker'); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="show_dual_dates" name="show_dual_dates" value="1" <?php checked($show_dual_dates, 1); ?>>
                        <?php _e('Display both AD and BS dates in transaction lists', 'hisab-financial-tracker'); ?>
                    </label>
                    <p class="description"><?php _e('When enabled, both AD and BS dates will be shown in transaction displays.', 'hisab-financial-tracker'); ?></p>
                </td>
            </tr>
        </table>
        
        <?php submit_button('Save Settings', 'primary', 'save_settings'); ?>
    </form>
</div>
