@extends('admin.layouts.master')

@section('content')
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="clearfix">
            <h1 class="app-page-title">All Job Vacancies</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.recruitment.index') }}">Recruitment</a></li>
                    <li class="breadcrumb-item active" aria-current="page">All Jobs</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.recruitment.index') }}" class="btn btn-primary waves-effect waves-light">
            <i class="fi fi-rr-plus me-1"></i> Create New Job
        </a>
    </div>

    <div class="col-lg-12 mt-4">
        <div class="card overflow-hidden">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0">Job Vacancies List</h6>
                <!-- Optional: Add Search/Filter here later -->
            </div>
            <div class="card-body p-0 pb-2">
                <div class="table-responsive">
                    <table id="dt_basic" class="table display table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="minw-200px">Job Title</th>

                                {{-- ✅ ADMIN LOGIC: Only Admin sees Company Column --}}
                                @if (Auth::user()->isAdmin())
                                    <th class="minw-150px">Company</th>
                                @endif

                                <th class="minw-150px">Department</th>
                                <th class="minw-150px">Type & Location</th>
                                <th class="minw-100px">Candidates</th>
                                <th class="minw-150px">Posted Date</th>
                                <th class="minw-100px">Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recruitments as $job)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center mw-175px">
                                            <div
                                                class="avatar avatar-xxs rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center">
                                                <!-- Initials or Icon -->
                                                <span class="fw-bold">{{ substr($job->title, 0, 1) }}</span>
                                            </div>
                                            <div class="ms-2 me-auto">
                                                <div class="fw-bold text-dark">{{ $job->title }}</div>
                                                @if ($job->hh_url)
                                                    <a href="{{ $job->hh_url }}" target="_blank"
                                                        class="text-xs text-danger" data-bs-toggle="tooltip"
                                                        title="Posted on HH.ru">
                                                        <i class="fi fi-brands-headhunter"></i> HH.ru
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- ✅ ADMIN LOGIC: Data Cell --}}
                                    @if (Auth::user()->isAdmin())
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $job->company->name ?? 'N/A' }}</span>
                                        </td>
                                    @endif

                                    <td>
                                        <span class="text-body">{{ $job->department->name ?? 'General' }}</span>
                                    </td>

                                    <td>
                                        <div class="d-flex flex-column">
                                            <span
                                                class="badge bg-light text-dark border mb-1 w-fit-content">{{ $job->job_type }}</span>
                                            <span class="text-muted text-xs"><i
                                                    class="fi fi-rr-marker me-1"></i>{{ $job->location }}</span>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fs-6 fw-bold">{{ $job->candidates_count }}</span>
                                            @if ($job->new_candidates_count > 0)
                                                <span class="badge bg-success-subtle text-success text-2xs">
                                                    +{{ $job->new_candidates_count }} New
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        {{ $job->created_at->format('d M Y') }}
                                        <div class="text-xs text-muted">Deadline:
                                            {{ $job->deadline ? $job->deadline->format('d M Y') : 'No Limit' }}</div>
                                    </td>

                                    <td>
                                        <div class="dropdown select-status">
                                            @php
                                                $statusClass = match ($job->status) {
                                                    'published' => 'btn-subtle-success',
                                                    'draft' => 'btn-subtle-secondary',
                                                    'closed' => 'btn-subtle-danger',
                                                    default => 'btn-subtle-secondary',
                                                };
                                            @endphp

                                            <!-- Give the button a unique ID so we can update it via JS -->
                                            <button id="status-btn-{{ $job->id }}"
                                                class="btn btn-sm {{ $statusClass }} dropdown-toggle waves-effect waves-light"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                {{ ucfirst($job->status) }}
                                            </button>

                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a href="javascript:void(0)"
                                                        onclick="changeStatus({{ $job->id }}, 'published')"
                                                        class="dropdown-item d-flex align-items-center">
                                                        <span
                                                            class="badge bg-success-subtle text-success me-2 p-1 rounded-circle">
                                                        </span> Published
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0)"
                                                        onclick="changeStatus({{ $job->id }}, 'draft')"
                                                        class="dropdown-item d-flex align-items-center">
                                                        <span
                                                            class="badge bg-secondary-subtle text-secondary me-2 p-1 rounded-circle">
                                                        </span> Draft
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0)"
                                                        onclick="changeStatus({{ $job->id }}, 'closed')"
                                                        class="dropdown-item d-flex align-items-center">
                                                        <span
                                                            class="badge bg-danger-subtle text-danger me-2 p-1 rounded-circle">
                                                        </span> Closed
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="btn-group float-end">
                                            <button
                                                class="btn btn-white btn-sm btn-shadow btn-icon waves-effect dropdown-toggle"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('admin.recruitment.edit', $job->id) }}">
                                                        <i class="fi fi-rr-edit me-2"></i> Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        onclick="copyToClipboard('{{ $job->telegram_apply_link }}')">
                                                        <i class="fi fi-brands-telegram me-2 text-primary"></i> Copy
                                                        Telegram Link
                                                    </a>
                                                </li>
                                                @if ($job->hh_url)
                                                    <li>
                                                        <a class="dropdown-item" href="{{ $job->hh_url }}"
                                                            target="_blank">
                                                            <i class="fi fi-brands-headhunter me-2"></i> View on HH
                                                        </a>
                                                    </li>
                                                @endif
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <form action="{{ route('admin.recruitment.destroy', $job->id) }}"
                                                        method="POST" onsubmit="return confirm('Delete this job?');">
                                                        @csrf @method('DELETE')
                                                        <button class="dropdown-item text-danger" type="submit">
                                                            <i class="fi fi-rr-trash me-2"></i> Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ Auth::user()->isAdmin() ? 8 : 7 }}" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <div class="avatar avatar-lg rounded-circle bg-light-subtle text-muted mb-3">
                                                <i class="fi fi-rr-search fs-2"></i>
                                            </div>
                                            <h6 class="text-muted">No vacancies found</h6>
                                            <p class="text-muted small">Create a new job to get started.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="card-footer border-0 py-3">
                {{ $recruitments->links() }}
            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <script>
        function changeStatus(recruitmentId, newStatus) {
            const btn = document.getElementById(`status-btn-${recruitmentId}`);
            const originalHtml = btn.innerHTML;
            const originalClass = btn.className;

            // 1. Visual Feedback (Loading)
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            btn.disabled = true;

            // 2. Prepare Data
            const url = `/admin/recruitment/${recruitmentId}/status`;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // 3. Send Request
            fetch(url, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        status: newStatus
                    })
                })
                .then(response => {
                    if (!response.ok) throw new Error('Failed to update');
                    return response.json();
                })
                .then(data => {
                    // 4. Update UI Success
                    btn.innerHTML = data.label; // "Published"

                    // Remove old color classes
                    btn.classList.remove('btn-subtle-success', 'btn-subtle-secondary', 'btn-subtle-danger');

                    // Add new color class
                    if (newStatus === 'published') btn.classList.add('btn-subtle-success');
                    else if (newStatus === 'draft') btn.classList.add('btn-subtle-secondary');
                    else if (newStatus === 'closed') btn.classList.add('btn-subtle-danger');

                    // Optional: Show Toast Notification
                    // If you have a toast library like Toastr or SweetAlert:
                    // toastr.success(data.message);
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Revert changes on error
                    btn.innerHTML = originalHtml;
                    btn.className = originalClass;
                    alert('Failed to update status. Please try again.');
                })
                .finally(() => {
                    btn.disabled = false;
                });
        }
    </script>
@endpush
