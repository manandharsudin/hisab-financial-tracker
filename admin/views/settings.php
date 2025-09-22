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
                    <label for="currency"><?php _e('Currency', 'hisab-financial-tracker'); ?></label>
                </th>
                <td>
                    <select id="currency" name="currency">
                        <option value="USD" <?php selected($currency, 'USD'); ?>>USD - US Dollar</option>
                        <option value="EUR" <?php selected($currency, 'EUR'); ?>>EUR - Euro</option>
                        <option value="GBP" <?php selected($currency, 'GBP'); ?>>GBP - British Pound</option>
                        <option value="JPY" <?php selected($currency, 'JPY'); ?>>JPY - Japanese Yen</option>
                        <option value="CAD" <?php selected($currency, 'CAD'); ?>>CAD - Canadian Dollar</option>
                        <option value="AUD" <?php selected($currency, 'AUD'); ?>>AUD - Australian Dollar</option>
                        <option value="INR" <?php selected($currency, 'INR'); ?>>INR - Indian Rupee</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="date_format"><?php _e('Date Format', 'hisab-financial-tracker'); ?></label>
                </th>
                <td>
                    <select id="date_format" name="date_format">
                        <option value="Y-m-d" <?php selected($date_format, 'Y-m-d'); ?>>YYYY-MM-DD</option>
                        <option value="m/d/Y" <?php selected($date_format, 'm/d/Y'); ?>>MM/DD/YYYY</option>
                        <option value="d/m/Y" <?php selected($date_format, 'd/m/Y'); ?>>DD/MM/YYYY</option>
                    </select>
                </td>
            </tr>
        </table>
        
        <?php submit_button('Save Settings', 'primary', 'save_settings'); ?>
    </form>
</div>
