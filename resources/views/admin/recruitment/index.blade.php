@extends('admin.layouts.master')

@section('content')
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="clearfix">
            <h1 class="app-page-title">Recruitment</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Recruitment</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-middle justify-center gap-2">
            @if (Auth::user()->isAdmin())
                <button type="button" class="btn btn-dark waves-effect waves-light" data-bs-toggle="modal"
                    data-bs-target="#hhConnectionsModal">
                    <i class="fi fi-brands-headhunter me-1"></i> Manage HH.ru
                </button>

                {{-- SCENARIO 2: HR MANAGER (Single Company) --}}
            @elseif(Auth::user()->company)
                @if (!Auth::user()->company->hh_access_token)
                    <a href="{{ route('admin.hh.connect') }}" class="btn btn-danger waves-effect waves-light">
                        <i class="fi fi-brands-headhunter me-1"></i> Connect HH.ru
                    </a>
                @else
                    <button class="btn btn-outline-success disabled">
                        <i class="fi fi-ss-check-circle me-1"></i> HH.ru Connected
                    </button>
                @endif
            @endif
            <a href="{{ route('admin.recruitment.create') }}" class="btn btn-primary waves-effect waves-light">
                <i class="fa-solid fa-plus me-1"></i> Create Job
            </a>
        </div>
    </div>

    <div class="row">
        <!-- STATS CARDS -->
        <div class="col-xxl-5">
            <div class="row">
                <!-- Total Job Openings -->
                <div class="col-xxl-6 col-lg-4 col-sm-6">
                    <div class="card card-action action-border-secondary p-1 position-relative">
                        <div class="card-body d-flex gap-2 align-items-center p-4">
                            <div class="clearfix pe-2 text-secondary"><i class="fa-solid fa-bag-shopping fs-1"></i></div>
                            <div class="clearfix">
                                <div class="mb-1">Total Job Openings</div>
                                <h3 class="mb-0 fw-bold fs-24">{{ $stats['total_openings'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Total Applications -->
                <div class="col-xxl-6 col-lg-4 col-sm-6">
                    <div class="card card-action action-border-warning p-1 position-relative">
                        <div class="card-body d-flex gap-2 align-items-center p-4">
                            <div class="clearfix pe-2 text-warning"><i class="fa-regular fa-file-lines fs-1"></i></i></div>
                            <div class="clearfix">
                                <div class="mb-1">Total Application</div>
                                <h3 class="mb-0 fw-bold fs-24">{{ $stats['total_applications'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Shortlisted -->
                <div class="col-xxl-6 col-lg-4 col-sm-6">
                    <div class="card card-action action-border-info p-1 position-relative">
                        <div class="card-body d-flex gap-2 align-items-center p-4">
                            <div class="clearfix pe-2 text-info"><i class="fa-solid fa-user-group fs-1"></i></div>
                            <div class="clearfix">
                                <div class="mb-1">Shortlisted</div>
                                <h3 class="mb-0 fw-bold fs-24">{{ $stats['shortlisted'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Interviewed -->
                <div class="col-xxl-6 col-lg-4 col-sm-6">
                    <div class="card card-action action-border-primary p-1 position-relative">
                        <div class="card-body d-flex gap-2 align-items-center p-4">
                            <div class="clearfix pe-2 text-primary"><i class="fa-solid fa-headphones fs-1"></i></div>
                            <div class="clearfix">
                                <div class="mb-1">Interviewed</div>
                                <h3 class="mb-0 fw-bold fs-24">{{ $stats['interviewed'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Rejected -->
                <div class="col-xxl-6 col-lg-4 col-sm-6">
                    <div class="card card-action action-border-danger p-1 position-relative">
                        <div class="card-body d-flex gap-2 align-items-center p-4">
                            <div class="clearfix pe-2 text-danger"><i class="fa-solid fa-user-xmark fs-1"></i></div>
                            <div class="clearfix">
                                <div class="mb-1">Rejected</div>
                                <h3 class="mb-0 fw-bold fs-24">{{ $stats['rejected'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Hired -->
                <div class="col-xxl-6 col-lg-4 col-sm-6">
                    <div class="card card-action action-border-success p-1 position-relative">
                        <div class="card-body d-flex gap-2 align-items-center p-4">
                            <div class="clearfix pe-2 text-success"><i class="fa-solid fa-thumbs-up fs-1"></i></div>
                            <div class="clearfix">
                                <div class="mb-1">Hired</div>
                                <h3 class="mb-0 fw-bold fs-24">{{ $stats['hired'] }}</h3>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- INTERVIEW SCHEDULE (Static for now, dynamic later) -->
        <div class="col-xxl-7">
            <div class="card bg-gray bg-opacity-10 border-0 shadow-none">
                <div class="card-header d-flex gap-3 flex-wrap align-items-center justify-content-between border-0 pb-0">
                    <h6 class="card-title mb-0">Interview Schedule</h6>
                    <div class="clearfix d-flex align-items-center">
                        <a href="javascript:void(0);" class="btn-link me-4">View All</a>
                        <div class="dropdown">
                            <button class="btn dropdown-toggle btn-white btn-shadow waves-effect btn-sm" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Last Month
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Today</a></li>
                                <li><a class="dropdown-item" href="#">This Week</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0);">Published</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0);">Date Modifed</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body px-3 pb-2">
                    <div class="row gx-2">
                        @if ($upcomingInterviews->isEmpty())
                            <div class="col-12 text-center">
                                <div class="avatar avatar-lg bg-white rounded-circle mb-3 mx-auto shadow-sm">
                                    <i class="fi fi-rr-calendar-clock fs-2 text-muted"></i>
                                </div>
                                <h6 class="text-muted">No interviews scheduled.</h6>
                                <p class="small text-muted">Use the candidate table to schedule one.</p>
                            </div>
                        @else
                            {{--
                                🚀 LOGIC: Split the collection into 2 equal chunks.
                                If you have 5 items: Left gets 3, Right gets 2.
                            --}}
                            @foreach ($upcomingInterviews->splitIn(2) as $chunk)
                                <div class="col-md-6">
                                    <ul class="list-group list-group-smooth list-group-unlined mb-0">
                                        @foreach ($chunk as $interview)
                                            <li
                                                class="list-group-item d-flex justify-content-between align-items-center mb-2">
                                                <!-- Avatar -->
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar rounded-circle me-2">
                                                        <img src="{{ $interview->photo_path ? asset('storage/' . $interview->photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($interview->full_name) }}"
                                                            alt="{{ $interview->full_name }}">
                                                    </div>
                                                    <div class="ms-1 me-auto">
                                                        <h6 class="mb-0 text-truncate" style="max-width: 120px;">
                                                            {{ $interview->first_name }} {{ $interview->last_name }}
                                                        </h6>
                                                        <small class="text-body text-truncate d-block"
                                                            style="max-width: 120px;">
                                                            {{ $interview->recruitment->title ?? 'Candidate' }}
                                                        </small>
                                                    </div>
                                                </div>

                                                <!-- Dynamic Badge (Time/Date) -->
                                                @php
                                                    $date = $interview->interview_scheduled_at;
                                                    $isToday = $date->isToday();
                                                    $isTomorrow = $date->isTomorrow();

                                                    // Logic for Badge Color & Text
                                                    if ($isToday) {
                                                        // If today, show Time (e.g., 12:30 PM)
                                                        // Green if future time today, Red if passed
                                                        $text = $date->format('h:i A');
                                                        $badgeClass = $date->isPast()
                                                            ? 'bg-danger-subtle text-danger'
                                                            : 'bg-success-subtle text-success';
                                                    } elseif ($isTomorrow) {
                                                        $text = 'Tomorrow';
                                                        $badgeClass = 'bg-primary-subtle text-primary';
                                                    } else {
                                                        // Future date
                                                        $text = $date->format('d M');
                                                        $badgeClass = 'bg-secondary-subtle text-secondary';
                                                    }
                                                @endphp

                                                <span class="badge badge-lg {{ $badgeClass }}">
                                                    {{ $text }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- VACANCY CARDS SECTION -->
        <div class="col-lg-12 my-3 d-flex justify-content-between align-middle">
            <h5 class="fw-bold mb-0">
                Current Vacancy
                <span class="text-primary ms-1 text-2xs">
                    {{ $stats['total_openings'] }} Active Jobs
                </span>
            </h5>
            <a href="{{ route('admin.recruitment.list') }}"
                class="btn btn-sm btn-outline-primary waves-effect waves-light">
                View All Jobs <i class="fi fi-rr-arrow-right ms-1"></i>
            </a>
        </div>

        @foreach ($recruitments as $item)
            <div class="col-xxl-3 col-md-6">
                <div class="card card-action action-elevate bg-primary-subtle border-0 shadow-none">
                    <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0 p-3">
                        <span
                            class="badge {{ $item->status === 'Active'
                                ? 'bg-success-subtle text-success'
                                : ($item->status === 'Inactive'
                                    ? 'bg-secondary-subtle text-secondary'
                                    : ($item->status === 'Probation'
                                        ? 'bg-warning-subtle text-warning'
                                        : ($item->status === 'Terminated'
                                            ? 'bg-danger-subtle text-danger'
                                            : 'bg-primary-subtle text-primary'))) }}">{{ $item->status }}</span>
                        <div class="clearfix">
                            <div class="btn-group">
                                <button class="btn btn-white btn-sm btn-shadow btn-icon waves-effect dropdown-toggle"
                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item cursor-pointer flex items-center gap-2"
                                            href="javascript:void(0);"
                                            onclick="copyToClipboard('{{ $item->telegram_apply_link }}')">
                                            Copy Telegram Link
                                        </a>
                                    </li>

                                    <li>
                                        <a href="{{ route('admin.recruitment.edit', $item->id) }}"
                                            class="dropdown-item edit-employee-btn"> Edit
                                        </a>
                                    </li>
                                    <li>
                                        <form action="{{ route('admin.recruitment.destroy', $item->id) }}" method="POST"
                                            style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm border-none dropdown-item"
                                                onclick="return confirm('Are you sure you want to delete {{ $item->title }}?');">
                                                Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-3 align-items-center mb-4">

                            <div class="clearfix">
                                <h6 class="mb-1 text-sm">{{ $item->title }}</h6>
                                <ul class="list-inline list-inline-disc d-flex mb-0">
                                    <li>{{ $item->job_type }}</li>
                                    <li>{{ $item->location }}</li>
                                </ul>
                            </div>
                        </div>
                        <div class="bg-body p-3 rounded-3 mb-3 d-flex">
                            <div class="text-center w-50">
                                <h2 class="fs-1 fw-bold mb-1">{{ $item->candidates_count }}</h2>
                                <span class="text-primary">Applied</span>
                            </div>
                            <div class="vr"></div>
                            <div class="text-center w-50">
                                <h2 class="fs-1 fw-bold mb-1">{{ $item->new_candidates_count }}</h2>
                                <span class="text-primary">New</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between gap-2 pt-1 mb-3">
                            <div class="text-start">
                                <span class="text-1xs">Salary</span>
                                <span
                                    class="text-sm text-dark d-block fw-semibold">{{ $item->salary_range ?? 'Negotiable' }}</span>
                            </div>
                        </div>
                        <a href="#" class="btn btn-primary waves-effect waves-light w-100">See Job Post</a>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- CANDIDATE TABLE (Renamed from Employee Leave to match data) -->
        <div class="col-lg-12">
            <div class="card overflow-hidden">
                <div class="card-header d-flex gap-3 flex-wrap align-items-center justify-content-between border-0 pb-0">
                    <h6 class="card-title mb-0">Candidate Applications</h6>
                </div>
                <div class="card-body p-2">
                    <table id="dt_PageEmployeeLeave" class="table display table-row-rounded">
                        <thead class="table-light">
                            <tr>
                                <th class="minw-200px">Candidate Name</th>

                                <!-- Admin sees Company -->
                                @if (Auth::user()->isAdmin())
                                    <th class="minw-150px">Company</th>
                                @endif

                                <th class="minw-200px">Applied For</th>
                                <th class="minw-150px">Contact</th>
                                <th class="minw-150px">Status</th>
                                <th class="minw-200px">Interview Schedule</th>
                                <th class="minw-100px text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($candidates as $candidate)
                                <tr>
                                    <!-- 1. NAME & AVATAR & SOURCE -->
                                    <td>
                                        <div class="d-flex align-items-center mw-175px">
                                            <div class="position-relative">
                                                <div class="avatar avatar-xs rounded-circle">
                                                    <img src="{{ $candidate->photo_path ? asset('storage/' . $candidate->photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($candidate->full_name) }}"
                                                        alt="{{ $candidate->full_name }}">
                                                </div>
                                                <!-- Source Badge (Small icon on corner) -->
                                                @if ($candidate->source === 'telegram')
                                                    <span
                                                        class="position-absolute top-0 start-100 d-flex align-middle justify-center translate-middle badge rounded-pill bg-primary border border-white p-0"
                                                        style="padding: 0 !important;" title="Telegram Bot">
                                                        <i class="fa-brands fa-telegram fs-20 my-auto"></i>
                                                    </span>
                                                @elseif($candidate->source === 'hh_ru')
                                                    <span
                                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white p-0"
                                                        title="HH.ru">
                                                        <span class="fs-10">HH</span>
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="ms-3">
                                                <div class="fw-bold text-dark">{{ $candidate->first_name }}
                                                    {{ $candidate->last_name }}</div>
                                                <div class="small text-muted">Applied:
                                                    {{ $candidate->created_at->diffForHumans() }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- 2. COMPANY (Admin Only) -->
                                    @if (Auth::user()->isAdmin())
                                        <td>
                                            <span class="badge bg-light text-dark border fs-14">
                                                {{ $candidate->company->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                    @endif

                                    <!-- 3. DEPARTMENT / JOB -->
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-primary">
                                                {{ $candidate->recruitment->title ?? 'General' }}
                                            </span>
                                            <span class="small text-muted">
                                                <i class="fi fi-rr-briefcase me-1"></i>
                                                {{ $candidate->recruitment->department->name ?? 'No Dept' }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- 4. CONTACT -->
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fs-13">{{ $candidate->phone }}</span>
                                            <span class="fs-12 text-muted">{{ $candidate->email }}</span>
                                        </div>
                                    </td>

                                    <!-- 5. STATUS DROPDOWN (Functional) -->
                                    <td>
                                        <div class="dropdown select-status">
                                            @php
                                                $statusClasses = [
                                                    'pending' => 'btn-subtle-secondary',
                                                    'shortlisted' => 'btn-subtle-info',
                                                    'interviewed' => 'btn-subtle-primary',
                                                    'hired' => 'btn-subtle-success',
                                                    'rejected' => 'btn-subtle-danger',
                                                ];
                                                $currentClass =
                                                    $statusClasses[$candidate->status] ?? 'btn-subtle-secondary';
                                                $allStatuses = [
                                                    'pending',
                                                    'shortlisted',
                                                    'interviewed',
                                                    'hired',
                                                    'rejected',
                                                ];
                                            @endphp

                                            <button
                                                class="btn btn-sm fs-16 {{ $currentClass }} dropdown-toggle waves-effect waves-light text-capitalize w-100"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                {{ ucfirst($candidate->status) }}
                                            </button>

                                            <ul class="dropdown-menu dropdown-menu-end">
                                                @foreach ($allStatuses as $status)
                                                    <li>
                                                        <form
                                                            action="{{ route('admin.candidates.update-status', $candidate->id) }}"
                                                            method="POST">
                                                            @csrf @method('PATCH')
                                                            <input type="hidden" name="status"
                                                                value="{{ $status }}">
                                                            <button type="submit"
                                                                class="dropdown-item {{ $candidate->status === $status ? 'active' : '' }}">
                                                                @php
                                                                    $dotColor = match ($status) {
                                                                        'hired' => 'text-success',
                                                                        'rejected' => 'text-danger',
                                                                        'interviewed' => 'text-primary',
                                                                        'shortlisted' => 'text-info',
                                                                        default => 'text-secondary',
                                                                    };
                                                                @endphp
                                                                <i
                                                                    class="fi fi-ss-circle-small {{ $dotColor }} me-2"></i>
                                                                {{ ucfirst($status) }}
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </td>

                                    <!-- 6. INTERVIEW SCHEDULE (Dropdown Form) -->
                                    <td>
                                        <div class="dropdown">
                                            @php
                                                $hasInterview = !is_null($candidate->interview_scheduled_at);
                                                // Format: "Jan 25, 14:30"
                                                $displayDate = $hasInterview
                                                    ? $candidate->interview_scheduled_at->format('M d, H:i')
                                                    : 'Schedule Now';

                                                $btnClass = $hasInterview
                                                    ? 'btn-subtle-primary'
                                                    : 'btn-outline-light text-muted';
                                                $icon = $hasInterview ? 'fi-rr-calendar-clock' : 'fi-rr-plus';
                                            @endphp

                                            <button
                                                class="btn btn-sm fs-16 {{ $btnClass }} dropdown-toggle waves-effect w-100"
                                                type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                                aria-expanded="false">
                                                <i class="fi {{ $icon }} me-1"></i> {{ $displayDate }}
                                            </button>

                                            <!-- The Dropdown Content (Form) -->
                                            <div class="dropdown-menu p-3" style="min-width: 250px;">
                                                <form
                                                    action="{{ route('admin.candidates.update-schedule', $candidate->id) }}"
                                                    method="POST">
                                                    @csrf @method('PATCH')

                                                    <label class="form-label small text-muted">Select Date &
                                                        Time</label>
                                                    <input type="datetime-local" name="interview_scheduled_at"
                                                        class="form-control form-control-sm mb-2"
                                                        value="{{ $candidate->interview_scheduled_at?->format('Y-m-d\TH:i') }}">

                                                    <div class="d-flex justify-content-between gap-2">
                                                        @if ($hasInterview)
                                                            <!-- Clear Button -->
                                                            <button type="submit" name="interview_scheduled_at"
                                                                value="" class="btn btn-xs btn-light text-danger">
                                                                Clear
                                                            </button>
                                                        @endif
                                                        <button type="submit" class="btn btn-xs btn-primary w-100">
                                                            Save Schedule
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- 7. ACTIONS -->
                                    <td class="text-end">
                                        <div class="d-flex gap-2 align-middle justify-center">
                                            <!-- Download Resume -->
                                            @if ($candidate->resume_path)
                                                <a href="{{ route('admin.candidates.download', $candidate->id) }}"
                                                    class="btn btn-white btn-sm btn-shadow btn-icon waves-effect"
                                                    target="_blank" title="Download Resume">
                                                    <i class="fa-regular fa-file-lines"></i>
                                                </a>
                                            @endif

                                            <!-- More Actions -->
                                            <div class="dropdown">
                                                <button
                                                    class="btn btn-white btn-sm btn-shadow btn-icon waves-effect dropdown-toggle
                                                    d-flex align-middle justify-center"
                                                    type="button" data-bs-toggle="dropdown">
                                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end ">
                                                    <li><a class="dropdown-item" href="#">View Full Profile</a>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li><a class="dropdown-item text-danger" href="#">Delete
                                                            Application</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ Auth::user()->isAdmin() ? 8 : 7 }}" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="fi fi-rr-search-alt fs-1 text-muted mb-2"></i>
                                            <h6 class="text-muted">No candidates found.</h6>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </div>
        </div>

    </div>


    <!-- CREATE JOB MODAL (Functional) -->
    <div class="modal fade" id="createJobModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-3">
                    <h5 class="modal-title">Create Job</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Added form tag and route -->
                    <form action="{{ route('admin.recruitment.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="jobTitle" class="form-label">Job Title</label>
                            <input type="text" name="title" class="form-control" id="jobTitle"
                                placeholder="Enter job title" required>
                        </div>
                        <div class="mb-3">
                            <label for="jobDepartment" class="form-label">Department</label>
                            <select class="form-select" name="department" id="jobDepartment" required>
                                <option selected disabled>Select department</option>
                                <option value="Human Resources">Human Resources</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Development">Development</option>
                                <option value="Sales">Sales</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="jobType" class="form-label">Job Type</label>
                            <select class="form-select" name="job_type" id="jobType" required>
                                <option selected disabled>Select type</option>
                                <option value="Full-time">Full-time</option>
                                <option value="Part-time">Part-time</option>
                                <option value="Internship">Internship</option>
                                <option value="Contract">Contract</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" id="location"
                                placeholder="Enter location" required>
                        </div>
                        <div class="mb-3">
                            <label for="salaryRange" class="form-label">Salary Range</label>
                            <input type="text" name="salary_range" class="form-control" id="salaryRange"
                                placeholder="8M - 12M UZS">
                        </div>
                        <div class="mb-3">
                            <label for="flatpickr_basic" class="form-label">Application Deadline</label>
                            <input type="date" name="deadline" class="form-control" id="flatpickr_basic">
                        </div>
                        <div class="mb-3">
                            <label for="jobDescription" class="form-label">Job Description</label>
                            <textarea class="form-control" name="description" id="jobDescription" rows="4" placeholder="Enter details"
                                required></textarea>
                        </div>

                        <!-- Checkbox to trigger HH.ru sync -->
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="post_to_hh" value="1"
                                id="postToHH">
                            <label class="form-check-label" for="postToHH">
                                Post to HH.ru automatically
                            </label>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary waves-effect waves-light">Post Job</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection


<!-- ADMIN HH CONNECTIONS MODAL -->
@if (Auth::user()->isAdmin())
    <div class="modal fade" id="hhConnectionsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">HH.ru Connections</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach ($companies as $company)
                            <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                                <div>
                                    <h6 class="mb-0">{{ $company->name }}</h6>
                                    <small class="text-muted">ID: {{ $company->id }}</small>
                                </div>

                                @if ($company->hh_access_token)
                                    <span class="badge bg-success-subtle text-success">
                                        <i class="fi fi-ss-check-circle me-1"></i> Connected
                                    </span>
                                @else
                                    <a href="{{ route('admin.hh.connect', ['company_id' => $company->id]) }}"
                                        class="btn btn-sm btn-outline-danger">
                                        Connect
                                    </a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endif

<script>
    function copyToClipboard(text) {
        if (!navigator.clipboard) {
            // Fallback for older browsers
            var textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand("Copy");
            textArea.remove();
            alert("Link copied to clipboard!");
            return;
        }

        navigator.clipboard.writeText(text).then(function() {
            // Success Feedback
            // If you use SweetAlert2 (common in Laravel Admin panels):

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Link copied!',
                showConfirmButton: false,
                timer: 1500
            });


            // Or just a standard alert for now:
            alert("✅ Link copied! Ready to paste on LinkedIn/Instagram.");
        }, function(err) {
            console.error('Async: Could not copy text: ', err);
        });
    }
</script>
