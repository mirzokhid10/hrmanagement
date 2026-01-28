<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRecruitmentRequest;
use App\Http\Requests\UpdateRecruitmentRequest;
use App\Models\Candidate;
use App\Models\Company;
use App\Models\Department;
use App\Models\Recruitment;
use App\Services\Interfaces\RecruitmentServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\HHruService;
use Illuminate\Support\Facades\Storage;
use App\Services\AI\GeminiService;

class RecruitmentController extends Controller
{

    protected RecruitmentServiceInterface $recruitmentService;

    public function __construct(RecruitmentServiceInterface $recruitmentService)
    {
        $this->recruitmentService = $recruitmentService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $companyIdForQuery = $user->isAdmin() ? null : $user->company_id;

        $stats = $this->recruitmentService->getStats($companyIdForQuery);

        $recruitments = $this->recruitmentService->getLatestPublishedRecruitments($companyIdForQuery, 4);

        $candidates = $this->recruitmentService->getRecentCandidates($companyIdForQuery, 10);

        // ✅ FETCH DEPARTMENTS
        // If Admin: Fetch all (or empty if you want them to select company first)
        // If HR: Fetch only their company's departments
        $departments = $user->isAdmin()
            ? Department::all() // Or Department::with('company')->get() to show "IT (Acme Corp)"
            : Department::where('company_id', $user->company_id)->get();

        $companies = $user->isAdmin() ? Company::all() : collect();

        $upcomingInterviews = $this->recruitmentService->getUpcomingInterviews($companyIdForQuery, 5);

        return view('admin.recruitment.index', compact('stats', 'recruitments', 'candidates', 'companies', 'departments', 'upcomingInterviews'));
    }

    public function list(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $companyIdForQuery = $user->isAdmin() ? null : $user->company_id;

        // Get 15 per page for the table
        $recruitments = $this->recruitmentService->getPaginatedRecruitments($companyIdForQuery, 10);

        // $candidates = Candidate::where('recruitment_id', $recruitmentId)
        //     ->where('status', 'active')
        //     ->get();

        return view('admin.recruitment.list', compact('recruitments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Recruitment $recruitment, HHruService $hhService)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Security Check (Redundant if using Middleware/Request auth, but good for safety)
        if (!$user->isAdmin() && $recruitment->company_id !== $user->company_id) {
            abort(403, 'Unauthorized action.');
        }

        // Prepare Dropdowns
        $companies = $user->isAdmin() ? Company::all() : collect([$user->company]);

        // Ensure we only show departments for the recruitment's company
        $departments = Department::where('company_id', $recruitment->company_id)->get();

        $hhRoles = $hhService->getProfessionalRoles();

        return view('admin.recruitment.create', compact('recruitment', 'companies', 'departments', 'hhRoles'));
    }

    public function store(StoreRecruitmentRequest $request, HHruService $hhService)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $data = $request->validated();

        // Determine the Company ID for the Service
        $companyIdForService = null;
        if ($user->isAdmin() && isset($data['company_id'])) {
            $companyIdForService = (int) $data['company_id'];
        }

        try {
            // 1. Create the Local Recruitment
            $recruitment = $this->recruitmentService->createRecruitment($data, $companyIdForService);

            // 2. Check if "Post to HH" was checked
            if ($request->has('post_to_hh') && $request->post_to_hh == '1') {
                try {
                    $hhService->postVacancy($recruitment);
                    notify()->success('Job created and posted to HH.ru!');
                } catch (\Exception $e) {
                    notify()->warning('Job created locally, but HH.ru posting failed: ' . $e->getMessage());
                }
            } else {
                notify()->success('Job Vacancy created successfully.');
            }

            return redirect()->route('admin.recruitment.index');
        } catch (\Exception $e) {
            notify()->error('Error caught:', $e);
        }
    }

    /**
     * Show the form for editing the specified Job Vacancy.
     */
    public function edit(Recruitment $recruitment, HHruService $hhService)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Security Check (Redundant if using Middleware/Request auth, but good for safety)
        if (!$user->isAdmin() && $recruitment->company_id !== $user->company_id) {
            abort(403, 'Unauthorized action.');
        }

        // Prepare Dropdowns
        $companies = $user->isAdmin() ? Company::all() : collect([$user->company]);

        // Ensure we only show departments for the recruitment's company
        $departments = Department::where('company_id', $recruitment->company_id)->get();

        $hhRoles = $hhService->getProfessionalRoles();

        return view('admin.recruitment.edit', compact('recruitment', 'companies', 'departments', 'hhRoles'));
    }

