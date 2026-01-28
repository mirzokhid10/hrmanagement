<?php

namespace App\Services\Analytics;

use App\Models\Employee;
use App\Models\EmployeeInsight;
use App\Services\AI\GeminiService;
use Carbon\Carbon;

class TurnoverRiskService
{
    public function __construct(protected GeminiService $ai) {}

    /**
     * Analyze a single employee and save the result.
     */
    public function analyze(Employee $employee): EmployeeInsight
    {
        // 1. Calculate Mathematical Score (0-100)
        $analysis = $this->calculateMathScore($employee);

        // 2. If Risk is Medium/High, ask AI for advice
        $aiAdviceUz = null;
        $aiAdviceRu = null;

        if ($analysis['score'] >= 50) {
            $context = $this->prepareAiContext($employee, $analysis);

            // Ask in Uzbek
            $aiAdviceUz = $this->ai->ask(
                "You are an HR Expert. Context: {$context}. Write 2 sentences of advice for the manager in Uzbek language. Be professional."
            );

            // Ask in Russian (Optional, can be done in a separate job)
            // $aiAdviceRu = $this->ai->ask("... in Russian ...");
        }

        // 3. Save/Update Database
        return EmployeeInsight::updateOrCreate(
            [
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
                'type' => 'turnover_risk',
            ],
            [
                'score' => $analysis['score'],
                'risk_level' => $analysis['level'],
                'factors' => $analysis['factors'],
                'ai_analysis_uz' => $aiAdviceUz,
            ]
        );
    }

    /**
     * Pure PHP Logic - Costs $0
     */
    private function calculateMathScore(Employee $employee): array
    {
        $score = 0;
        $factors = [];

        // Factor 1: Tenure (The "2-Year Itch")
        $yearsWorked = $employee->hire_date ? $employee->hire_date->diffInYears(now()) : 0;

        if ($yearsWorked < 0.5) {
            $score += 20; // New hire risk (probation)
            $factors[] = "New Hire (Probation)";
        } elseif ($yearsWorked > 2 && $yearsWorked < 3) {
            $score += 15; // 2-year mark is common for leaving
            $factors[] = "2-Year Tenure Mark";
        }

        // Factor 2: Salary (Mock logic - in real app, compare to dept average)
        // If salary is low (< 3M UZS) - just an example
        if ($employee->salary && $employee->salary < 4000000) {
            $score += 25;
            $factors[] = "Low Compensation Tier";
        }

        // Factor 3: Burnout (Time Off)
        // Check if they took leave in last 6 months
        $hasTakenLeave = $employee->timeOffs()
            ->where('status', 'approved')
            ->where('end_date', '>=', now()->subMonths(6))
            ->exists();

        if (!$hasTakenLeave && $yearsWorked > 0.5) {
            $score += 30;
            $factors[] = "No Vacation in 6 Months (Burnout)";
        }

        // Factor 4: Commute (Simple check if address is long)
        // This is a rough proxy for distance if we don't have geo-coords
        if ($employee->address && strlen($employee->address) > 50) {
            $score += 10;
            $factors[] = "Complex Commute";
        }

        // Cap score at 100
        $score = min($score, 100);

        // Determine Level
        $level = match (true) {
            $score >= 80 => 'critical',
            $score >= 50 => 'high',
            $score >= 30 => 'medium',
            default => 'low',
        };

        return [
            'score' => $score,
            'level' => $level,
            'factors' => $factors
        ];
    }

    private function prepareAiContext(Employee $employee, array $analysis): string
    {
        $factorString = implode(", ", $analysis['factors']);
        return "Employee {$employee->first_name} (Job: {$employee->job_title}) has a turnover risk score of {$analysis['score']}/100. Key risk factors: {$factorString}. Tenure: {$employee->hire_date->diffForHumans()}.";
    }
}
