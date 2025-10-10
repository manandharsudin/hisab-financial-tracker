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
        
        <h2><?php _e('Logging Settings', 'hisab-financial-tracker'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="enable_logging"><?php _e('Enable Logging', 'hisab-financial-tracker'); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="enable_logging" name="enable_logging" value="1" <?php checked($enable_logging, 1); ?>>
                        <?php _e('Enable activity logging for the plugin', 'hisab-financial-tracker'); ?>
                    </label>
                    <p class="description"><?php _e('When enabled, all plugin activities will be logged to files in the uploads folder.', 'hisab-financial-tracker'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label><?php _e('Log Actions', 'hisab-financial-tracker'); ?></label>
                </th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text"><?php _e('Select which actions to log', 'hisab-financial-tracker'); ?></legend>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                            <label>
                                <input type="checkbox" name="log_actions[]" value="TRANSACTION_CREATE" <?php checked(in_array('TRANSACTION_CREATE', $log_actions)); ?>>
                                <?php _e('Transaction Create', 'hisab-financial-tracker'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="log_actions[]" value="TRANSACTION_UPDATE" <?php checked(in_array('TRANSACTION_UPDATE', $log_actions)); ?>>
                                <?php _e('Transaction Update', 'hisab-financial-tracker'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="log_actions[]" value="TRANSACTION_DELETE" <?php checked(in_array('TRANSACTION_DELETE', $log_actions)); ?>>
                                <?php _e('Transaction Delete', 'hisab-financial-tracker'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="log_actions[]" value="BANK_TRANSACTION_CREATE" <?php checked(in_array('BANK_TRANSACTION_CREATE', $log_actions)); ?>>
                                <?php _e('Bank Transaction Create', 'hisab-financial-tracker'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="log_actions[]" value="BANK_TRANSACTION_UPDATE" <?php checked(in_array('BANK_TRANSACTION_UPDATE', $log_actions)); ?>>
                                <?php _e('Bank Transaction Update', 'hisab-financial-tracker'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="log_actions[]" value="BANK_TRANSACTION_DELETE" <?php checked(in_array('BANK_TRANSACTION_DELETE', $log_actions)); ?>>
                                <?php _e('Bank Transaction Delete', 'hisab-financial-tracker'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="log_actions[]" value="BANK_ACCOUNT_CREATE" <?php checked(in_array('BANK_ACCOUNT_CREATE', $log_actions)); ?>>
                                <?php _e('Bank Account Create', 'hisab-financial-tracker'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="log_actions[]" value="BANK_ACCOUNT_UPDATE" <?php checked(in_array('BANK_ACCOUNT_UPDATE', $log_actions)); ?>>
                                <?php _e('Bank Account Update', 'hisab-financial-tracker'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="log_actions[]" value="BANK_ACCOUNT_DELETE" <?php checked(in_array('BANK_ACCOUNT_DELETE', $log_actions)); ?>>
                                <?php _e('Bank Account Delete', 'hisab-financial-tracker'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="log_actions[]" value="CATEGORY_CREATE" <?php checked(in_array('CATEGORY_CREATE', $log_actions)); ?>>
                                <?php _e('Category Create', 'hisab-financial-tracker'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="log_actions[]" value="CATEGORY_UPDATE" <?php checked(in_array('CATEGORY_UPDATE', $log_actions)); ?>>
                                <?php _e('Category Update', 'hisab-financial-tracker'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="log_actions[]" value="CATEGORY_DELETE" <?php checked(in_array('CATEGORY_DELETE', $log_actions)); ?>>
                                <?php _e('Category Delete', 'hisab-financial-tracker'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="log_actions[]" value="OWNER_CREATE" <?php checked(in_array('OWNER_CREATE', $log_actions)); ?>>
                                <?php _e('Owner Create', 'hisab-financial-tracker'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="log_actions[]" value="OWNER_UPDATE" <?php checked(in_array('OWNER_UPDATE', $log_actions)); ?>>
                                <?php _e('Owner Update', 'hisab-financial-tracker'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="log_actions[]" value="OWNER_DELETE" <?php checked(in_array('OWNER_DELETE', $log_actions)); ?>>
                                <?php _e('Owner Delete', 'hisab-financial-tracker'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="log_actions[]" value="IMPORT" <?php checked(in_array('IMPORT', $log_actions)); ?>>
                                <?php _e('Data Import', 'hisab-financial-tracker'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="log_actions[]" value="EXPORT" <?php checked(in_array('EXPORT', $log_actions)); ?>>
                                <?php _e('Data Export', 'hisab-financial-tracker'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="log_actions[]" value="SETTINGS_UPDATE" <?php checked(in_array('SETTINGS_UPDATE', $log_actions)); ?>>
                                <?php _e('Settings Update', 'hisab-financial-tracker'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="log_actions[]" value="TRANSACTION_DETAILS_SAVE" <?php checked(in_array('TRANSACTION_DETAILS_SAVE', $log_actions)); ?>>
                                <?php _e('Transaction Details Save', 'hisab-financial-tracker'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="log_actions[]" value="TRANSACTION_DETAILS_DELETE" <?php checked(in_array('TRANSACTION_DETAILS_DELETE', $log_actions)); ?>>
                                <?php _e('Transaction Details Delete', 'hisab-financial-tracker'); ?>
                            </label>
                        </div>
                    </fieldset>
                    <p class="description"><?php _e('Select which actions should be logged. All actions are logged by default when logging is enabled.', 'hisab-financial-tracker'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="log_retention_days"><?php _e('Log Retention (Days)', 'hisab-financial-tracker'); ?></label>
                </th>
                <td>
                    <input type="number" id="log_retention_days" name="log_retention_days" value="<?php echo esc_attr($log_retention_days); ?>" min="1" max="365" class="small-text">
                    <p class="description"><?php _e('Number of days to keep log files. Older files will be automatically deleted.', 'hisab-financial-tracker'); ?></p>
                </td>
            </tr>
        </table>
        
        <?php submit_button('Save Settings', 'primary', 'save_settings'); ?>
    </form>
</div>