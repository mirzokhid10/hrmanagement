@extends('admin.layouts.master')

@section('content')
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="clearfix">
            <h1 class="app-page-title">
                Edit Employee #{{ $employee->id }}
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.employee.index') }}">Employee</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
                </ol>
            </nav>
        </div>
        <a class="btn btn-primary waves-effect waves-light" href="{{ route('admin.employee.index') }}">
            <i class="fa-solid fa-users me-2"></i> Back to Employee List
        </a>
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
            <h5 class="card-title">Edit Employee Details</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.employee.update', $employee->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT') {{-- IMPORTANT: For update requests --}}

                <div class="row">
                    <div class="mb-3 col-md-4">
                        <label for="edit_firstName" class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name"
                            class="form-control @error('first_name') is-invalid @enderror" id="edit_firstName"
                            placeholder="Enter first name" value="{{ old('first_name', $employee->first_name) }}">
                        @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3 col-md-4">
                        <label for="edit_lastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                            id="edit_lastName" placeholder="Enter last name"
                            value="{{ old('last_name', $employee->last_name) }}">
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="edit_email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            id="edit_email" placeholder="example@email.com" value="{{ old('email', $employee->email) }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="edit_phone" class="form-label">Phone Number</label>
                        <input type="tel" name="phone_number"
                            class="form-control @error('phone_number') is-invalid @enderror" id="edit_phone"
                            placeholder="+91 9876543210" value="{{ old('phone_number', $employee->phone_number) }}">
                        @error('phone_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Company Selection for Admin ONLY, otherwise read-only for HR --}}
                    @if (Auth::user()->isAdmin())
                        <div class="mb-3 col-md-4">
                            <label for="company_id" class="form-label">Company <span class="text-danger">*</span></label>
                            <select class="form-control @error('company_id') is-invalid @enderror" id="company_id"
                                name="company_id" required>
                                <option value="">Select Company</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}"
                                        {{ old('company_id', $employee->company_id) == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }} ({{ $company->subdomain }})
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @else
                        {{-- For non-admins, show the company but make it read-only --}}
                        <div class="mb-3 col-md-4">
                            <label for="company_name" class="form-label">Company</label>
                            <input type="text" class="form-control" id="company_name"
                                value="{{ $employee->company->name }}" readonly disabled>
                            {{-- Keep a hidden field for the company_id if the backend expects it --}}
                            <input type="hidden" name="company_id" value="{{ $employee->company_id }}">
                        </div>
                    @endif

                    <div class="col-md-4 mb-3">
                        <label for="edit_department" class="form-label">Department <span
                                class="text-danger">*</span></label>
                        <select class="form-select @error('department_id') is-invalid @enderror" name="department_id"
                            id="edit_department">
                            <option value="" selected disabled>Select Department</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}"
                                    {{ old('department_id', $employee->department_id) == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="edit_joiningDate" class="form-label">Joining Date <span
                                class="text-danger">*</span></label>
                        <input type="date" name="hire_date"
                            class="form-control @error('hire_date') is-invalid @enderror" id="edit_joiningDate"
                            value="{{ old('hire_date', $employee->hire_date?->format('Y-m-d')) }}">
                        @error('hire_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="edit_dateOfBirth" class="form-label">Date Of Birth</label>
                        <input type="date" name="date_of_birth"
                            class="form-control @error('date_of_birth') is-invalid @enderror" id="edit_dateOfBirth"
                            value="{{ old('date_of_birth', $employee->date_of_birth?->format('Y-m-d')) }}">
                        @error('date_of_birth')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="edit_designation" class="form-label">Designation <span
                                class="text-danger">*</span></label>
                        <input type="text" name="job_title"
                            class="form-control @error('job_title') is-invalid @enderror" id="edit_designation"
                            placeholder="e.g. Software Engineer" value="{{ old('job_title', $employee->job_title) }}">
                        @error('job_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="edit_status" class="form-label">Employment Status <span
                                class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" name="status"
                            id="edit_status">
                            <option value="Active" {{ old('status', $employee->status) == 'Active' ? 'selected' : '' }}>
                                Active</option>
                            <option value="Inactive"
                                {{ old('status', $employee->status) == 'Inactive' ? 'selected' : '' }}>
                                Inactive</option>
                            <option value="Probation"
                                {{ old('status', $employee->status) == 'Probation' ? 'selected' : '' }}>Probation</option>
                            <option value="Terminated"
                                {{ old('status', $employee->status) == 'Terminated' ? 'selected' : '' }}>Terminated
                            </option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3 col-md-4">
                        <label for="edit_salary" class="form-label">Salary</label>
                        <input class="form-control @error('salary') is-invalid @enderror" name="salary" type="text"
                            id="edit_salary" value="{{ old('salary', $employee->salary) }}">
                        @error('salary')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3 col-md-4">
                        <label for="edit_photo" class="form-label">Profile Photo</label>
                        <input class="form-control @error('image') is-invalid @enderror" name="image" type="file"
                            id="edit_photo">
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if ($employee->image)
                            <div id="current_profile_image_container" class="mt-2">
                                Current: <img src="{{ Storage::url($employee->image) }}"
                                    id="current_profile_image_preview" alt="Profile"
                                    style="width: 50px; height: 50px; object-fit: cover;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_image" value="1"
                                        id="removeImageCheckbox" {{ old('remove_image') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="removeImageCheckbox">Remove current
                                        photo</label>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="mb-3">
                    <label for="edit_address" class="form-label">Address</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" name="address" id="edit_address"
                        rows="2" placeholder="Enter address">{{ old('address', $employee->address) }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">Update Employee</button>
                </div>
            </form>
        </div>
    </div>
@endsection
