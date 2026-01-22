@extends('admin.layouts.master')

@section('content')
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="clearfix">
            <h1 class="app-page-title">Announcements</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Announcements</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        {{-- LEFT: Create Form --}}
        <div class="col-md-4">
            <div class="card p-4">
                <div class="card-header border-0 px-0 pt-0">
                    <h5 class="card-title">📢 Post Announcement</h5>
                </div>
                <div class="card-body px-0">
                    <form action="{{ route('admin.announcements.store') }}" method="POST">
                        @csrf

                        {{-- 1. COMPANY SELECTOR (For Admin) --}}
                        @if(Auth::user()->isAdmin())
                            <div class="mb-3">
                                <label class="form-label">Company <span class="text-danger">*</span></label>
                                <select name="company_id" id="company_id" class="form-select" onchange="loadCompanyData(this.value)" required>
                                    <option value="" selected disabled>Select Company</option>
                                    @foreach(\App\Models\Company::all() as $company)
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" id="company_id" value="{{ Auth::user()->company_id }}">
                        @endif

                        <div class="mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Office Closed on Friday" required>
                        </div>

                        {{-- 2. AUDIENCE SELECTOR (Dropdown Style) --}}
                        <div class="mb-3">
                            <label class="form-label">Audience</label>
                            <select name="audience_type" id="audience_type" class="form-select" onchange="toggleAudience()">
                                <option value="company">🏢 Everyone (All Company)</option>
                                <option value="department">👥 Specific Department</option>
                                <option value="employees">👤 Specific Employees</option>
                            </select>
                        </div>

                        {{-- 3. DEPARTMENT SELECTOR --}}
                        <div class="mb-3" id="dept_wrapper" style="display: none;">
                            <label class="form-label">Select Department <span class="text-danger">*</span></label>
                            <select name="department_id" id="department_id" class="form-select" onchange="loadEmployeesInDept()">
                                <option value="" selected disabled>Choose...</option>
                                {{-- Populated via JS or initial load --}}
                                @if(!Auth::user()->isAdmin())
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        {{-- 4. EMPLOYEE SELECTOR (Hidden by default) --}}
                        <div class="mb-3" id="emp_wrapper" style="display: none;">
                            <label class="form-label">Select Employees <span class="text-danger">*</span></label>
                            <select name="employee_ids[]" id="employee_ids" class="form-select" multiple size="5">
                                {{-- Populated via JS --}}
                            </select>
                            <div class="form-text text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Content <span class="text-danger">*</span></label>
                            <textarea name="content" class="form-control" rows="5" placeholder="Write your message here..." required></textarea>
                        </div>

                        <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="send_to_telegram" value="1" id="telegramCheck" checked>
                            <label class="form-check-label" for="telegramCheck">
                                <i class="fa-brands fa-telegram text-info"></i> Send to Telegram Bot
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fi fi-rr-paper-plane me-1"></i> Post Announcement
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- RIGHT: Feed --}}
        <div class="col-md-8">
            {{-- Loop through announcements --}}
                <div class="card mb-3">
                    <div class="card-body">
                    @foreach($announcements as $item)
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title mb-1">{{ $item->title }}</h5>
                                <div class="text-muted small mb-3">
                                    <i class="fi fi-rr-calendar me-1"></i> {{ $item->created_at->diffForHumans() }}
                                    <span class="mx-2">•</span>
                                    @if($item->audience_type === 'company' || $item->audience_type === 'all')
                                        <span class="badge bg-success-subtle text-success">Everyone</span>
                                    @elseif($item->audience_type === 'department')
                                        <span class="badge bg-info-subtle text-info">
                                            {{ $item->department->name ?? 'Deleted Dept' }}
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning">
                                            Specific People
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Delete Button --}}
                            <form action="{{ route('admin.announcements.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Delete this announcement?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-icon btn-action-danger">
                                    <i class="fi fi-rr-trash"></i>
                                </button>
                            </form>
                        </div>

                        <p class="card-text text-secondary" style="white-space: pre-wrap;">{{ $item->content }}</p>

                        <div class="border-top pt-2 mt-2">
                            <small class="text-muted">Posted by: {{ $item->creator->name ?? 'Unknown' }}</small>
                        </div>
                    @endforeach
                    </div>
                </div>

        </div>
    </div>

    {{-- Include the Scripts --}}
    @include('admin.announcements.partials.scripts')
@endsection
