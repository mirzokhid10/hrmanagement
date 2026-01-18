<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\Employee;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $date = $request->input('date', Carbon::today()->toDateString());
        $departmentId = $request->input('department_id');

        $query = Attendance::where('company_id', tenant()->id)
            ->where('date', $date)
            ->with(['employee.department']);

        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        $attendances = $query->orderBy('check_in_time')->get();

        $summary = [
            'total' => Employee::where('company_id', tenant()->id)->count(),
            'present' => $attendances->whereNotNull('check_in_time')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'absent' => Employee::where('company_id', tenant()->id)->count() - $attendances->count(),
        ];

        return view('admin.attendance.index', compact('attendances', 'summary', 'date'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Attendance $attendance)
    {
        $this->authorize('view', $attendance);
        return view('admin.attendance.show', compact('attendance'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
