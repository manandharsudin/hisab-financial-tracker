<?php
/**
 * Add Transaction page for Hisab Financial Tracker
 */

if (!defined('ABSPATH')) {
    exit;
}

$admin = new HisabAdmin();
$admin->render_add_transaction();
