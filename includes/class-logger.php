<?php
/**
 * Logger class for Hisab Financial Tracker
 */

if (!defined('ABSPATH')) {
    exit;
}

class HisabLogger {
    
    private $log_dir;
    private $log_file_prefix = 'hisab-';
    private $log_file_extension = '.log';
    
    // Log levels
    const LEVEL_INFO = 'INFO';
    const LEVEL_WARNING = 'WARNING';
    const LEVEL_ERROR = 'ERROR';
    const LEVEL_DEBUG = 'DEBUG';
    
    // Action types
    const ACTION_TRANSACTION_CREATE = 'TRANSACTION_CREATE';
    const ACTION_TRANSACTION_UPDATE = 'TRANSACTION_UPDATE';
    const ACTION_TRANSACTION_DELETE = 'TRANSACTION_DELETE';
    const ACTION_BANK_TRANSACTION_CREATE = 'BANK_TRANSACTION_CREATE';
    const ACTION_BANK_TRANSACTION_UPDATE = 'BANK_TRANSACTION_UPDATE';
    const ACTION_BANK_TRANSACTION_DELETE = 'BANK_TRANSACTION_DELETE';
    const ACTION_BANK_ACCOUNT_CREATE = 'BANK_ACCOUNT_CREATE';
    const ACTION_BANK_ACCOUNT_UPDATE = 'BANK_ACCOUNT_UPDATE';
    const ACTION_BANK_ACCOUNT_DELETE = 'BANK_ACCOUNT_DELETE';
    const ACTION_CATEGORY_CREATE = 'CATEGORY_CREATE';
    const ACTION_CATEGORY_UPDATE = 'CATEGORY_UPDATE';
    const ACTION_CATEGORY_DELETE = 'CATEGORY_DELETE';
    const ACTION_OWNER_CREATE = 'OWNER_CREATE';
    const ACTION_OWNER_UPDATE = 'OWNER_UPDATE';
    const ACTION_OWNER_DELETE = 'OWNER_DELETE';
    const ACTION_IMPORT = 'IMPORT';
    const ACTION_EXPORT = 'EXPORT';
    const ACTION_LOGIN = 'LOGIN';
    const ACTION_LOGOUT = 'LOGOUT';
    const ACTION_SETTINGS_UPDATE = 'SETTINGS_UPDATE';
    
    public function __construct() {
        $upload_dir = wp_upload_dir();
        $this->log_dir = $upload_dir['basedir'] . '/hisab-logs/';
        
        // Create log directory if it doesn't exist
        if (!file_exists($this->log_dir)) {
            wp_mkdir_p($this->log_dir);
        }
    }
    
    /**
     * Check if logging is enabled for a specific action
     */
    public function is_logging_enabled($action = null) {
        $general_logging = get_option('hisab_enable_logging', false);
        
        if (!$general_logging) {
            return false;
        }
        
        // If no specific action, return general logging status
        if (!$action) {
            return $general_logging;
        }
        
        // Check specific action logging
        $action_logging = get_option('hisab_log_actions', array());
        return in_array($action, $action_logging);
    }
    
    /**
     * Get log file path for today
     */
    private function get_log_file_path() {
        $date = current_time('Y-m-d');
        return $this->log_dir . $this->log_file_prefix . $date . $this->log_file_extension;
    }
    
