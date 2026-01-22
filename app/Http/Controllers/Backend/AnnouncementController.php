<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\AnnouncementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Department;

class AnnouncementController extends Controller
{
    protected AnnouncementService $service;

    public function __construct(AnnouncementService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $user = Auth::user();

        // Get announcements for this company, ordered by newest
        $announcements = Announcement::with(['creator', 'department'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get departments for the dropdown
        $departments = Department::where('company_id', $user->company_id)->get();

        return view('admin.announcements.index', compact('announcements', 'departments'));
    }

    public function store(Request $request)
    {
        // Debugging: Uncomment if you still get errors to see exactly what failed
        // dd($request->all());

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            // FIX 1: Add 'company' and 'employees' to the allowed list
            'audience_type' => 'required|in:all,company,department,employees',

            // FIX 2: Department is required if audience is 'department' OR 'employees' (since you filter employees by dept)
            'department_id' => 'nullable|required_if:audience_type,department,employees|exists:departments,id',

            // FIX 3: Validate the employee array
            'employee_ids' => 'array|required_if:audience_type,employees',
            'employee_ids.*' => 'exists:employees,id',

            'send_to_telegram' => 'nullable|boolean'
        ]);

        $this->service->createAnnouncement($request->all());

        notify()->success('Announcement posted successfully!');
        return redirect()->back();
    }

    public function destroy(Announcement $announcement)
    {
        if ($announcement->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $this->service->deleteAnnouncement($announcement);
        notify()->success('Announcement deleted.');
        return redirect()->back();
    }
}
