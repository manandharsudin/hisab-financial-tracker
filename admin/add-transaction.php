<?php
/**
 * Add Transaction page for Hisab Financial Tracker
 */

if (!defined('ABSPATH')) {
    exit;
}

$admin = new HisabAdmin();

// Check if we're in edit mode
$edit_transaction_id = isset($_GET['edit']) ? intval($_GET['edit']) : null;
$edit_transaction = null;

if ($edit_transaction_id) {
    $database = new HisabDatabase();
    $edit_transaction = $database->get_transaction($edit_transaction_id);
}

$admin->render_add_transaction($edit_transaction);
