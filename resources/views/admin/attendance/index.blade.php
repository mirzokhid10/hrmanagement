@extends('admin.layouts.master')

@section('content')
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between mb-4">
        <div class="clearfix">
            <h1 class="app-page-title mb-0">{{ __('Attendance') }}</h1>
            <span class="text-muted small">
                {{-- Use translatedFormat to get localized month names (e.g., Yanvar, Январь) --}}
                {{ __('Monthly Overview') }}: {{ \Carbon\Carbon::parse($selectedMonth)->translatedFormat('F Y') }}
            </span>
        </div>

        <div class="d-flex gap-2">
            <!-- Month Filter -->
            <form action="{{ route('admin.attendance.index') }}" method="GET" class="d-flex gap-2">
                @if ($isAdmin)
                    <select name="company_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">{{ __('All Companies') }}</option>
                        @foreach ($companies as $comp)
                            <option value="{{ $comp->id }}" {{ $companyId == $comp->id ? 'selected' : '' }}>
                                {{ $comp->name }}
                            </option>
                        @endforeach
                    </select>
                @endif

                <input type="month" name="month" class="form-control form-control-sm" value="{{ $selectedMonth }}"
                    onchange="this.form.submit()">
            </form>
            <a href="{{ route('admin.attendance.settings.index') }}" class="btn btn-outline-primary">
                <span><i class="fa-solid fa-gears mx-1"></i></span>{{ __('Settings') }}
            </a>

            <button type="button" class="btn btn-primary btn-sm waves-effect waves-light">
                <i class="fa-solid fa-download me-1"></i> {{ __('Report') }}
            </button>
        </div>
    </div>

    <!-- ATTENDANCE GRID -->
    <div class="card overflow-hidden border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover table-bordered align-middle mb-0" style="font-size: 13px;">
                    <thead class="bg-light text-center">
                        <tr>
                            <!-- 1. Employee Header -->
                            <th class="text-start ps-3 minw-200px bg-light sticky-start" style="z-index: 10;">
                                {{ __('Employee') }}</th>

                            <!-- 2. Department Header -->
                            <th class="bg-light text-muted" style="min-width: 120px;">{{ __('Dept') }}</th>

                            <!-- 3. Company Header (Admin Only) -->
                            @if ($isAdmin)
                                <th class="bg-light text-muted" style="min-width: 120px;">{{ __('Company') }}</th>
                            @endif

                            <!-- 4. Days Header -->
                            @for ($day = 1; $day <= $daysInMonth; $day++)
                                @php
                                    $date = \Carbon\Carbon::createFromDate($selectedMonth)->day($day);
                                    $isWeekend = $date->isWeekend();
                                @endphp
                                <th class="{{ $isWeekend ? 'bg-secondary bg-opacity-10 text-muted' : '' }}"
                                    style="min-width: 35px; width: 35px;">
                                    <span class="fw-bold">{{ $day }}</span>
                                </th>
                            @endfor
                            <th class="bg-light">{{ __('Stats') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $employee)
                            <tr>
                                <!-- 1. Employee Name Column -->
                                <td class="ps-3 bg-white sticky-start" style="z-index: 10;">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-xs rounded-circle me-2">
                                            <img src="{{ $employee->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($employee->first_name) }}"
                                                alt="">
                                        </div>
                                        <div class="fw-bold text-dark text-nowrap">
                                            {{ $employee->first_name }} {{ $employee->last_name }}
                                        </div>
                                    </div>
                                </td>

                                <!-- 2. Department Column -->
                                <td class="text-muted text-nowrap">
                                    {{ $employee->department->name ?? '-' }}
                                </td>

                                <!-- 3. Company Column (Admin Only) -->
                                @if ($isAdmin)
                                    <td class="text-primary text-nowrap">
                                        {{ $employee->company->name ?? '-' }}
                                    </td>
                                @endif

                                <!-- 4. Days Loop -->
                                @php
                                    $presentCount = 0;
                                    $absentCount = 0;
                                @endphp

                                @for ($day = 1; $day <= $daysInMonth; $day++)
                                    @php
                                        $dateObj = \Carbon\Carbon::createFromDate($selectedMonth)->day($day);
                                        $lookupKey = $employee->id . '_' . $day;
                                        $attendance = $attendances->get($lookupKey)?->first();

                                        $isLeave = isset($leaveMap[$employee->id][$day]);
                                        $isWeekend = $dateObj->isWeekend();
                                        $isFuture = $dateObj->isFuture();
                                    @endphp

                                    <td class="text-center p-0 {{ $isWeekend ? 'bg-secondary bg-opacity-10' : '' }}"
                                        style="height: 38px;">

                                        @if ($attendance)
                                            <!-- CASE 1: PRESENT -->
                                            @php $presentCount++; @endphp
                                            <i class="fa-regular fa-circle-check text-primary" data-bs-toggle="tooltip"
                                                title="{{ __('In') }}: {{ \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') }}"></i>
                                        @elseif($isLeave)
                                            <!-- CASE 2: ON LEAVE -->
                                            <span class="badge bg-danger-subtle text-danger p-1"
                                                style="font-size: 10px;">{{ __('OFF') }}</span>
                                        @elseif($isWeekend)
                                            <!-- CASE 3: WEEKEND -->
                                            <i class="fa-regular fa-circle-check" style="color: #97A1C0;"></i>
                                        @elseif($isFuture)
                                            <!-- CASE 4: FUTURE -->
                                            <span class="text-muted">-</span>
                                        @else
                                            <!-- CASE 5: ABSENT -->
                                            @php $absentCount++; @endphp
                                            <i class="fa-regular fa-circle-xmark text-danger"
                                                title="{{ __('Absent') }}"></i>
                                        @endif
                                    </td>
                                @endfor

                                <!-- 5. Stats Column -->
                                <td class="text-center fw-bold text-xs">
                                    <span class="text-success">{{ $presentCount }}P</span> /
                                    <span class="text-danger">{{ $absentCount }}A</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- LEGEND -->
    <div class="mt-3 d-flex gap-4 justify-content-center text-sm text-muted">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-regular fa-circle-check text-primary"></i> {{ __('Present') }}
        </div>
        <div class="d-flex align-items-center gap-2">
            <i class="fa-regular fa-circle-xmark text-danger"></i> {{ __('Absent') }}
        </div>
        <div class="d-flex align-items-center gap-2">
            <i class="fa-regular fa-circle-check" style="color: #97A1C0;"></i> {{ __('Weekend/Holiday') }}
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-danger-subtle text-danger text-xs">{{ __('OFF') }}</span> {{ __('On Leave') }}
        </div>
    </div>

    <script>
        // Initialize Tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });
    </script>
@endsection
