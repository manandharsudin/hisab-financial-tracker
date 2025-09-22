<?php
/**
 * Analytics class for Hisab Financial Tracker
 */

if (!defined('ABSPATH')) {
    exit;
}

class HisabAnalytics {
    
    private $database;
    
    public function __construct() {
        $this->database = new HisabDatabase();
    }
    
    public function get_monthly_trends($months = 12) {
        $income_trend = $this->database->get_trend_data('income', $months);
        $expense_trend = $this->database->get_trend_data('expense', $months);
        
        return array(
            'income' => $income_trend,
            'expense' => $expense_trend
        );
    }
    
    public function get_category_breakdown($type, $year, $month) {
        return $this->database->get_category_summary($type, $year, $month);
    }
    
    public function get_yearly_analysis($year) {
        $yearly_data = $this->database->get_yearly_summary($year);
        
        $analysis = array(
            'total_income' => 0,
            'total_expense' => 0,
            'average_monthly_income' => 0,
            'average_monthly_expense' => 0,
            'best_month_income' => 0,
            'worst_month_income' => 0,
            'best_month_expense' => 0,
            'worst_month_expense' => 0,
            'monthly_data' => $yearly_data
        );
        
        $income_months = array();
        $expense_months = array();
        
        foreach ($yearly_data as $month => $data) {
            $analysis['total_income'] += $data['income'];
            $analysis['total_expense'] += $data['expense'];
            
            if ($data['income'] > 0) {
                $income_months[] = $data['income'];
            }
            if ($data['expense'] > 0) {
                $expense_months[] = $data['expense'];
            }
        }
        
        if (!empty($income_months)) {
            $analysis['average_monthly_income'] = array_sum($income_months) / count($income_months);
            $analysis['best_month_income'] = max($income_months);
            $analysis['worst_month_income'] = min($income_months);
        }
        
        if (!empty($expense_months)) {
            $analysis['average_monthly_expense'] = array_sum($expense_months) / count($expense_months);
            $analysis['best_month_expense'] = max($expense_months);
            $analysis['worst_month_expense'] = min($expense_months);
        }
        
        $analysis['net_profit'] = $analysis['total_income'] - $analysis['total_expense'];
        
        return $analysis;
    }
    
    public function calculate_growth_rate($data) {
        if (count($data) < 2) {
            return 0;
        }
        
        $first_value = $data[0]->total;
        $last_value = end($data)->total;
        
        if ($first_value == 0) {
            return $last_value > 0 ? 100 : 0;
        }
        
        return (($last_value - $first_value) / $first_value) * 100;
    }
    
    public function get_spending_patterns($months = 6) {
        $patterns = array();
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = date('Y-m-01', strtotime("-{$i} months"));
            $year = date('Y', strtotime($date));
            $month = date('n', strtotime($date));
            
            $income_categories = $this->database->get_category_summary('income', $year, $month);
            $expense_categories = $this->database->get_category_summary('expense', $year, $month);
            
            $patterns[date('Y-m', strtotime($date))] = array(
                'income' => $income_categories,
                'expense' => $expense_categories
            );
        }
        
        return $patterns;
    }
}
