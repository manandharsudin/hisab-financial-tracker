<?php
/**
 * Nepali Date Converter using milantarami/nepali-calendar library
 * 
 * @package HisabFinancialTracker
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('HisabNepaliDate')) {
    class HisabNepaliDate {
    
        /**
         * Convert AD date to BS date using milantarami/nepali-calendar library
         * 
         * @param int $ad_year AD Year
         * @param int $ad_month AD Month (1-12)
         * @param int $ad_day AD Day
         * @return array|false BS date array ['year', 'month', 'day'] or false on error
         */
        public static function ad_to_bs($ad_year, $ad_month, $ad_day) {
            // Validate input
            if (!self::is_valid_ad_date($ad_year, $ad_month, $ad_day)) {
                return false;
            }
            
            try {
                // Load the library if not already loaded
                if (!self::load_library()) {
                    return false;
                }
                
                // Convert AD to BS using static method
                $ad_date_string = sprintf('%04d-%02d-%02d', $ad_year, $ad_month, $ad_day);
                $result = \MilanTarami\NepaliCalendar\CalendarFunction::adToBs($ad_date_string, 'YYYY-MM-DD', '-');
                
                if ($result && isset($result['BS_DATE'])) {
                    $bs_parts = explode('-', $result['BS_DATE']);
                    if (count($bs_parts) === 3) {
                        return array(
                            'year' => intval($bs_parts[0]),
                            'month' => intval($bs_parts[1]),
                            'day' => intval($bs_parts[2])
                        );
                    }
                }
                
                return false;
                
            } catch (Exception $e) {
                error_log('Hisab: Error converting AD to BS: ' . $e->getMessage());
                return false;
            }
        }
        
        /**
         * Convert BS date to AD date using milantarami/nepali-calendar library
         * 
         * @param int $bs_year BS Year
         * @param int $bs_month BS Month (1-12)
         * @param int $bs_day BS Day
         * @return array|false AD date array ['year', 'month', 'day'] or false on error
         */
        public static function bs_to_ad($bs_year, $bs_month, $bs_day) {
            // Validate input
            if (!self::is_valid_bs_date($bs_year, $bs_month, $bs_day)) {
                return false;
            }
            
            try {
                // Load the library if not already loaded
                if (!self::load_library()) {
                    return false;
                }
                
                // Convert BS to AD using static method
                $bs_date_string = sprintf('%04d-%02d-%02d', $bs_year, $bs_month, $bs_day);
                $result = \MilanTarami\NepaliCalendar\CalendarFunction::bsToAd($bs_date_string, 'YYYY-MM-DD', '-');
                
                if ($result && isset($result['AD_DATE'])) {
                    $ad_parts = explode('-', $result['AD_DATE']);
                    if (count($ad_parts) === 3) {
                        return array(
                            'year' => intval($ad_parts[0]),
                            'month' => intval($ad_parts[1]),
                            'day' => intval($ad_parts[2])
                        );
                    }
                }
                
                return false;
                
            } catch (Exception $e) {
                error_log('Hisab: Error converting BS to AD: ' . $e->getMessage());
                return false;
            }
        }
        
        /**
         * Get current BS date
         * 
         * @return array|false Current BS date array ['year', 'month', 'day'] or false on error
         */
        public static function get_current_bs_date() {
            $today = getdate();
            return self::ad_to_bs($today['year'], $today['mon'], $today['mday']);
        }
        
        /**
         * Get BS months data
         * 
         * @param int|null $month Optional month number (1-12) to get specific month name
         * @return array|string If $month is provided, returns month name string, otherwise returns array of all months
         */
        public static function get_bs_months($month = null) {
            $months = array(
                1 => 'Baisakh', 'Jestha', 'Ashadh', 'Shrawan', 'Bhadra', 'Ashwin',
                'Kartik', 'Mangsir', 'Poush', 'Magh', 'Falgun', 'Chaitra'
            );
            
            // If specific month requested, return just the name
            if ($month !== null) {
                return isset($months[$month]) ? $months[$month] : '';
            }
            
            // Otherwise, return array format for dropdowns
            $result = array();
            foreach ($months as $number => $name) {
                $result[] = array(
                    'number' => $number,
                    'name_en' => $name
                );
            }
            
            return $result;
        }
        
        /**
         * Load the milantarami/nepali-calendar library
         * 
         * @return bool True if library is available, false otherwise
         */
        private static function load_library() {
            if (!class_exists('MilanTarami\NepaliCalendar\CalendarFunction')) {
                $autoloader = plugin_dir_path(__FILE__) . '../vendor/autoload.php';
                if (file_exists($autoloader)) {
                    require_once $autoloader;
                } else {
                    error_log('Hisab: milantarami/nepali-calendar library not found.');
                    return false;
                }
            }
            
            // Check if the class exists after loading autoloader
            if (!class_exists('MilanTarami\NepaliCalendar\CalendarFunction')) {
                error_log('Hisab: milantarami/nepali-calendar library not available after loading autoloader.');
                return false;
            }
            
            return true;
        }
        
        /**
         * Validate AD date
         * 
         * @param int $year Year
         * @param int $month Month
         * @param int $day Day
         * @return bool True if valid
         */
        private static function is_valid_ad_date($year, $month, $day) {
            return checkdate($month, $day, $year);
        }
        
        /**
         * Validate BS date
         * 
         * @param int $year Year
         * @param int $month Month
         * @param int $day Day
         * @return bool True if valid
         */
        private static function is_valid_bs_date($year, $month, $day) {
            // Basic validation
            if ($year < 1975 || $year > 2100) return false;
            if ($month < 1 || $month > 12) return false;
            if ($day < 1 || $day > 32) return false;
            
            // More specific validation would require the library
            return true;
        }
        
        
        
        /**
         * Get BS year range for dropdowns
         * 
         * @param int $center_year Center year
         * @param int $range Range in years
         * @return array Array of years
         */
        public static function get_bs_year_range($center_year = null, $range = 10) {
            if (!$center_year) {
                // Use approximate BS year (AD + 57)
                $current_ad = getdate();
                $center_year = $current_ad['year'] + 57;
            }
            
            $years = array();
            for ($year = $center_year - $range; $year <= $center_year + $range; $year++) {
                $years[] = $year;
            }
            
            return $years;
        }
        
    }
}