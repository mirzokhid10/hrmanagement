@extends('admin.layouts.master')

@section('content')
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="clearfix">
            <h1 class="app-page-title">
                {{ __('Add New Employee') }}
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.employee.index') }}">{{ __('Employee') }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Add') }}</li>
                </ol>
            </nav>
        </div>
        <a class="btn btn-primary waves-effect waves-light" href="{{ route('admin.employee.index') }}">
            <i class="fa-solid fa-users me-2"></i> {{ __('Back to Employee List') }}
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
            <h5 class="card-title">{{ __('Employee Details') }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.employee.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="mb-3 col-md-4">
                        <label for="firstName" class="form-label">{{ __('First Name') }} <span
                                class="text-danger">*</span></label>
                        <input type="text" name="first_name"
                            class="form-control @error('first_name') is-invalid @enderror" id="firstName"
                            placeholder="{{ __('Enter first name') }}" value="{{ old('first_name') }}">
                        @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3 col-md-4">
                        <label for="lastName" class="form-label">{{ __('Last Name') }} <span
                                class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                            id="lastName" placeholder="{{ __('Enter last name') }}" value="{{ old('last_name') }}">
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="email" class="form-label">{{ __('Email Address') }} <span
                                class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            id="email" placeholder="example@email.com" value="{{ old('email') }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="phone" class="form-label">{{ __('Phone Number') }}</label>
                        <input type="tel" name="phone_number"
                            class="form-control @error('phone_number') is-invalid @enderror" id="phone"
                            placeholder="+998 90 123 45 67" value="{{ old('phone_number') }}">
                        @error('phone_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3 col-md-4">
                        <label for="photo" class="form-label">{{ __('Profile Photo') }}</label>
                        <input class="form-control @error('image') is-invalid @enderror" name="image" type="file"
                            id="photo">
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Company Selection Logic --}}
                    @if (Auth::user()->isAdmin())
                        <div class="mb-3 col-md-4">
                            <label for="company_id" class="form-label">{{ __('Company') }} <span
                                    class="text-danger">*</span></label>
                            <select class="form-control @error('company_id') is-invalid @enderror" id="company_id"
                                name="company_id" required onchange="loadDepartments(this.value)">
                                <option value="">{{ __('Select Company') }}</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}"
                                        {{ old('company_id') == $company->id ? 'selected' : '' }}>
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
                            {{-- Assuming $employee might not be set in create view, usually this is Auth::user()->company->name for create --}}
                            <input type="text" class="form-control" id="company_name"
                                value="{{ Auth::user()->company->name ?? '' }}" readonly disabled>
                            <input type="hidden" name="company_id" value="{{ Auth::user()->company_id ?? '' }}">
                        </div>
                    @endif
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="department" class="form-label">{{ __('Department') }} <span
                                class="text-danger">*</span></label>
                        <select class="form-select @error('department_id') is-invalid @enderror" name="department_id"
                            id="department">
                            <option value="" selected disabled>{{ __('Select Company First') }}</option>

                            @if (!Auth::user()->isAdmin())
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="designation" class="form-label">{{ __('Designation') }} <span
                                class="text-danger">*</span></label>
                        <input type="text" name="job_title"
                            class="form-control @error('job_title') is-invalid @enderror" id="designation"
                            placeholder="{{ __('Designation') }}" value="{{ old('job_title') }}">
                        @error('job_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="joiningDate" class="form-label">{{ __('Joining Date') }} <span
                                class="text-danger">*</span></label>
                        <input type="date" name="hire_date"
                            class="form-control @error('hire_date') is-invalid @enderror" id="joiningDate"
                            value="{{ old('hire_date') }}">
                        @error('hire_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="status" class="form-label">{{ __('Employment Status') }} <span
                                class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" name="status" id="status">
                            <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>{{ __('Active') }}
                            </option>
                            <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>
                                {{ __('Inactive') }}</option>
                            <option value="Probation" {{ old('status') == 'Probation' ? 'selected' : '' }}>
                                {{ __('Probation') }}</option>
                            <option value="Terminated" {{ old('status') == 'Terminated' ? 'selected' : '' }}>
                                {{ __('Terminated') }}</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="dateOfBirth" class="form-label">{{ __('Date Of Birth') }}</label>
                        <input type="date" name="date_of_birth"
                            class="form-control @error('date_of_birth') is-invalid @enderror" id="dateOfBirth"
                            value="{{ old('date_of_birth') }}">
                        @error('date_of_birth')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3 col-md-4">
                        <label for="salary" class="form-label">{{ __('Salary') }}</label>
                        <input class="form-control @error('salary') is-invalid @enderror" name="salary" type="text"
                            id="salary" value="{{ old('salary') }}">
                        @error('salary')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">{{ __('Address') }}</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" name="address" id="address" rows="2"
                        placeholder="{{ __('Enter address') }}">{{ old('address') }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-success">{{ __('Add Employee') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
