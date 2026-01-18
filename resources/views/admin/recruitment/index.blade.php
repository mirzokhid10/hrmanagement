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
            <i class="fi fi-rr-plus me-1"></i> Create Job
        </a>

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
                                <h3 class="mb-0 fw-bold">{{ $stats['total_openings'] }}</h3>
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
                                <h3 class="mb-0 fw-bold">{{ $stats['total_applications'] }}</h3>
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
                                <h3 class="mb-0 fw-bold">{{ $stats['shortlisted'] }}</h3>
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
                                <h3 class="mb-0 fw-bold">{{ $stats['interviewed'] }}</h3>
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
                                <h3 class="mb-0 fw-bold">{{ $stats['rejected'] }}</h3>
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
                                <h3 class="mb-0 fw-bold">{{ $stats['hired'] }}</h3>

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
                    <a href="javascript:void(0);" class="btn-link me-4">View All</a>
                </div>
                <div class="card-body px-3 pb-2">
                    <!-- Placeholder content preserved from your template -->
                    <div class="alert alert-light text-center">
                        Integration with Google Calendar/Telegram coming in Week 3
                    </div>
                </div>
            </div>
        </div>

        <!-- VACANCY CARDS SECTION -->
        <div class="col-lg-12 my-3">
            <h5 class="fw-bold mb-0">Current Vacancy <span class="text-primary ms-1 text-2xs">{{ $recruitments->count() }}
                    Job Added</span></h5>
        </div>

        @foreach ($recruitments as $item)
            <div class="col-xxl-3 col-md-6">
                <div class="card card-action action-elevate bg-primary-subtle border-0 shadow-none">
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
                                <th class="minw-50px pe-0">
                                    <div class="form-check"><input class="form-check-input" type="checkbox"></div>
                                </th>
                                <th class="minw-200px">Name</th>
                                <th class="minw-200px">Department</th>
                                <th class="minw-150px">Phone No.</th>
                                <th class="minw-200px">Mail ID</th>
                                <th class="minw-150px">Status</th>
                                <th class="minw-150px">Interview Schedule</th>
                                <th class="minw-100px text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($candidates as $candidate)
                                <tr>
                                    <td class="pe-0">
                                        <div class="form-check"><input class="form-check-input" type="checkbox">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center mw-175px">
                                            <div class="avatar avatar-xxs rounded-circle">
                                                <img src="{{ $candidate->photo_path ?? asset('assets/images/avatar/default.webp') }}"
                                                    alt="">
                                            </div>
                                            <div class="ms-2 me-auto">{{ $candidate->first_name }}
                                                {{ $candidate->last_name }}</div>
                                        </div>
                                    </td>
                                    <td>{{ $candidate->vacancy->title ?? 'General' }}</td>
                                    <td>{{ $candidate->phone }}</td>
                                    <td>{{ $candidate->email }}</td>
                                    <td>
                                        <!-- Dynamic Badge based on status -->
                                        @php
                                            $badgeClass = match ($candidate->status) {
                                                'hired' => 'btn-subtle-success',
                                                'shortlisted' => 'btn-subtle-info',
                                                'rejected' => 'btn-subtle-danger',
                                                'interviewed' => 'btn-subtle-primary',
                                                default => 'btn-subtle-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ ucfirst($candidate->status) }}</span>
                                    </td>
                                    <td>{{ $candidate->email }}</td>
                                    <td>
                                        <div class="d-flex gap-2 justify-content-end">
                                            <button class="btn btn-white btn-sm btn-shadow btn-icon waves-effect"><i
                                                    class="fi fi-rr-eye"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
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
