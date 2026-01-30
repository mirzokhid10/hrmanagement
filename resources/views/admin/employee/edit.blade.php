@extends('admin.layouts.master')

@section('content')
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="clearfix">
            <h1 class="app-page-title">
                {{ __('Edit Employee') }} #{{ $employee->id }}
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.employee.index') }}">{{ __('Employee') }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('edit') }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-middle justify-end gap-2">
            <form class="h-100" action="{{ route('admin.employee.telegram-welcome', $employee->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-info text-white"
                    {{ !$employee->telegram_chat_id ? 'disabled' : '' }}>
                    <i class="fi fi-rr-paper-plane me-1"></i> {{ __('Send Welcome Message') }}
                </button>
            </form>
            <form action="{{ route('admin.employee.send-welcome', $employee->id) }}" method="POST" class="d-flex gap-2">
                @csrf
                <select name="language" class="form-select form-select-sm w-auto">
                    <option value="uz">{{ __('Uzbek') }}</option>
                    <option value="ru">{{ __('Russian') }}</option>
                    <option value="en">{{ __('English') }}</option>
                </select>
                <button type="submit" class="btn btn-sm btn-outline-primary">
                    <i class="fi fi-rr-paper-plane me-1"></i> {{ __('Send Welcome Email') }}
                </button>
            </form>
            <a class="btn btn-primary waves-effect waves-light" href="{{ route('admin.employee.index') }}">
                <i class="fa-solid fa-users me-2"></i> {{ __('Back to Employee List') }}
            </a>
        </div>
    </div>

    <div class="card p-4">
        {{-- Display validation errors if any --}}
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
            <h5 class="card-title">{{ __('Edit Employee Details') }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.employee.update', $employee->id) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="mb-3 col-md-4">
                        <label for="edit_firstName" class="form-label">{{ __('First Name') }} <span
                                class="text-danger">*</span></label>
                        <input type="text" name="first_name"
                            class="form-control @error('first_name') is-invalid @enderror" id="edit_firstName"
                            placeholder="{{ __('Enter first name') }}"
                            value="{{ old('first_name', $employee->first_name) }}">
                        @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3 col-md-4">
                        <label for="edit_lastName" class="form-label">{{ __('Last Name') }} <span
                                class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                            id="edit_lastName" placeholder="{{ __('Enter last name') }}"
                            value="{{ old('last_name', $employee->last_name) }}">
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="edit_email" class="form-label">{{ __('Email Address') }} <span
                                class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            id="edit_email" placeholder="example@email.com" value="{{ old('email', $employee->email) }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="edit_phone" class="form-label">{{ __('Phone Number') }}</label>
                        <input type="tel" name="phone_number"
                            class="form-control @error('phone_number') is-invalid @enderror" id="edit_phone"
                            placeholder="+998 90 123 45 67" value="{{ old('phone_number', $employee->phone_number) }}">
                        @error('phone_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Company Selection for Admin ONLY --}}
                    @if (Auth::user()->isAdmin())
                        <div class="mb-3 col-md-4">
                            <label for="company_id" class="form-label">{{ __('Company') }} <span
                                    class="text-danger">*</span></label>
                            <select class="form-control @error('company_id') is-invalid @enderror" id="company_id"
                                name="company_id" required onchange="loadDepartments(this.value)">
                                <option value="">{{ __('Select Company') }}</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}"
                                        {{ old('company_id', $employee->company_id) == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @else
                        {{-- For non-admins --}}
                        <div class="mb-3 col-md-4">
                            <label for="company_name" class="form-label">{{ __('Company') }}</label>
                            <input type="text" class="form-control" id="company_name"
                                value="{{ $employee->company->name }}" readonly disabled>
                            <input type="hidden" name="company_id" value="{{ $employee->company_id }}">
                        </div>
                    @endif

                    <div class="col-md-4 mb-3">
                        <label for="department" class="form-label">{{ __('Department') }} <span
                                class="text-danger">*</span></label>
                        <select class="form-select @error('department_id') is-invalid @enderror" name="department_id"
                            id="department">
                            <option value="" selected disabled>{{ __('Select Company First') }}</option>

                            @if (!Auth::user()->isAdmin())
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ $employee->department_id == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="edit_joiningDate" class="form-label">{{ __('Joining Date') }} <span
                                class="text-danger">*</span></label>
                        <input type="date" name="hire_date"
                            class="form-control @error('hire_date') is-invalid @enderror" id="edit_joiningDate"
                            value="{{ old('hire_date', $employee->hire_date?->format('Y-m-d')) }}">
                        @error('hire_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="edit_dateOfBirth" class="form-label">{{ __('Date Of Birth') }}</label>
                        <input type="date" name="date_of_birth"
                            class="form-control @error('date_of_birth') is-invalid @enderror" id="edit_dateOfBirth"
                            value="{{ old('date_of_birth', $employee->date_of_birth?->format('Y-m-d')) }}">
                        @error('date_of_birth')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="edit_designation" class="form-label">{{ __('Designation') }} <span
                                class="text-danger">*</span></label>
                        <input type="text" name="job_title"
                            class="form-control @error('job_title') is-invalid @enderror" id="edit_designation"
                            placeholder="{{ __('Designation') }}" value="{{ old('job_title', $employee->job_title) }}">
                        @error('job_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="edit_status" class="form-label">{{ __('Employment Status') }} <span
                                class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" name="status"
                            id="edit_status">
                            <option value="Active" {{ old('status', $employee->status) == 'Active' ? 'selected' : '' }}>
                                {{ __('Active') }}</option>
                            <option value="Inactive"
                                {{ old('status', $employee->status) == 'Inactive' ? 'selected' : '' }}>
                                {{ __('Inactive') }}</option>
                            <option value="Probation"
                                {{ old('status', $employee->status) == 'Probation' ? 'selected' : '' }}>
                                {{ __('Probation') }}</option>
                            <option value="Terminated"
                                {{ old('status', $employee->status) == 'Terminated' ? 'selected' : '' }}>
                                {{ __('Terminated') }}
                            </option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3 col-md-4">
                        <label for="edit_salary" class="form-label">{{ __('Salary') }}</label>
                        <input class="form-control @error('salary') is-invalid @enderror" name="salary" type="text"
                            id="edit_salary" value="{{ old('salary', $employee->salary) }}">
                        @error('salary')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3 col-md-4">
                        <label for="edit_photo" class="form-label">{{ __('Profile Photo') }}</label>
                        <input class="form-control @error('image') is-invalid @enderror" name="image" type="file"
                            id="edit_photo">
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if ($employee->image)
                            <div id="current_profile_image_container" class="mt-2">
                                {{ __('Current') }}: <img src="{{ Storage::url($employee->image) }}"
                                    id="current_profile_image_preview" alt="Profile"
                                    style="width: 50px; height: 50px; object-fit: cover;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_image" value="1"
                                        id="removeImageCheckbox" {{ old('remove_image') ? 'checked' : '' }}>
                                    <label class="form-check-label"
                                        for="removeImageCheckbox">{{ __('Remove current photo') }}</label>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="mb-3">
                    <label for="edit_address" class="form-label">{{ __('Address') }}</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" name="address" id="edit_address"
                        rows="2" placeholder="{{ __('Enter address') }}">{{ old('address', $employee->address) }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">{{ __('Update Employee') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection


@push('scripts')
    <script>
        function loadDepartments(companyId) {
            const departmentSelect = document.getElementById('department');

            // Reset dropdown with translated text
            departmentSelect.innerHTML = '<option value="" selected disabled>{{ __('Loading...') }}</option>';
            departmentSelect.disabled = true;

            if (!companyId) {
                departmentSelect.innerHTML =
                    '<option value="" selected disabled>{{ __('Select Company First') }}</option>';
                return;
            }

            // Fetch data
            fetch(`/admin/companies/${companyId}/departments`)
                .then(response => response.json())
                .then(data => {
                    departmentSelect.innerHTML =
                        '<option value="" selected disabled>{{ __('Select Department') }}</option>';

                    if (data.length === 0) {
                        departmentSelect.innerHTML +=
                            '<option value="" disabled>{{ __('No departments found') }}</option>';
                    }

                    data.forEach(dept => {
                        let option = document.createElement('option');
                        option.value = dept.id;
                        option.text = dept.name;
                        // Pre-select if it matches the current employee's department (mostly for Admin view)
                        if (dept.id == "{{ $employee->department_id }}") {
                            option.selected = true;
                        }
                        departmentSelect.appendChild(option);
                    });

                    departmentSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error:', error);
                    departmentSelect.innerHTML =
                        '<option value="" selected disabled>{{ __('Error loading departments') }}</option>';
                });
        }

        document.addEventListener("DOMContentLoaded", function() {
            const companyId = document.getElementById('company_id')?.value;
            // Use the employee's current department ID as default, or old input if validation failed
            const targetDepartmentId = "{{ old('department_id', $employee->department_id) }}";

            if (companyId && "{{ Auth::user()->isAdmin() }}") {
                loadDepartments(companyId);

                // Re-select logic
                setTimeout(() => {
                    if (targetDepartmentId) {
                        const deptSelect = document.getElementById('department');
                        if (deptSelect) deptSelect.value = targetDepartmentId;
                    }
                }, 500);
            }
        });
    </script>
@endpush
