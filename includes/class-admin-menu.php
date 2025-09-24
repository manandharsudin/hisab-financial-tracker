<?php
/**
 * Admin Menu management class for Hisab Financial Tracker
 */

if (!defined('ABSPATH')) {
    exit;
}

class HisabAdminMenu {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    public function add_admin_menu() {
        // Main menu page
        add_menu_page(
            __('Financial Tracker', 'hisab-financial-tracker'),
            __('Financial Tracker', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-dashboard',
            array($this, 'admin_dashboard_page'),
            'dashicons-chart-line',
            30
        );
        
        // Dashboard submenu
        add_submenu_page(
            'hisab-dashboard',
            __('Dashboard', 'hisab-financial-tracker'),
            __('Dashboard', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-dashboard',
            array($this, 'admin_dashboard_page')
        );
        
        // Add Transaction submenu
        add_submenu_page(
            'hisab-dashboard',
            __('Add Transaction', 'hisab-financial-tracker'),
            __('Add Transaction', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-add-transaction',
            array($this, 'admin_add_transaction_page')
        );
        
        // Analytics submenu
        add_submenu_page(
            'hisab-dashboard',
            __('Analytics', 'hisab-financial-tracker'),
            __('Analytics', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-analytics',
            array($this, 'admin_analytics_page')
        );
        
        // Projections submenu
        add_submenu_page(
            'hisab-dashboard',
            __('Projections', 'hisab-financial-tracker'),
            __('Projections', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-projections',
            array($this, 'admin_projections_page')
        );
        
        // Settings submenu
        add_submenu_page(
            'hisab-dashboard',
            __('Settings', 'hisab-financial-tracker'),
            __('Settings', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-settings',
            array($this, 'admin_settings_page')
        );
    }
    
    public function admin_dashboard_page() {
        if (!class_exists('HisabAdmin')) {
            echo '<div class="wrap"><h1>Error</h1><p>Admin class not available. Please check if all plugin files are properly uploaded.</p></div>';
            return;
        }
        include HISAB_PLUGIN_PATH . 'admin/dashboard.php';
    }
    
    public function admin_add_transaction_page() {
        if (!class_exists('HisabAdmin')) {
            echo '<div class="wrap"><h1>Error</h1><p>Admin class not available. Please check if all plugin files are properly uploaded.</p></div>';
            return;
        }
        include HISAB_PLUGIN_PATH . 'admin/add-transaction.php';
    }
    
    public function admin_analytics_page() {
        if (!class_exists('HisabAdmin')) {
            echo '<div class="wrap"><h1>Error</h1><p>Admin class not available. Please check if all plugin files are properly uploaded.</p></div>';
            return;
        }
        include HISAB_PLUGIN_PATH . 'admin/analytics.php';
    }
    
    public function admin_projections_page() {
        if (!class_exists('HisabAdmin')) {
            echo '<div class="wrap"><h1>Error</h1><p>Admin class not available. Please check if all plugin files are properly uploaded.</p></div>';
            return;
        }
        include HISAB_PLUGIN_PATH . 'admin/projections.php';
    }
    
    public function admin_settings_page() {
        if (!class_exists('HisabAdmin')) {
            echo '<div class="wrap"><h1>Error</h1><p>Admin class not available. Please check if all plugin files are properly uploaded.</p></div>';
            return;
        }
        include HISAB_PLUGIN_PATH . 'admin/settings.php';
    }
}
