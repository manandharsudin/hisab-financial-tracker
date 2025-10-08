<?php
/**
 * Projection class for Hisab Financial Tracker
 */

if (!defined('ABSPATH')) {
    exit;
}

class HisabProjection {
    
    private $database;
    private $analytics;
    
    public function __construct() {
        $this->database = new HisabDatabase();
        $this->analytics = new HisabAnalytics();
    }
    
    public function get_future_projections($months_ahead = 12) {
        // Get historical data for trend analysis
        $income_trend = $this->database->get_trend_data('income', 12);
        $expense_trend = $this->database->get_trend_data('expense', 12);
        
        $projections = array();
        $current_date = date('Y-m-01');
        
        for ($i = 1; $i <= $months_ahead; $i++) {
            $projection_date = date('Y-m-01', strtotime("+{$i} months", strtotime($current_date)));
            $projection_month = date('n', strtotime($projection_date));
            $projection_year = date('Y', strtotime($projection_date));
            
            $projected_income = $this->calculate_projected_amount($income_trend, $projection_month);
            $projected_expense = $this->calculate_projected_amount($expense_trend, $projection_month);
            
            // Get calendar setting for month name display
            $default_calendar = get_option('hisab_default_calendar', 'ad');
            if ($default_calendar === 'bs') {
                $bs_date = HisabNepaliDate::ad_to_bs($projection_year, $projection_month, 1);
                $month_name = HisabNepaliDate::get_bs_months($bs_date['month']) . ' ' . $bs_date['year'];
            } else {
                $month_name = date('F Y', strtotime($projection_date));
            }
            
            $projections[] = array(
                'month' => $projection_month,
                'year' => $projection_year,
                'month_name' => $month_name,
                'projected_income' => $projected_income,
                'projected_expense' => $projected_expense,
                'projected_net' => $projected_income - $projected_expense
            );
        }
        
        return $projections;
    }
    
    private function calculate_projected_amount($trend_data, $target_month) {
        if (empty($trend_data)) {
            return 0;
        }
        
        // Get the most recent amount
        $latest_amount = end($trend_data)->total;
        
        // Calculate growth rate with improved logic
        $growth_rate = $this->calculate_improved_growth_rate($trend_data);
        
        // Apply seasonal adjustment if we have enough data
        $seasonal_adjustment = $this->get_seasonal_adjustment($trend_data, $target_month);
        
        // Calculate projection with month-based growth application
        $months_ahead = $this->get_months_ahead($trend_data, $target_month);
        $projected_amount = $latest_amount * pow((1 + ($growth_rate / 100)), $months_ahead) * $seasonal_adjustment;
        
        // Add some realistic variation for limited data scenarios
        if (count($trend_data) < 6) {
            $variation = $this->add_realistic_variation($latest_amount, $target_month);
            $projected_amount += $variation;
        }
        
        return max(0, $projected_amount); // Ensure non-negative
    }
    
    private function get_seasonal_adjustment($trend_data, $target_month) {
        if (count($trend_data) < 3) {
            // Use default seasonal factors for very limited data
            return $this->get_default_seasonal_factor($target_month);
        }
        
        // Group data by month
        $monthly_averages = array();
        foreach ($trend_data as $data) {
            $month = date('n', strtotime($data->month . '-01'));
            if (!isset($monthly_averages[$month])) {
                $monthly_averages[$month] = array();
            }
            $monthly_averages[$month][] = $data->total;
        }
        
        // Calculate average for each month
        foreach ($monthly_averages as $month => $amounts) {
            $monthly_averages[$month] = array_sum($amounts) / count($amounts);
        }
        
        // If we have data for the target month, use it
        if (isset($monthly_averages[$target_month]) && count($monthly_averages) > 1) {
            $overall_average = array_sum($monthly_averages) / count($monthly_averages);
            if ($overall_average > 0) {
                return $monthly_averages[$target_month] / $overall_average;
            }
        }
        
        // Fall back to default seasonal factors
        return $this->get_default_seasonal_factor($target_month);
    }
    
    /**
     * Get default seasonal adjustment factors
     */
    private function get_default_seasonal_factor($target_month) {
        $seasonal_factors = array(
            1 => 1.05,   // January - slightly higher
            2 => 1.02,   // February - normal
            3 => 1.08,   // March - higher (end of fiscal year)
            4 => 1.03,   // April - normal
            5 => 1.01,   // May - slightly higher
            6 => 0.98,   // June - lower
            7 => 0.99,   // July - slightly lower
            8 => 1.02,   // August - normal
            9 => 1.04,   // September - slightly higher
            10 => 1.06,  // October - higher
            11 => 1.03,  // November - normal
            12 => 1.10   // December - highest (holiday season)
        );
        
        return isset($seasonal_factors[$target_month]) ? $seasonal_factors[$target_month] : 1.0;
    }
    
