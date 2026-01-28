@extends('admin.layouts.master')

@section('content')
    <!-- Page Header -->
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between mb-4">
        <div>
            <h1 class="app-page-title">Retention Risk Analysis</h1>
            <span class="text-muted">AI-Powered insights for employee retention</span>
        </div>
        <button class="btn btn-primary waves-effect waves-light">
            <i class="fi fi-rr-refresh me-1"></i> Recalculate AI
        </button>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card overflow-hidden">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="card-title mb-0">Risk Assessment Report</h6>
                    <!-- Optional Filter Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-white btn-sm btn-shadow btn-icon waves-effect dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fi fi-rr-menu-dots"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">Export Excel</a></li>
                            <li><a class="dropdown-item" href="#">Filter High Risk</a></li>
                        </ul>
                    </div>
                </div>

                <div class="card-body p-0 pb-2">
                    <div class="table-responsive">
                        <!-- Using your specific table classes -->
                        <table id="dt_risks" class="table display align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="minw-200px">Employee Name</th>

                                    <!-- Admin Only Column -->
                                    @if (auth()->user()->isAdmin())
                                        <th class="minw-150px">Company</th>
                                    @endif

                                    <th class="minw-100px">Score</th>
                                    <th class="minw-150px">Risk Level</th>
                                    <th class="minw-200px">Key Factors</th>
                                    <th class="minw-200px">AI Insight</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($risks as $risk)
                                    <tr>
                                        <!-- 1. Employee Name (Avatar + Text) -->
                                        <td>
                                            <div class="d-flex align-items-center mw-175px">
                                                <div class="avatar avatar-xs rounded-circle">
                                                    @if ($risk->employee?->image)
                                                        <img src="{{ asset('storage/' . $risk->employee->image) }}"
                                                            alt="">
                                                    @else
                                                        <!-- Initials Fallback -->
                                                        <div
                                                            class="d-flex justify-content-center align-items-center avatar-title rounded-circle bg-primary-subtle text-primary fw-bold w-100 h-100">
                                                            {{ substr($risk->employee?->first_name ?? 'U', 0, 1) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="ms-2 me-auto">
                                                    <div class="text-dark fw-bold">
                                                        {{ $risk->employee?->first_name ?? 'Unknown' }}
                                                        {{ $risk->employee?->last_name ?? '' }}
                                                    </div>
                                                    <small class="text-muted d-block" style="font-size: 11px;">
                                                        {{ $risk->employee?->job_title ?? 'Employee' }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- 2. Company (Admin Only) -->
                                        @if (auth()->user()->isAdmin())
                                            <td>
                                                <span class="text-body fw-medium">
                                                    {{ $risk->employee?->company?->name ?? 'N/A' }}
                                                </span>
                                            </td>
                                        @endif

                                        <!-- 3. Risk Score (Colored Text) -->
                                        <td>
                                            @php
                                                $score = $risk->score;
                                                $colorClass = 'text-danger'; // Default (< 39)

                                                if ($score >= 80) {
                                                    $colorClass = 'text-primary'; // Blue
                                                } elseif ($score >= 60) {
                                                    $colorClass = 'text-warning'; // Orange (Bootstrap warning is usually orange/yellow)
                                                    // If you need specific orange: style="color: #fd7e14;"
                                                } elseif ($score >= 40) {
                                                    $colorClass = 'text-warning-emphasis'; // Yellowish
                                                }
                                            @endphp

                                            <span class="fs-6 fw-bold {{ $colorClass }}">
                                                {{ $score }}
                                            </span>

                                        </td>

                                        <!-- 4. Risk Level (Styled like your Status Buttons) -->
                                        <td>
                                            <div class="dropdown select-status">
                                                @if ($risk->score >= 80)
                                                    <button class="btn btn-sm btn-subtle-danger waves-effect waves-light"
                                                        type="button" disabled>
                                                        Critical
                                                    </button>
                                                @elseif($risk->score >= 50)
                                                    <button class="btn btn-sm btn-subtle-warning waves-effect waves-light"
                                                        type="button" disabled>
                                                        High Risk
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-subtle-info waves-effect waves-light"
                                                        type="button" disabled>
                                                        Medium
                                                    </button>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- 5. Key Factors -->
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach ($risk->factors ?? [] as $factor)
                                                    <span class="badge bg-light text-secondary border fw-normal">
                                                        {{ $factor }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </td>

                                        <!-- 6. AI Insight -->
                                        <td>
                                            @if ($risk->ai_analysis_uz)
                                                <span class="d-inline-block text-truncate" style="max-width: 200px;"
                                                    data-bs-toggle="tooltip" title="{{ $risk->ai_analysis_uz }}">
                                                    <i class="fi fi-rr-magic-wand text-primary me-1"></i>
                                                    <span
                                                        class="text-muted fst-italic small">{{ $risk->ai_analysis_uz }}</span>
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <!-- 7. Action Dropdown -->
                                        <td>
                                            <div class="btn-group float-end">
                                                <button
                                                    class="btn btn-white btn-sm btn-shadow btn-icon waves-effect dropdown-toggle"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item" href="#">
                                                            <i class="fi fi-rr-eye me-2"></i> View Profile
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#">
                                                            <i class="fi fi-rr-comment-alt me-2"></i> Add Note
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#">
                                                            <i class="fi fi-rr-trash me-2"></i> Dismiss Risk
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-3 py-3">
                        {{ $risks->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
