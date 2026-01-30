@extends('admin.layouts.master')

@section('content')
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="clearfix">
            <h1 class="app-page-title">{{ __('dashboard') }}</h1>
            <span><i class="fa-regular fa-calendar me-1"></i> {{ now()->format('D, M d, Y') }} </span>
        </div>
        <a href="{{ route('admin.employee.create') }}" class="btn btn-primary waves-effect waves-light">
            <i class="fa-solid fa-plus me-1"></i> {{ __('add_employee') }}
        </a>
    </div>

    <div class="row">

        <div class="col-xxl-9">

            <div class="row">
                <div class="col-6 col-md-4 col-lg">
                    <div class="card bg-secondary bg-opacity-05 shadow-none border-0">
                        <div class="card-body">
                            <div class="avatar bg-secondary shadow-secondary rounded-circle text-white mb-3">
                                <i class="fa-solid fa-user-group"></i>
                            </div>
                            <h3 class="fs-1">{{ $stats['total_employees'] }}</h3>
                            <h6 class="mb-0">{{ __('total_employee') }}</h6>
                            <small class="fw-medium">
                                <span class="text-success">
                                    <i class="fi fi-rr-arrow-small-up scale-3x"></i> +5%
                                </span> {{ __('last_month') }}
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg">
                    <div class="card bg-info bg-opacity-05 shadow-none border-0">
                        <div class="card-body">
                            <div class="avatar bg-info shadow-info rounded-circle text-white mb-3">
                                <i class="fa-solid fa-user-plus"></i>
                            </div>
                            <h3 class="fs-1">{{ $stats['new_hires'] }}</h3>
                            <h6 class="mb-0">{{ __('new_employee') }}</h6>
                            <small class="fw-medium">
                                <span class="text-success">
                                    <i class="fi fi-rr-arrow-small-up scale-3x"></i> +3.2%
                                </span> {{ __('last_month') }}
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg">
                    <div class="card bg-secondary bg-opacity-05 shadow-none border-0">
                        <div class="card-body">
                            <div class="avatar bg-warning shadow-warning rounded-circle text-white mb-3">
                                <i class="fa-solid fa-user-xmark"></i>
                            </div>
                            <h3 class="fs-1">{{ $stats['on_leave_today'] }}</h3>
                            <h6 class="mb-0">{{ __('on_leave') }}</h6>
                            <small class="fw-medium">
                                <span class="text-danger">
                                    <i class="fi fi-rr-arrow-small-down scale-3x"></i> -2%
                                </span> {{ __('last_month') }}
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-6 col-lg">
                    <div class="card bg-success bg-opacity-05 shadow-none border-0">
                        <div class="card-body">
                            <div class="avatar bg-success shadow-success rounded-circle text-white mb-3">
                                <i class="fa-solid fa-briefcase"></i>
                            </div>
                            <h3 class="fs-1">{{ $stats['job_applicants'] }}</h3>
                            <h6 class="mb-0">{{ __('job_applicants') }}</h6>
                            <small class="fw-medium">
                                <span class="text-success">
                                    <i class="fi fi-rr-arrow-small-down scale-3x"></i> +8%
                                </span> {{ __('last_month') }}
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg">
                    <div class="card bg-danger bg-opacity-05 shadow-none border-0">
                        <div class="card-body">
                            <div class="avatar bg-danger shadow-danger rounded-circle text-white mb-3">
                                <i class="fa-solid fa-alarm-clock"></i>
                            </div>
                            <h3 class="fs-1">1017</h3>
                            <h6 class="mb-0">{{ __('over_time') }}</h6>
                            <small class="fw-medium">
                                <span class="text-danger">
                                    <i class="fi fi-rr-arrow-small-down scale-3x"></i> -8%
                                </span>{{ __('last_month') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3">
            <div class="card overflow-hidden z-1">
                <div class="card-body">
                    <div class="w-75">
                        <h6 class="card-title">{{ __('create_announcement') }}</h6>
                        <p>{{ __('make_announcement_desc') }}</p>

                    </div>
                    <img src="{{ asset('assets/images/media1.svg') }}" alt=""
                        class="position-absolute bottom-0 end-0 z-n1">
                </div>
                <div class="card-footer border-0 pt-0">
                    <a href="{{ route('admin.announcements.index') }}"
                        class="btn btn-outline-light waves-effect btn-shadow">
                        {{ __('create_now') }}
                        <i class="fa-solid fa-bullhorn mx-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between border-0">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="card-title mb-0">{{ __('retention_risks') }}</h6>

                        <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary">
                            {{ __('ai_beta') }}
                        </span>
                    </div>

                    <!-- Company Filter Dropdown -->
                    <div class="dropdown">
                        <button class="btn dropdown-toggle btn-white btn-shadow waves-effect btn-sm" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            {{ request('company_name') ?? __('all_companies') }}
                        </button>
                        <ul class="dropdown-menu">
                            <button class="btn dropdown-toggle btn-white btn-shadow btn-sm" type="button">
                                {{ request('company_name') ?? __('all_companies') }}
                            </button>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            @foreach ($companies as $company)
                                <li>
                                    <!-- Passes company_id to the URL to filter -->
                                    <a class="dropdown-item"
                                        href="{{ route('admin.dashboard', ['company_id' => $company->id, 'company_name' => $company->name]) }}">
                                        {{ $company->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="card-body px-2 pb-2 pt-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless table-row-rounded mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">{{ __('employee') }}</th>
                                    <th>{{ __('score') }}</th>
                                    <th class="text-end pe-3">{{ __('action') }}</th>

                                </tr>
                            </thead>
                            <tbody>
                                @forelse($riskInsights as $insight)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center">
                                                <!-- Avatar with Initials Fix -->
                                                <div class="avatar rounded-circle">
                                                    @if ($insight->employee && $insight->employee->image)
                                                        <img src="{{ asset('storage/' . $insight->employee->image) }}"
                                                            alt="">
                                                    @else
                                                        <div
                                                            class="d-flex justify-content-center align-items-center ali avatar-title rounded-circle bg-light text-primary fw-bold w-100 h-100">
                                                            <!-- Safe Null Check for Initials -->
                                                            {{ substr($insight->employee->full_name ?? '?', 0, 1) }}
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- Name & Details -->
                                                <div class="ms-2 me-auto">
                                                    <h6 class="mb-0 text-dark">
                                                        <h6 class="mb-0 text-dark">
                                                            {{ $insight->employee->first_name ?? __('unknown') }}
                                                            {{ $insight->employee->last_name ?? '' }}
                                                        </h6>
                                                    </h6>
                                                    <div class="d-flex align-items-center justify-content-start gap-1">
                                                        <!-- Company Badge -->
                                                        @if (auth()->id() === 1)
                                                            <span
                                                                class="badge bg-light text-secondary border px-1 d-flex justify-content-center align-items-center"
                                                                style="font-size: 9px;">
                                                                {{ $insight->employee->company->name ?? __('unknown_company') }}
                                                            </span>
                                                        @endif
                                                        <small class="text-muted" style="font-size: 11px;">
                                                            {{ $insight->employee->job_title ?? __('no_title') }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Risk Score Column (Custom Colors) -->
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                @php
                                                    $score = $insight->score;
                                                    $colorClass = 'text-danger'; // Default (< 39)

                                                    if ($score >= 80) {
                                                        $colorClass = 'text-primary'; // Blue
                                                    } elseif ($score >= 60) {
                                                        $colorClass = 'text-warning'; // Orange (Bootstrap warning is usually orange/yellow)
                                                        // If you need specific orange: style="color: #fd7e14;"
                                                    } elseif ($score >= 40) {
                                                        $colorClass = 'text-warning text-opacity-75'; // Yellowish
                                                    }
                                                @endphp

                                                <span class="fs-6 fw-bold {{ $colorClass }}">
                                                    {{ $score }}
                                                </span>

                                                <!-- AI Tooltip Icon -->
                                                @if ($insight->ai_analysis_uz)
                                                    <i class="fi fi-rr-magic-wand text-muted" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" title="{{ $insight->ai_analysis_uz }}"
                                                        style="cursor: pointer; font-size: 12px;"></i>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Action Dropdown -->
                                        <td class="text-end pe-3">
                                            <div class="btn-group">
                                                <button
                                                    class="btn btn-white btn-sm btn-shadow btn-icon waves-effect dropdown-toggle"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.risks.index') }}">
                                                            {{ __('view_details') }}
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4">
                                            <small class="text-muted">{{ __('no_risks_detected') }}</small>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>



        <div class="col-xxl-7">
            <div class="card bg-gray bg-opacity-10 border-0 shadow-none">
                <div class="card-header d-flex flex-wrap gap-3 align-items-center justify-content-between border-0 pb-0">
                    <h6 class="card-title mb-0">{{ __('no_interviews') }}</h6>
                    <div class="clearfix d-flex align-items-center">
                        <a href="{{ route('admin.recruitment.index') }}" class="btn-link me-4"> {{ __('view_all') }}</a>
                        {{-- <div class="dropdown">
                            <button class="btn dropdown-toggle btn-white btn-shadow waves-effect btn-sm" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Last Month
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);">Last Month</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);">Category</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);">Published</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);">Date Modifed</a>
                                </li>
                            </ul>
                        </div> --}}
                    </div>
                </div>
                <div class="card-body px-3 pb-2">
                    <div class="row gy-2">
                        <div class="col-md-6">
                            <ul class="d-flex flex-wrap list-group list-group-smooth list-group-unlined">
                                @forelse($interviews as $interview)
                                    <li class="list-group-item d-flex justify-content-between align-items-center mb-2">
                                        <div class="small text-danger fw-bold text-uppercase">
                                            <div class="small text-danger fw-bold text-uppercase">
                                                {{ $interview->interview_scheduled_at->format('M') }}
                                            </div>
                                            <div class="h5 mb-0 fw-bold">
                                                {{ $interview->interview_scheduled_at->format('d') }}
                                            </div>
                                        </div>
                                        <div class="ms-2 me-auto">
                                            <h6 class="mb-0">{{ $interview->full_name }}</h6>
                                            <small class="text-body">
                                                {{ $interview->recruitment->title ?? __('general_interview') }}</small>
                                        </div>
                                        <div>
                                            <span
                                                class="badge badge-lg bg-danger-subtle text-danger">{{ $interview->interview_scheduled_at->format('H:i') }}</span>

                                        </div>
                                    </li>
                                @empty
                                    <div class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-calendar-xmark fa-2x mb-2 opacity-50"></i>
                                        <p class="mb-0">{{ __('no_interviews') }}</p>
                                    </div>
                                @endforelse

                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    </div>
@endsection

<script>
    // Initialize Bootstrap Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>
