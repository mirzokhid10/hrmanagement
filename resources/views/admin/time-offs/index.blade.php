@extends('admin.layouts.master') {{-- Assuming you have an admin layout --}}

@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="clearfix">
            <h1 class="app-page-title">Leaves</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Leaves</li>
                </ol>
            </nav>
        </div>
        {{-- Button to open the ADD modal --}}
        <a type="button" class="btn btn-primary waves-effect waves-light" href="{{ route('admin.time-offs.create') }}">
            <i class="fa-solid fa-plus me-1"></i> Add Leave
        </a>
    </div>

    <div class="row">
        <div class="col-xxl-3 col-md-6">
            <div class="card card-action action-border-primary">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div class="clearfix ps-2">
                        <div class="d-flex text-dark align-items-end gap-1 lh-1 mb-1">
                            {{-- Dynamic Present Count --}}
                            <span class="fs-2 fw-bold">{{ $stats['present_today'] }}</span>
                            <span class="mb-1">/{{ $stats['total_employees'] }}</span>
                        </div>
                        <span class="text-primary">Today Presents</span>
                    </div>
                    <div class="clearfix">
                        {{-- Pass percentage to JS for chart --}}
                        <div id="leavesPresentsScore"
                            data-percent="{{ $stats['total_employees'] > 0 ? round(($stats['present_today'] / $stats['total_employees']) * 100) : 0 }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Planned Leaves --}}
        <div class="col-xxl-3 col-md-6">
            <div class="card card-action action-border-danger">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div class="clearfix ps-2">
                        <div class="d-flex text-dark align-items-end gap-1 lh-1 mb-1">
                            <span class="fs-2 fw-bold">{{ $stats['planned_today'] }}</span>
                            <span class="mb-1">/{{ $stats['total_employees'] }}</span>
                        </div>
                        <span class="text-danger">Planned Leaves (Today)</span>
                    </div>
                    <div class="clearfix">
                        <div id="leavesPlannedScore"
                            data-percent="{{ $stats['total_employees'] > 0 ? round(($stats['planned_today'] / $stats['total_employees']) * 100) : 0 }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Unplanned Leaves --}}
        <div class="col-xxl-3 col-md-6">
            <div class="card card-action action-border-info">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div class="clearfix ps-2">
                        <div class="d-flex text-dark align-items-end gap-1 lh-1 mb-1">
                            <span class="fs-2 fw-bold">{{ $stats['unplanned_today'] }}</span>
                            <span class="mb-1">/{{ $stats['total_employees'] }}</span>
                        </div>
                        <span class="text-info">Unplanned/Sick (Today)</span>
                    </div>
                    <div class="clearfix">
                        <div id="leavesUnplannedScore"
                            data-percent="{{ $stats['total_employees'] > 0 ? round(($stats['unplanned_today'] / $stats['total_employees']) * 100) : 0 }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Pending Requests --}}
        <div class="col-xxl-3 col-md-6">
            <div class="card card-action action-border-secondary">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div class="clearfix ps-2">
                        <div class="d-flex text-dark align-items-end gap-1 lh-1 mb-1">
                            <span class="fs-2 fw-bold">{{ $stats['pending_total'] }}</span>
                            <span class="mb-1">Requests</span>
                        </div>
                        <span class="text-secondary">Pending Action</span>
                    </div>
                    <div class="clearfix">
                        <div id="leavesPendingScore"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card overflow-hidden">
                <div class="card-header d-flex gap-3 flex-wrap align-items-center justify-content-between border-0 pb-0">
                    <h6 class="card-title mb-0">Employee’s Leave</h6>
                    <div class="d-flex gap-3 flex-wrap">
                        <div id="dt_PageEmployeeLeave_Search"></div>
                        <button type="button" class="btn btn-sm btn-outline-light btn-shadow waves-effect">Download
                            Report</button>
                        <select class="selectpicker" data-style="btn-sm btn-outline-light btn-shadow waves-effect">
                            <option value="pending">2024</option>
                            <option>2023</option>
                            <option>2022</option>
                            <option>2021</option>
                        </select>
                    </div>
                </div>
                <div class="card-body p-2">
                    <div class="table-responsive">
                        {{ $dataTable->table() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
@endpush
