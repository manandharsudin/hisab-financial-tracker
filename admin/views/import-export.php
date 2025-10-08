<?php
/**
 * Import/Export Settings Page
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Import/Export Data', 'hisab-financial-tracker'); ?></h1>
    
    <div class="hisab-import-export-container">
        
        <!-- Export Section -->
        <div class="hisab-export-section">
            <h2><?php _e('Export Data', 'hisab-financial-tracker'); ?></h2>
            <p><?php _e('Export your financial data to backup or transfer to another system.', 'hisab-financial-tracker'); ?></p>
            
            <div class="hisab-export-options">
                <form id="hisab-export-form" class="hisab-form">
                    <div class="hisab-form-row">
                        <div class="hisab-form-group">
                            <label for="export-type"><?php _e('Data Type', 'hisab-financial-tracker'); ?></label>
                            <select id="export-type" name="export_type" required>
                                <option value="all"><?php _e('All Data', 'hisab-financial-tracker'); ?></option>
                                <option value="transactions"><?php _e('Transactions Only', 'hisab-financial-tracker'); ?></option>
                                <option value="bank_transactions"><?php _e('Bank Transactions Only', 'hisab-financial-tracker'); ?></option>
                                <option value="bank_accounts"><?php _e('Bank Accounts Only', 'hisab-financial-tracker'); ?></option>
                                <option value="categories"><?php _e('Categories Only', 'hisab-financial-tracker'); ?></option>
                                <option value="owners"><?php _e('Owners Only', 'hisab-financial-tracker'); ?></option>
                            </select>
                        </div>
                        
                        <div class="hisab-form-group">
                            <button type="button" class="button button-primary hisab-export-btn" data-format="json" data-type="all">
                                <span class="dashicons dashicons-download"></span>
                                <?php _e('Export JSON', 'hisab-financial-tracker'); ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Import Section -->
        <div class="hisab-import-section">
            <h2><?php _e('Import Data', 'hisab-financial-tracker'); ?></h2>
            <p><?php _e('Import financial data from a previously exported JSON file.', 'hisab-financial-tracker'); ?></p>
            
            <div class="hisab-import-options">
                <form id="hisab-import-form" class="hisab-form" enctype="multipart/form-data">
                    <div class="hisab-form-row">
                        <div class="hisab-form-group">
                            <label for="import-file"><?php _e('Select File', 'hisab-financial-tracker'); ?></label>
                            <input type="file" id="import-file" name="import_file" accept=".json" required>
                            <p class="description"><?php _e('Only JSON files exported from this plugin are supported.', 'hisab-financial-tracker'); ?></p>
                        </div>
                    </div>
                    
                    <div class="hisab-form-row">
                        <h3><?php _e('Import Options', 'hisab-financial-tracker'); ?></h3>
                        <div class="hisab-import-checkboxes">
                            <label>
                                <input type="checkbox" name="import_categories" value="1" checked>
                                <?php _e('Import Categories', 'hisab-financial-tracker'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="import_owners" value="1" checked>
                                <?php _e('Import Owners', 'hisab-financial-tracker'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="import_bank_accounts" value="1" checked>
                                <?php _e('Import Bank Accounts', 'hisab-financial-tracker'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="import_transactions" value="1" checked>
                                <?php _e('Import Transactions', 'hisab-financial-tracker'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="import_bank_transactions" value="1" checked>
                                <?php _e('Import Bank Transactions', 'hisab-financial-tracker'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="import_transaction_details" value="1" checked>
                                <?php _e('Import Transaction Details', 'hisab-financial-tracker'); ?>
                            </label>
                        </div>
                    </div>
                    
                    <div class="hisab-form-row">
                        <h3><?php _e('Duplicate Handling', 'hisab-financial-tracker'); ?></h3>
                        <div class="hisab-import-options">
                            <label>
                                <input type="radio" name="duplicate_handling" value="skip" checked>
                                <?php _e('Skip Duplicates', 'hisab-financial-tracker'); ?>
                            </label>
                            <label>
                                <input type="radio" name="duplicate_handling" value="update">
                                <?php _e('Update Existing', 'hisab-financial-tracker'); ?>
                            </label>
                        </div>
                    </div>
                    
                    <div class="hisab-form-row">
                        <button type="submit" class="button button-primary hisab-import-btn">
                            <span class="dashicons dashicons-upload"></span>
                            <?php _e('Import Data', 'hisab-financial-tracker'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Results Section -->
        <div id="hisab-import-export-results" class="hisab-results-section" style="display: none;">
            <h3><?php _e('Results', 'hisab-financial-tracker'); ?></h3>
            <div id="hisab-results-content"></div>
        </div>
        
    </div>
</div>

<style>
.hisab-import-export-container {
    max-width: 800px;
}

.hisab-export-section,
.hisab-import-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}

.hisab-export-section h2,
.hisab-import-section h2 {
    margin-top: 0;
    color: #23282d;
}

.hisab-import-checkboxes {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 10px;
    margin: 10px 0;
}

.hisab-import-checkboxes label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: normal;
}

.hisab-import-options {
    display: flex;
    gap: 20px;
    margin: 10px 0;
}

.hisab-import-options label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: normal;
}

.hisab-export-btn,
.hisab-import-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    height: auto;
}

.hisab-results-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}

.hisab-results-section h3 {
    margin-top: 0;
    color: #23282d;
}

.hisab-result-item {
    margin: 10px 0;
    padding: 10px;
    border-radius: 4px;
}

.hisab-result-item.success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.hisab-result-item.error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.hisab-result-item.warning {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
}

.hisab-result-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin: 15px 0;
}

.hisab-stat-card {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 15px;
    text-align: center;
}

.hisab-stat-number {
    font-size: 24px;
    font-weight: bold;
    color: #007cba;
}

.hisab-stat-label {
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
    margin-top: 5px;
}
</style>

