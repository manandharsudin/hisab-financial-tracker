<?php
/**
 * Owners management admin page
 */

if (!defined('ABSPATH')) {
    exit;
}

// Initialize admin class
$admin = new HisabAdmin();
$admin->render_owners();
