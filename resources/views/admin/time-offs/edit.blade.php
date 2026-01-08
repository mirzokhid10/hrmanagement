@extends('admin.layouts.master')

@section('title', 'Edit Leave Request')

@section('content')
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="clearfix">
            <h1 class="app-page-title">
                Add New Employee
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.employee.index') }}">Employee</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Add</li>
                </ol>
            </nav>
        </div>
        <a class="btn btn-primary waves-effect waves-light" href="{{ route('admin.employee.index') }}">
            <i class="fa-solid fa-users me-2"></i> Back to Employee List
        </a>
    </div>
    <div class="card p-4">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="card-header py-3">
            <h5 class="card-title">Employee Details</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.time-offs.update', $timeOff->id) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')

                <div class="row">
                    {{-- SUPER ADMIN ONLY: Company Selection --}}
                    @if (auth()->user()->isAdmin() && !auth()->user()->company_id)
                        <div class="mb-3 col-12 col-md-6">
                            <label for="company_id" class="form-label">Company <span class="text-danger">*</span></label>
                            <select name="company_id" id="company_id"
                                class="form-select @error('company_id') is-invalid @enderror" required>
                                <option value="">Select Company</option>
                                @foreach ($companies as $id => $name)
                                    <option value="{{ $id }}"
                                        {{ old('company_id', $timeOff->company_id) == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')
                                <p class="invalid-feedback">{{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        {{-- Hidden company_id for regular users --}}
                        <input type="hidden" name="company_id" id="company_id" value="{{ auth()->user()->company_id }}">
                    @endif

                    {{-- Department (Filter Helper) --}}
                    <div class="mb-3 col-12 col-md-6">
                        <label for="department_id" class="form-label">Department</label>
                        <select id="department_id" name="department_id" class="form-select">
                            <option value="">All Departments</option>
                            @foreach ($departments as $id => $name)
                                <option value="{{ $id }}"
                                    {{ old('department_id', $timeOff->employee->department_id ?? '') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <p class="invalid-feedback">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Employee Selection --}}
                <div class="row">
                    <div class="mb-3 col-12 col-md-6">
                        <label for="employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
                        <select id="employee_id" name="employee_id" required class="form-select">
                            <option value="">Select Employee</option>
                            @foreach ($employees as $id => $name)
                                <option value="{{ $id }}"
                                    {{ old('employee_id', $timeOff->employee_id) == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')
                            <p class="invalid-feedback">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Leave Type --}}
                    <div class="mb-3 col-12 col-md-6">
                        <label for="time_off_type_id" class="form-label">Leave Type <span
                                class="text-danger">*</span></label>
                        <select id="time_off_type_id" name="time_off_type_id" required class="form-select">
                            <option value="">Select Type</option>
                            @foreach ($types as $type)
                                <option value="{{ $type->id }}"
                                    {{ old('time_off_type_id', $timeOff->time_off_type_id) == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} ({{ $type->is_paid ? 'Paid' : 'Unpaid' }})
                                </option>
                            @endforeach
                        </select>
                        @error('time_off_type_id')
                            <p class="invalid-feedback">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    {{-- Dates --}}
                    <div class="mb-3 col-12 col-md-6">
                        <label for="add_start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" id="add_start_date"
                            value="{{ old('start_date', $timeOff->start_date->format('Y-m-d')) }}" required
                            class="form-control">
                        @error('start_date')
                            <p class="invalid-feedback">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-3 col-12 col-md-6">
                        <label for="add_end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" id="add_end_date"
                            value="{{ old('end_date', $timeOff->end_date->format('Y-m-d')) }}" required
                            class="form-control">
                        @error('end_date')
                            <p class="invalid-feedback">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Reason --}}
                <div class="mb-3">
                    <label for="add_reason" class="form-label">Reason</label>
                    <textarea id="add_reason" name="reason" rows="3" class="form-control @error('reason') is-invalid @enderror">{{ old('reason', $timeOff->reason) }}</textarea>
                    @error('reason')
                        <p class="invalid-feedback">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status Management (Admins Only) --}}
                <div class="mb-3">
                    <label for="edit_status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" id="edit_status" class="form-select @error('status') is-invalid @enderror">
                        @foreach (['Pending', 'Approved', 'Rejected', 'Cancelled'] as $statusOption)
                            <option value="{{ $statusOption }}"
                                {{ old('status', $timeOff->status ?? '') == $statusOption ? 'selected' : '' }}>
                                {{ $statusOption }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Note: I removed style="display:none" here and let JS handle the initial state --}}
                <div class="mb-3" id="edit_rejection_reason_group" style="display: none;">
                    <label for="edit_rejection_reason" class="form-label">Rejection Reason <span
                            class="text-danger">*</span></label>
                    <textarea name="rejection_reason" id="edit_rejection_reason"
                        class="form-control @error('rejection_reason') is-invalid @enderror" rows="3"
                        placeholder="Enter reason for rejection">{{ old('rejection_reason', $timeOff->rejection_reason ?? '') }}</textarea>
                    @error('rejection_reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">Update Leave</button>
                </div> {{-- Rejection Reason (Hidden by default) --}}
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Select Elements
            const companySelect = document.getElementById('company_id');
            const departmentSelect = document.getElementById('department_id');
            const employeeSelect = document.getElementById('employee_id');
            const typeSelect = document.getElementById('time_off_type_id');
            const statusSelect = document.getElementById('edit_status');
            const reasonGroup = document.getElementById('edit_rejection_reason_group'); // Correct ID
            const reasonInput = document.getElementById('edit_rejection_reason');

            // 2. Define URLs (Relative paths to fix CORS)
            const urls = {
                getDepartments: "{{ route('admin.ajax.get-departments', ['companyId' => '000'], false) }}",
                getEmployees: "{{ route('admin.ajax.get-employees', [], false) }}"
            };

            // ============================================================
            // LOGIC 1: Rejection Reason Toggle
            // ============================================================
            function toggleRejectionReason() {
                if (!statusSelect || !reasonGroup) return;

                if (statusSelect.value === 'Rejected') {
                    // Show
                    reasonGroup.style.display = 'block';
                    if (reasonInput) reasonInput.setAttribute('required', 'required');
                } else {
                    // Hide
                    reasonGroup.style.display = 'none';
                    if (reasonInput) reasonInput.removeAttribute('required');
                }
            }

            // Init Rejection Logic
            if (statusSelect) {
                statusSelect.addEventListener('change', toggleRejectionReason);
                toggleRejectionReason(); // Run immediately on load
            }

            // ============================================================
            // LOGIC 2: AJAX for Company/Department/Employee
            // ============================================================

            // A. Load Departments
            function loadDepartments(companyId) {
                if (!departmentSelect) return;

                if (!companyId) {
                    departmentSelect.innerHTML = '<option value="">All Departments</option>';
                    return;
                }

                departmentSelect.innerHTML = '<option value="">Loading...</option>';
                departmentSelect.disabled = true;

                const url = urls.getDepartments.replace('000', companyId);

                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        departmentSelect.innerHTML = '<option value="">All Departments</option>';
                        data.forEach(dept => {
                            departmentSelect.innerHTML +=
                                `<option value="${dept.id}">${dept.name}</option>`;
                        });
                        departmentSelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error loading departments:', error);
                        departmentSelect.innerHTML = '<option value="">Error</option>';
                        departmentSelect.disabled = false;
                    });
            }

            // B. Load Employees & Types
            function loadEmployeesAndTypes() {
                let companyId = null;

                // Handle Super Admin (Select) vs Regular User (Hidden Input)
                if (companySelect) {
                    companyId = companySelect.value;
                } else {
                    const hiddenInput = document.querySelector('input[name="company_id"]');
                    if (hiddenInput) companyId = hiddenInput.value;
                }

                const departmentId = departmentSelect ? departmentSelect.value : '';

                if (!companyId) return;

                // UI Loading State
                if (employeeSelect) {
                    employeeSelect.disabled = true;
                    employeeSelect.innerHTML = '<option>Loading...</option>';
                }
                if (typeSelect) {
                    typeSelect.disabled = true;
                    typeSelect.innerHTML = '<option>Loading...</option>';
                }

                const params = new URLSearchParams({
                    company_id: companyId,
                    department_id: departmentId
                });

                fetch(`${urls.getEmployees}?${params.toString()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Update Employees
                        if (employeeSelect) {
                            employeeSelect.innerHTML = '<option value="">Select Employee</option>';
                            if (data.employees && data.employees.length > 0) {
                                data.employees.forEach(emp => {
                                    employeeSelect.innerHTML +=
                                        `<option value="${emp.id}">${emp.name}</option>`;
                                });
                            } else {
                                employeeSelect.innerHTML = '<option value="">No employees found</option>';
                            }
                            employeeSelect.disabled = false;
                        }

                        // Update Types
                        if (typeSelect && data.types && data.types.length > 0) {
                            typeSelect.innerHTML = '<option value="">Select Type</option>';
                            data.types.forEach(type => {
                                const isPaid = type.is_paid ? 'Paid' : 'Unpaid';
                                typeSelect.innerHTML +=
                                    `<option value="${type.id}">${type.name} (${isPaid})</option>`;
                            });
                            typeSelect.disabled = false;
                        } else if (typeSelect) {
                            // If no new types came back, keep old ones but re-enable
                            typeSelect.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error loading employees:', error);
                        if (employeeSelect) employeeSelect.disabled = false;
                        if (typeSelect) typeSelect.disabled = false;
                    });
            }

            // ============================================================
            // LOGIC 3: Event Listeners
            // ============================================================

            // 1. Company Change (Super Admin)
            if (companySelect) {
                companySelect.addEventListener('change', function() {
                    loadDepartments(this.value);
                    loadEmployeesAndTypes();
                });
            }

            // 2. Department Change
            if (departmentSelect) {
                departmentSelect.addEventListener('change', function() {
                    loadEmployeesAndTypes();
                });
            }
        });
    </script>
@endpush
