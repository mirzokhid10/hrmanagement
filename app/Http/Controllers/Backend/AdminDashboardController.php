<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeInsight;
use App\Models\TimeOff;
use App\Scopes\TenantScope;
use App\Services\RecruitmentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    protected $recruitmentService;

    // Inject the service
    public function __construct(RecruitmentService $recruitmentService)
    {
        $this->recruitmentService = $recruitmentService;
    }

    public function dashboard(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $isAdmin = $user->isAdmin();

        // 1. Determine Company ID for Queries
        // If Admin: Use the filter from URL (or null for all).
        // If Tenant: Force their own company ID.
        $companyIdForQuery = $isAdmin ? $request->get('company_id') : $user->company_id;

        // 2. Fetch Companies (Only for Admin Dropdown)
        $companies = $isAdmin ? Company::withoutGlobalScopes()->get() : collect();
        $selectedCompany = $request->filled('company_id') ? $companies->find($request->company_id) : null;

        // 3. Fetch Key Stats (Using helper scope for consistency)
        $applyScope = function ($query) use ($isAdmin, $companyIdForQuery) {
            if ($isAdmin) {
                $query->withoutGlobalScope(TenantScope::class);
            }
            if ($companyIdForQuery) {
                $query->where('company_id', $companyIdForQuery);
            }
            return $query;
        };

        $stats = [
            'total_employees' => $applyScope(Employee::query())->count(),
            'new_hires'       => $applyScope(Employee::query())->where('created_at', '>=', now()->subDays(30))->count(),
            'job_applicants'  => $applyScope(Candidate::query())->count(),
            'on_leave_today'  => class_exists(TimeOff::class)
                ? $applyScope(TimeOff::query())
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->count()
                : 0,
        ];

        // 4. Fetch Retention Risks
        $riskInsights = EmployeeInsight::query()
            ->when($isAdmin, function ($query) {
                $query->withoutGlobalScope(TenantScope::class)
                    ->with(['employee' => fn($q) => $q->withoutGlobalScope(TenantScope::class)->with('company')]);
            }, function ($query) {
                $query->with('employee.company');
            })
            ->when($companyIdForQuery, fn($q) => $q->where('company_id', $companyIdForQuery))
            ->orderByDesc('score')
            ->take(5)
            ->get();

        // 5. Fetch Interviews using YOUR Service
        // We pass $companyIdForQuery which is correctly set for both Admin (filter) and Tenant (auth)
        $interviews = $this->recruitmentService->getUpcomingInterviews($companyIdForQuery, 5);

        return view('admin.dashboard', compact(
            'stats',
            'riskInsights',
            'interviews', // Passed from service
            'companies',
            'selectedCompany',
            'isAdmin'
        ));
    }
}
