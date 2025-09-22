<?php
/**
 * Plugin Name: Hisab Financial Tracker
 * Description: A comprehensive financial tracking plugin for managing monthly income and expenses with trend analysis and future projections.
 * Version: 1.0.0
 * Author: Sudin Manandhar
 * License: GPL v3 or later
 * Text Domain: hisab-financial-tracker
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('HISAB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('HISAB_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('HISAB_VERSION', '1.0.0');

class HisabFinancialTracker {
    
    public function __construct() {
        // Include required files immediately
        $this->include_files();
        
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Load text domain
        load_plugin_textdomain('hisab-financial-tracker', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Initialize components
        $this->init_components();
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        
        // AJAX handlers
        add_action('wp_ajax_hisab_save_transaction', array($this, 'ajax_save_transaction'));
        add_action('wp_ajax_hisab_get_data', array($this, 'ajax_get_data'));
        add_action('wp_ajax_hisab_delete_transaction', array($this, 'ajax_delete_transaction'));
        add_action('wp_ajax_hisab_calculate_savings', array($this, 'ajax_calculate_savings'));
        
        // Shortcodes
        add_shortcode('hisab_dashboard', array($this, 'shortcode_dashboard'));
        add_shortcode('hisab_income_chart', array($this, 'shortcode_income_chart'));
        add_shortcode('hisab_expense_chart', array($this, 'shortcode_expense_chart'));
    }
    
    private function include_files() {
        require_once HISAB_PLUGIN_PATH . 'includes/class-database.php';
        require_once HISAB_PLUGIN_PATH . 'includes/class-admin.php';
        require_once HISAB_PLUGIN_PATH . 'includes/class-frontend.php';
        require_once HISAB_PLUGIN_PATH . 'includes/class-analytics.php';
        require_once HISAB_PLUGIN_PATH . 'includes/class-projection.php';
    }
    
    private function init_components() {
        if (class_exists('HisabDatabase')) {
            new HisabDatabase();
        }
        if (class_exists('HisabAdmin')) {
            new HisabAdmin();
        }
        if (class_exists('HisabFrontend')) {
            new HisabFrontend();
        }
        if (class_exists('HisabAnalytics')) {
            new HisabAnalytics();
        }
        if (class_exists('HisabProjection')) {
            new HisabProjection();
        }
    }
    
    public function activate() {
        // Create database tables
        $database = new HisabDatabase();
        $database->create_tables();
        
        // Set default options
        add_option('hisab_version', HISAB_VERSION);
        add_option('hisab_currency', 'USD');
        add_option('hisab_date_format', 'Y-m-d');
    }
    
    public function deactivate() {
        // Clean up if needed
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('Financial Tracker', 'hisab-financial-tracker'),
            __('Financial Tracker', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-dashboard',
            array($this, 'admin_dashboard_page'),
            'dashicons-chart-line',
            30
        );
        
        add_submenu_page(
            'hisab-dashboard',
            __('Dashboard', 'hisab-financial-tracker'),
            __('Dashboard', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-dashboard',
            array($this, 'admin_dashboard_page')
        );
        
        add_submenu_page(
            'hisab-dashboard',
            __('Add Transaction', 'hisab-financial-tracker'),
            __('Add Transaction', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-add-transaction',
            array($this, 'admin_add_transaction_page')
        );
        
        add_submenu_page(
            'hisab-dashboard',
            __('Analytics', 'hisab-financial-tracker'),
            __('Analytics', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-analytics',
            array($this, 'admin_analytics_page')
        );
        
        add_submenu_page(
            'hisab-dashboard',
            __('Projections', 'hisab-financial-tracker'),
            __('Projections', 'hisab-financial-tracker'),
            'manage_options',
            'hisab-projections',
            array($this, 'admin_projections_page')
        );
        
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
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'hisab') === false) {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.9.1', true);
        wp_enqueue_script('hisab-admin', HISAB_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'chart-js'), HISAB_VERSION, true);
        wp_enqueue_style('hisab-admin', HISAB_PLUGIN_URL . 'assets/css/admin.css', array(), HISAB_VERSION);
        
        wp_localize_script('hisab-admin', 'hisab_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hisab_nonce'),
            'currency' => get_option('hisab_currency', 'USD')
        ));
    }
    
    public function enqueue_frontend_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.9.1', true);
        wp_enqueue_script('hisab-frontend', HISAB_PLUGIN_URL . 'assets/js/frontend.js', array('jquery', 'chart-js'), HISAB_VERSION, true);
        wp_enqueue_style('hisab-frontend', HISAB_PLUGIN_URL . 'assets/css/frontend.css', array(), HISAB_VERSION);
        
        wp_localize_script('hisab-frontend', 'hisab_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hisab_nonce'),
            'currency' => get_option('hisab_currency', 'USD')
        ));
    }
    
    // AJAX Handlers
    public function ajax_save_transaction() {
        check_ajax_referer('hisab_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabDatabase')) {
            wp_send_json(array('success' => false, 'message' => 'Database class not available'));
        }
        
        $database = new HisabDatabase();
        $result = $database->save_transaction($_POST);
        
        wp_send_json($result);
    }
    
    public function ajax_get_data() {
        check_ajax_referer('hisab_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabDatabase')) {
            wp_send_json(array('success' => false, 'message' => 'Database class not available'));
        }
        
        $database = new HisabDatabase();
        $data = $database->get_transactions($_POST);
        
        wp_send_json($data);
    }
    
    public function ajax_delete_transaction() {
        check_ajax_referer('hisab_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabDatabase')) {
            wp_send_json(array('success' => false, 'message' => 'Database class not available'));
        }
        
        $database = new HisabDatabase();
        $result = $database->delete_transaction($_POST['id']);
        
        wp_send_json($result);
    }
    
    public function ajax_calculate_savings() {
        check_ajax_referer('hisab_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!class_exists('HisabProjection')) {
            wp_send_json(array('success' => false, 'message' => 'Projection class not available'));
        }
        
        $target_amount = floatval($_POST['target_amount']);
        $months_to_target = intval($_POST['months_to_target']);
        
        $projection = new HisabProjection();
        $result = $projection->get_savings_projection($target_amount, $months_to_target);
        
        wp_send_json(array('success' => true, 'data' => $result));
    }
    
    // Shortcodes
    public function shortcode_dashboard($atts) {
        if (!class_exists('HisabFrontend')) {
            return '<p>Frontend class not available</p>';
        }
        $frontend = new HisabFrontend();
        return $frontend->render_dashboard($atts);
    }
    
    public function shortcode_income_chart($atts) {
        if (!class_exists('HisabFrontend')) {
            return '<p>Frontend class not available</p>';
        }
        $frontend = new HisabFrontend();
        return $frontend->render_income_chart($atts);
    }
    
    public function shortcode_expense_chart($atts) {
        if (!class_exists('HisabFrontend')) {
            return '<p>Frontend class not available</p>';
        }
        $frontend = new HisabFrontend();
        return $frontend->render_expense_chart($atts);
    }
}

// Initialize the plugin
new HisabFinancialTracker();