    /**
     * Update the specified Job Vacancy.
     */
    public function update(UpdateRecruitmentRequest $request, Recruitment $recruitment)
    {
        $data = $request->validated();

        try {
            // Process Key Skills (String -> Array) if present
            if (isset($data['key_skills_input'])) {
                $data['key_skills'] = array_map('trim', explode(',', $data['key_skills_input']));
                unset($data['key_skills_input']); // Remove raw input
            }

            // Perform Update
            // We use the model directly here as it's a simple update.
            // If you add updateRecruitment to your ServiceInterface later, you can swap this out.
            $recruitment->update($data);

            notify()->success('Job Vacancy updated successfully.');
            return redirect()->route('admin.recruitment.index');
        } catch (\Exception $e) {
            notify()->error('Failed to update job vacancy.');
            return redirect()->back()->withInput();
        }
    }

    public function updateStatus(Request $request, Recruitment $recruitment)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isAdmin() && $recruitment->company_id !== $user->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|in:published,draft,closed'
        ]);

        $recruitment->update(['status' => $request->status]);

        // ✅ Return JSON for AJAX
        return response()->json([
            'message' => 'Status updated successfully',
            'status'  => $request->status,
            'label'   => ucfirst($request->status)
        ]);
    }

    /**
     * Remove the specified Job Vacancy.
     */
    public function destroy(Recruitment $recruitment)
    {
        try {
            // The service handles the security check for deletion
            $this->recruitmentService->deleteRecruitment($recruitment);

            notify()->success('Job Vacancy deleted successfully.');
            return redirect()->back();
        } catch (\Exception $e) {
            notify()->error('Failed to delete job vacancy.');
            return redirect()->back();
        }
    }

    /**
     * AJAX: Get Departments for a specific Company (For Admin usage in Forms).
     */
    public function getDepartments(Request $request)
    {
        $request->validate(['company_id' => 'required|exists:companies,id']);

        $departments = Department::where('company_id', $request->company_id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($departments);
    }

    /**
     * Update the status of a specific candidate.
     */
    public function updateCandidateStatus(Request $request, Candidate $candidate)
    {
        /** @var \App\Models\User $user */
        // 🔒 SECURITY CHECK: Multi-Tenancy
        // Ensure the HR manager can only update candidates FOR THEIR OWN COMPANY.
        // Admins can update anyone.
        $user = Auth::user();
        if (!$user->isAdmin() && $candidate->company_id !== Auth::user()->company_id) {
            notify()->error('Unauthorized action.');
        }

        // Validate Status
        $request->validate([
            'status' => 'required|in:pending,shortlisted,interviewed,hired,rejected'
        ]);

        $candidate->update(['status' => $request->status]);

        // Optional: Trigger Notification (Email/Telegram) here
        // $this->recruitmentService->notifyCandidateStatusChange($candidate);

        return back()->with('success', "Candidate status updated to " . ucfirst($request->status));
    }

    /**
     * Download the candidate's resume.
     */
    public function downloadCandidateResume(Candidate $candidate)
    {
        /** @var \App\Models\User $user */
        // 🔒 SECURITY CHECK
        $user = Auth::user();
        if (!$user->isAdmin() && $candidate->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        if (!$candidate->resume_path || !Storage::exists($candidate->resume_path)) {
            return back()->with('error', 'Resume file not found.');
        }

        // Force download with a nice filename (e.g., "Resume_Jamshid_Sobirov.pdf")
        $extension = pathinfo($candidate->resume_path, PATHINFO_EXTENSION);
        $filename = "Resume_{$candidate->first_name}_{$candidate->last_name}.{$extension}";

        return Storage::download($candidate->resume_path, $filename);
    }

    public function updateInterviewSchedule(Request $request, Candidate $candidate)
    {
        /** @var \App\Models\User $user */
        // Security Check
        $user = Auth::user();
        if (!$user->isAdmin() && $candidate->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $request->validate([
            'interview_scheduled_at' => 'nullable|date',
        ]);

        $candidate->update([
            'interview_scheduled_at' => $request->interview_scheduled_at,
            // Optional: Auto-change status to 'interviewed' if a date is set
            'status' => $request->interview_scheduled_at ? 'interviewed' : $candidate->status
        ]);

        return back()->with('success', 'Interview schedule updated successfully.');
    }

    public function analyzeCandidate(GeminiService $ai, $candidateId)
    {
        $candidate = Candidate::find($candidateId);

        // The Prompt
        $prompt = "
        Analyze this candidate for the position of Senior Laravel Developer.
        Resume Text: {$candidate->resume_text}

        Return JSON with:
        - score (0-100)
        - summary (string, 1 sentence in Uzbek)
        - skills_found (array)";

        // The Magic
        $analysis = $ai->askJson($prompt);

        // Result is already an array!
        // $analysis['score'] -> 85
        // $analysis['summary'] -> "Nomzod Laravel bo'yicha kuchli bilimga ega..."

        $candidate->update([
            'ai_score' => $analysis['score'] ?? 0,
            'ai_data' => $analysis
        ]);
    }
}