    /**
     * Write log entry
     */
    public function log($level, $action, $message, $data = null, $user_id = null) {
        // Check if logging is enabled for this action
        if (!$this->is_logging_enabled($action)) {
            return;
        }
        
        $user_id = $user_id ?: get_current_user_id();
        $user = get_userdata($user_id);
        $username = $user ? $user->user_login : 'Unknown';
        
        $log_entry = array(
            'timestamp' => current_time('Y-m-d H:i:s'),
            'level' => $level,
            'action' => $action,
            'user_id' => $user_id,
            'username' => $username,
            'message' => $message,
            'data' => $data,
            'ip_address' => $this->get_client_ip(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown'
        );
        
        $log_line = json_encode($log_entry) . "\n";
        $log_file = $this->get_log_file_path();
        
        // Write to log file with file locking
        file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log info level
     */
    public function info($action, $message, $data = null, $user_id = null) {
        $this->log(self::LEVEL_INFO, $action, $message, $data, $user_id);
    }
    
    /**
     * Log warning level
     */
    public function warning($action, $message, $data = null, $user_id = null) {
        $this->log(self::LEVEL_WARNING, $action, $message, $data, $user_id);
    }
    
    /**
     * Log error level
     */
    public function error($action, $message, $data = null, $user_id = null) {
        $this->log(self::LEVEL_ERROR, $action, $message, $data, $user_id);
    }
    
    /**
     * Log debug level
     */
    public function debug($action, $message, $data = null, $user_id = null) {
        $this->log(self::LEVEL_DEBUG, $action, $message, $data, $user_id);
    }
    
    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Unknown';
    }
    
    /**
     * Get log files list
     */
    public function get_log_files() {
        $files = array();
        if (is_dir($this->log_dir)) {
            $log_files = glob($this->log_dir . $this->log_file_prefix . '*' . $this->log_file_extension);
            foreach ($log_files as $file) {
                $files[] = array(
                    'filename' => basename($file),
                    'path' => $file,
                    'size' => filesize($file),
                    'modified' => filemtime($file),
                    'date' => date('Y-m-d', filemtime($file))
                );
            }
        }
        
        // Sort by modification time (newest first)
        usort($files, function($a, $b) {
            return $b['modified'] - $a['modified'];
        });
        
        return $files;
    }
    
    /**
     * Read log file content
     */
    public function read_log_file($filename, $lines = 100) {
        $file_path = $this->log_dir . $filename;
        
        if (!file_exists($file_path)) {
            return false;
        }
        
        $content = file_get_contents($file_path);
        $log_entries = array_filter(explode("\n", $content));
        
        // Get last N lines
        $log_entries = array_slice($log_entries, -$lines);
        
        $parsed_entries = array();
        foreach ($log_entries as $entry) {
            $decoded = json_decode($entry, true);
            if ($decoded) {
                $parsed_entries[] = $decoded;
            }
        }
        
        return $parsed_entries;
    }
    
    /**
     * Clean old log files
     */
    public function clean_old_logs($days_to_keep = 30) {
        $log_files = $this->get_log_files();
        $cutoff_time = time() - ($days_to_keep * 24 * 60 * 60);
        
        $deleted_count = 0;
        foreach ($log_files as $file) {
            if ($file['modified'] < $cutoff_time) {
                if (unlink($file['path'])) {
                    $deleted_count++;
                }
            }
        }
        
        return $deleted_count;
    }
    
    /**
     * Get log statistics
     */
    public function get_log_statistics($days = 7) {
        $log_files = $this->get_log_files();
        $cutoff_time = time() - ($days * 24 * 60 * 60);
        
        $stats = array(
            'total_entries' => 0,
            'by_level' => array(),
            'by_action' => array(),
            'by_user' => array(),
            'errors_count' => 0,
            'warnings_count' => 0
        );
        
        foreach ($log_files as $file) {
            if ($file['modified'] >= $cutoff_time) {
                $entries = $this->read_log_file($file['filename'], 10000);
                
                foreach ($entries as $entry) {
                    $stats['total_entries']++;
                    
                    // Count by level
                    if (!isset($stats['by_level'][$entry['level']])) {
                        $stats['by_level'][$entry['level']] = 0;
                    }
                    $stats['by_level'][$entry['level']]++;
                    
                    // Count by action
                    if (!isset($stats['by_action'][$entry['action']])) {
                        $stats['by_action'][$entry['action']] = 0;
                    }
                    $stats['by_action'][$entry['action']]++;
                    
                    // Count by user
                    if (!isset($stats['by_user'][$entry['username']])) {
                        $stats['by_user'][$entry['username']] = 0;
                    }
                    $stats['by_user'][$entry['username']]++;
                    
                    // Count errors and warnings
                    if ($entry['level'] === self::LEVEL_ERROR) {
                        $stats['errors_count']++;
                    } elseif ($entry['level'] === self::LEVEL_WARNING) {
                        $stats['warnings_count']++;
                    }
                }
            }
        }
        
        return $stats;
    }
}
