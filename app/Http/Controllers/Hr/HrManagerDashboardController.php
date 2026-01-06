<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class HrManagerDashboardController extends Controller
{

    public function dashboard()
    {
        try {
            if (!Auth::check()) {
                return redirect()->route('login');
            }

            /** @var User $user */
            $user = Auth::user();
            $user->load(['company', 'employee']); // Eager load

            $company = $user->company;

            if (!$company) {
                return redirect()->route('login')
                    ->with('error', 'Your account is not associated with a company.');
            }

            // PERFORMANCE: Cache dashboard data for 5 minutes
            $cacheKey = "dashboard_data_{$user->id}_{$company->id}";

            $dashboardData = Cache::remember($cacheKey, 300, function () use ($company) {
                // Use raw queries for better performance
                $totalEmployees = DB::table('employees')
                    ->where('company_id', $company->id)
                    ->count();

                $activeEmployees = DB::table('employees')
                    ->where('company_id', $company->id)
                    ->where('status', 'active')
                    ->count();

                $departmentsCount = DB::table('departments')
                    ->where('company_id', $company->id)
                    ->count();

                // Get recent employees efficiently
                $employees = Employee::withoutGlobalScopes()
                    ->where('company_id', $company->id)
                    ->where('status', 'active')
                    ->with('department:id,name') // Only load needed columns
                    ->select('id', 'first_name', 'last_name', 'email', 'job_title', 'status', 'department_id')
                    ->orderBy('created_at', 'desc')
                    ->limit(15) // Reduced from 50
                    ->get();

                return [
                    'stats' => [
                        'total_employees' => $totalEmployees,
                        'active_employees' => $activeEmployees,
                        'departments_count' => $departmentsCount,
                        'pending_time_offs' => 0,
                    ],
                    'employees' => $employees,
                ];
            });

            return view('hr.dashboard', [
                'user' => $user,
                'company' => $company,
                'employees' => $dashboardData['employees'],
                'stats' => $dashboardData['stats'],
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard Error: ' . $e->getMessage());

            return response()->view('errors.500', [
                'message' => 'Dashboard failed to load.'
            ], 500);
        }
    }
}
