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
            
            $projections[] = array(
                'month' => $projection_month,
                'year' => $projection_year,
                'month_name' => date('F Y', strtotime($projection_date)),
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
        
        // Calculate average growth rate
        $growth_rate = $this->analytics->calculate_growth_rate($trend_data);
        
        // Get the most recent amount
        $latest_amount = end($trend_data)->total;
        
        // Apply seasonal adjustment if we have enough data
        $seasonal_adjustment = $this->get_seasonal_adjustment($trend_data, $target_month);
        
        // Calculate projection
        $projected_amount = $latest_amount * (1 + ($growth_rate / 100)) * $seasonal_adjustment;
        
        return max(0, $projected_amount); // Ensure non-negative
    }
    
    private function get_seasonal_adjustment($trend_data, $target_month) {
        if (count($trend_data) < 12) {
            return 1.0; // No seasonal adjustment if less than a year of data
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
        
        // Calculate overall average
        $overall_average = array_sum($monthly_averages) / count($monthly_averages);
        
        // Calculate seasonal adjustment for target month
        if (isset($monthly_averages[$target_month]) && $overall_average > 0) {
            return $monthly_averages[$target_month] / $overall_average;
        }
        
        return 1.0;
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
}
