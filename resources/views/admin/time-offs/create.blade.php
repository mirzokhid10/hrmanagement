@extends('admin.layouts.master')

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
            <form action="{{ route('admin.time-offs.store') }}" method="POST">
                @csrf

                <div class="row">
                    {{-- Company Selection - Only for Super Admins --}}
                    @if (auth()->user()->isAdmin() || (auth()->user()->hasRole('admin') && !auth()->user()->company_id))
                        <div class="mb-3 col-12 col-md-6">
                            <label for="company_id" class="form-label">Company <span class="text-danger">*</span></label>
                            <select name="company_id" id="company_id"
                                class="form-select @error('company_id') is-invalid @enderror" required>
                                <option value="">Select Company</option>
                                @foreach ($companies as $id => $name)
                                    <option value="{{ $id }}" {{ old('company_id') == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @else
                        {{-- Hidden company_id for regular users --}}
                        <input type="hidden" name="company_id" id="company_id" value="{{ auth()->user()->company_id }}">
                    @endif

                    {{-- Department Selection --}}
                    <div class="mb-3 col-12 col-md-6">
                        <label for="department_id" class="form-label">Department</label>
                        <select name="department_id" id="department_id" class="form-select">
                            <option value="">All Departments</option>
                            @foreach ($departments as $id => $name)
                                <option value="{{ $id }}" {{ old('department_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    {{-- Employee Selection --}}
                    <div class="mb-3 col-12 col-md-6">
                        <label for="add_employee_id" class="form-label">Employee Name <span
                                class="text-danger">*</span></label>
                        <select name="employee_id" id="add_employee_id"
                            class="form-select @error('employee_id') is-invalid @enderror" required>
                            <option value="">Select Employee</option>
                            @foreach ($employees as $id => $name)
                                <option value="{{ $id }}" {{ old('employee_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Time-off Type Selection --}}
                    <div class="mb-3 col-12 col-md-6">
                        <label for="add_time_off_type_id" class="form-label">Leave Type <span
                                class="text-danger">*</span></label>
                        <select name="time_off_type_id" id="add_time_off_type_id"
                            class="form-select @error('time_off_type_id') is-invalid @enderror" required>
                            <option value="">Select leave type</option>
                            @foreach ($types as $type)
                                <option value="{{ $type->id }}"
                                    {{ old('time_off_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} ({{ $type->is_paid ? 'Paid' : 'Unpaid' }})
                                </option>
                            @endforeach
                        </select>
                        @error('time_off_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    {{-- Start Date --}}
                    <div class="mb-3 col-12 col-md-6">
                        <label for="add_start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" id="add_start_date"
                            class="form-control @error('start_date') is-invalid @enderror" required
                            value="{{ old('start_date') }}" min="{{ date('Y-m-d') }}">
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- End Date --}}
                    <div class="mb-3 col-12 col-md-6">
                        <label for="add_end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" id="add_end_date"
                            class="form-control @error('end_date') is-invalid @enderror" required
                            value="{{ old('end_date') }}" min="{{ date('Y-m-d') }}">
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Reason --}}
                <div class="mb-3">
                    <label for="add_reason" class="form-label">Reason</label>
                    <textarea name="reason" id="add_reason" class="form-control @error('reason') is-invalid @enderror" rows="3"
                        placeholder="Enter leave reason">{{ old('reason') }}</textarea>
                    @error('reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Status (Always Pending for new requests) --}}
                <div class="mb-3">
                    <label for="add_status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select" disabled>
                        <option selected>Pending</option>
                    </select>
                    <input type="hidden" name="status" value="Pending">
                </div>

                {{-- Submit Button --}}
                <div class="text-end">
                    <a href="{{ route('admin.time-offs.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-success">Submit Leave Request</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const companySelect = document.getElementById('company_id');
            const departmentSelect = document.getElementById('department_id');
            const employeeSelect = document.getElementById('add_employee_id');
            const typeSelect = document.getElementById('add_time_off_type_id');

            // Only set up listeners if company select exists (super admin view)
            if (companySelect) {
                companySelect.addEventListener('change', function() {
                    const companyId = this.value;

                    // Reset dependent dropdowns
                    departmentSelect.innerHTML = '<option value="">Loading...</option>';
                    employeeSelect.innerHTML = '<option value="">Select Employee</option>';
                    typeSelect.innerHTML = '<option value="">Select leave type</option>';

                    if (!companyId) {
                        departmentSelect.innerHTML = '<option value="">Select company first</option>';
                        return;
                    }

                    // Fetch departments
                    fetch(`/admin/ajax/get-departments/${companyId}`)
                        .then(response => response.json())
                        .then(departments => {
                            departmentSelect.innerHTML = '<option value="">All Departments</option>';
                            departments.forEach(dept => {
                                departmentSelect.innerHTML +=
                                    `<option value="${dept.id}">${dept.name}</option>`;
                            });
                        })
                        .catch(error => {
                            console.error('Error fetching departments:', error);
                            departmentSelect.innerHTML =
                                '<option value="">Error loading departments</option>';
                        });

                    // Fetch employees and types
                    fetchEmployeesAndTypes(companyId, null);
                });

                departmentSelect.addEventListener('change', function() {
                    const companyId = companySelect.value;
                    const departmentId = this.value;

                    if (companyId) {
                        fetchEmployeesAndTypes(companyId, departmentId);
                    }
                });
            }

            function fetchEmployeesAndTypes(companyId, departmentId) {
                const url =
                    `/admin/ajax/get-employees?company_id=${companyId}${departmentId ? `&department_id=${departmentId}` : ''}`;

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        // Update employees
                        employeeSelect.innerHTML = '<option value="">Select Employee</option>';
                        data.employees.forEach(emp => {
                            employeeSelect.innerHTML +=
                                `<option value="${emp.id}">${emp.name}</option>`;
                        });

                        // Update types
                        typeSelect.innerHTML = '<option value="">Select leave type</option>';
                        data.types.forEach(type => {
                            const paidLabel = type.is_paid ? 'Paid' : 'Unpaid';
                            typeSelect.innerHTML +=
                                `<option value="${type.id}">${type.name} (${paidLabel})</option>`;
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching employees and types:', error);
                        employeeSelect.innerHTML = '<option value="">Error loading employees</option>';
                        typeSelect.innerHTML = '<option value="">Error loading types</option>';
                    });
            }
        });
    </script>
@endpush
