<?php
/**
 * Shortcode management class for Hisab Financial Tracker
 */

if (!defined('ABSPATH')) {
    exit;
}

class HisabShortcodes {
    
    public function __construct() {
        add_shortcode('hisab_dashboard', array($this, 'shortcode_dashboard'));
        add_shortcode('hisab_income_chart', array($this, 'shortcode_income_chart'));
        add_shortcode('hisab_expense_chart', array($this, 'shortcode_expense_chart'));
        add_shortcode('hisab_monthly_summary', array($this, 'shortcode_monthly_summary'));
        add_shortcode('hisab_transaction_form', array($this, 'shortcode_transaction_form'));
    }
    
    public function shortcode_dashboard($atts) {
        if (!class_exists('HisabFrontend')) {
            return '<p>Frontend class not available</p>';
        }
        
        $atts = shortcode_atts(array(
            'months' => 6,
            'show_charts' => 'true',
            'show_recent' => 'true',
            'show_summary' => 'true'
        ), $atts);
        
        $frontend = new HisabFrontend();
        return $frontend->render_dashboard($atts);
    }
    
    public function shortcode_income_chart($atts) {
        if (!class_exists('HisabFrontend')) {
            return '<p>Frontend class not available</p>';
        }
        
        $atts = shortcode_atts(array(
            'months' => 6,
            'height' => '300',
            'width' => '100%',
            'show_legend' => 'true'
        ), $atts);
        
        $frontend = new HisabFrontend();
        return $frontend->render_income_chart($atts);
    }
    
    public function shortcode_expense_chart($atts) {
        if (!class_exists('HisabFrontend')) {
            return '<p>Frontend class not available</p>';
        }
        
        $atts = shortcode_atts(array(
            'months' => 6,
            'height' => '300',
            'width' => '100%',
            'show_legend' => 'true'
        ), $atts);
        
        $frontend = new HisabFrontend();
        return $frontend->render_expense_chart($atts);
    }
    
    public function shortcode_monthly_summary($atts) {
        if (!class_exists('HisabFrontend')) {
            return '<p>Frontend class not available</p>';
        }
        
        $atts = shortcode_atts(array(
            'year' => date('Y'),
            'month' => date('n'),
            'show_net' => 'true',
            'currency' => 'USD'
        ), $atts);
        
        $frontend = new HisabFrontend();
        return $frontend->render_monthly_summary($atts);
    }
    
    public function shortcode_transaction_form($atts) {
        if (!class_exists('HisabFrontend')) {
            return '<p>Frontend class not available</p>';
        }
        
        $atts = shortcode_atts(array(
            'show_categories' => 'true',
            'default_type' => '',
            'redirect_url' => ''
        ), $atts);
        
        $frontend = new HisabFrontend();
        return $frontend->render_transaction_form($atts);
    }
}
