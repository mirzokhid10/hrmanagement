<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\TimeOff;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        /** @var App\Models\User */
        $user = Auth::user();
        $isAdmin = $user->isAdmin(); // Or $user->hasRole('super-admin')

        // 1. Determine Scope (Company)
        // If Admin: Allow filtering. If HR: Enforce their company.
        $companyId = $isAdmin ? $request->get('company_id') : $user->company_id;

        // 2. Determine Month (Default to current month)
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $startOfMonth = Carbon::parse($selectedMonth)->startOfMonth();
        $endOfMonth = Carbon::parse($selectedMonth)->endOfMonth();
        $daysInMonth = $startOfMonth->daysInMonth;

        // 3. Fetch Employees
        $employees = Employee::query()
            ->with(['department', 'company'])
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->orderBy('first_name')
            ->get();

        // 4. Fetch Attendances for the WHOLE month (Eager Loading)
        // We group by 'employee_id' and then 'date' for instant lookup in the View
        $attendances = Attendance::query()
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->get()
            ->groupBy(function ($item) {
                return $item->employee_id . '_' . $item->date->format('j'); // Key: "5_1" (Employee 5, Day 1)
            });

        // 5. Fetch Approved Leaves (TimeOffs)
        // We need to know if someone is Absent or on Leave
        $leaves = TimeOff::query()
            ->where('status', 'approved')
            ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                    ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth]);
            })
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->get();

        // 6. Map Leaves for fast lookup: [employee_id][day] => true
        $leaveMap = [];
        foreach ($leaves as $leave) {
            // Loop through each day of the leave and mark it
            $period = \Carbon\CarbonPeriod::create($leave->start_date, $leave->end_date);
            foreach ($period as $date) {
                if ($date->month == $startOfMonth->month) {
                    $leaveMap[$leave->employee_id][$date->day] = $leave->type; // e.g. "Sick", "Vacation"
                }
            }
        }

        // 7. Companies list for Admin Filter
        $companies = $isAdmin ? Company::all() : collect();

        return view('admin.attendance.index', compact(
            'employees',
            'attendances',
            'leaveMap',
            'daysInMonth',
            'selectedMonth',
            'companies',
            'companyId',
            'isAdmin'
        ));
    }
}