    public function get_savings_projection($target_amount, $months_to_target) {
        $current_savings = $this->get_current_savings();
        $monthly_income = $this->get_average_monthly_income();
        $monthly_expense = $this->get_average_monthly_expense();
        
        $monthly_savings = $monthly_income - $monthly_expense;
        
        if ($monthly_savings <= 0) {
            return array(
                'achievable' => false,
                'message' => 'Current expenses exceed income. Cannot achieve savings target.',
                'required_monthly_savings' => 0,
                'current_monthly_savings' => $monthly_savings
            );
        }
        
        $required_monthly_savings = ($target_amount - $current_savings) / $months_to_target;
        
        return array(
            'achievable' => $required_monthly_savings <= $monthly_savings,
            'required_monthly_savings' => $required_monthly_savings,
            'current_monthly_savings' => $monthly_savings,
            'months_to_target' => $months_to_target,
            'target_amount' => $target_amount,
            'current_savings' => $current_savings
        );
    }
    
    private function get_current_savings() {
        $current_year = date('Y');
        $yearly_data = $this->database->get_yearly_summary($current_year);
        
        $total_income = 0;
        $total_expense = 0;
        
        foreach ($yearly_data as $month_data) {
            $total_income += $month_data['income'];
            $total_expense += $month_data['expense'];
        }
        
        return $total_income - $total_expense;
    }
    
    private function get_average_monthly_income() {
        $trend_data = $this->database->get_trend_data('income', 6);
        if (empty($trend_data)) {
            return 0;
        }
        
        $total = 0;
        foreach ($trend_data as $data) {
            $total += $data->total;
        }
        
        return $total / count($trend_data);
    }
    
    private function get_average_monthly_expense() {
        $trend_data = $this->database->get_trend_data('expense', 6);
        if (empty($trend_data)) {
            return 0;
        }
        
        $total = 0;
        foreach ($trend_data as $data) {
            $total += $data->total;
        }
        
        return $total / count($trend_data);
    }
    
    /**
     * Calculate improved growth rate with better handling for limited data
     */
    private function calculate_improved_growth_rate($trend_data) {
        if (count($trend_data) < 2) {
            // If no historical data, assume modest growth
            return 2.0; // 2% monthly growth assumption
        }
        
        if (count($trend_data) < 3) {
            // With only 2 data points, use simple growth rate
            $first_value = $trend_data[0]->total;
            $last_value = end($trend_data)->total;
            
            if ($first_value == 0) {
                return $last_value > 0 ? 5.0 : 0; // 5% if starting from 0
            }
            
            return (($last_value - $first_value) / $first_value) * 100;
        }
        
        // For 3+ data points, use weighted average growth rate
        $growth_rates = array();
        for ($i = 1; $i < count($trend_data); $i++) {
            $prev_value = $trend_data[$i-1]->total;
            $curr_value = $trend_data[$i]->total;
            
            if ($prev_value > 0) {
                $monthly_growth = (($curr_value - $prev_value) / $prev_value) * 100;
                $growth_rates[] = $monthly_growth;
            }
        }
        
        if (empty($growth_rates)) {
            return 1.0; // 1% default growth
        }
        
        // Weight recent growth more heavily
        $weights = array();
        for ($i = 0; $i < count($growth_rates); $i++) {
            $weights[] = $i + 1; // More recent = higher weight
        }
        
        $weighted_sum = 0;
        $total_weight = 0;
        for ($i = 0; $i < count($growth_rates); $i++) {
            $weighted_sum += $growth_rates[$i] * $weights[$i];
            $total_weight += $weights[$i];
        }
        
        $average_growth = $weighted_sum / $total_weight;
        
        // Cap growth rate to reasonable limits
        return max(-10, min(20, $average_growth)); // Between -10% and 20%
    }
    
    /**
     * Calculate how many months ahead we're projecting
     */
    private function get_months_ahead($trend_data, $target_month) {
        $latest_month = date('n', strtotime(end($trend_data)->month . '-01'));
        $current_year = date('Y');
        $latest_year = date('Y', strtotime(end($trend_data)->month . '-01'));
        
        $months_ahead = ($target_month - $latest_month) % 12;
        if ($months_ahead <= 0) {
            $months_ahead += 12;
        }
        
        return $months_ahead;
    }
    
    /**
     * Add realistic variation when data is limited
     */
    private function add_realistic_variation($base_amount, $target_month) {
        // Add some seasonal variation even with limited data
        $seasonal_factors = array(
            1 => 0.05,   // January - slightly higher
            2 => 0.02,   // February - normal
            3 => 0.08,   // March - higher (end of fiscal year)
            4 => 0.03,   // April - normal
            5 => 0.01,   // May - slightly lower
            6 => -0.02,  // June - lower
            7 => -0.01,  // July - slightly lower
            8 => 0.02,   // August - normal
            9 => 0.04,   // September - slightly higher
            10 => 0.06,  // October - higher
            11 => 0.03,  // November - normal
            12 => 0.10   // December - highest (holiday season)
        );
        
        $variation_factor = isset($seasonal_factors[$target_month]) ? $seasonal_factors[$target_month] : 0.02;
        
        // Add some random variation (±5%)
        $random_variation = (mt_rand(-50, 50) / 1000); // -5% to +5%
        
        return $base_amount * ($variation_factor + $random_variation);
    }
}
