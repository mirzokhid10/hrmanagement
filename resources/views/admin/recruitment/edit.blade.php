@extends('admin.layouts.master')

@section('content')
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="clearfix">
            <h1 class="app-page-title">
                Edit Job Vacancy
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.recruitment.index') }}">Recruitment</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Edit #{{ $recruitment->id }}</li>
                </ol>
            </nav>
        </div>
        <a class="btn btn-primary waves-effect waves-light" href="{{ route('admin.recruitment.index') }}">
            <i class="fi fi-rr-arrow-left me-2"></i> Back to List
        </a>
    </div>

    <div class="card p-4">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Edit Recruitment Details</h5>
            @if ($recruitment->hh_url)
                <a href="{{ $recruitment->hh_url }}" target="_blank" class="btn btn-sm btn-outline-danger">
                    <i class="fi fi-brands-headhunter me-1"></i> View on HH.ru
                </a>
            @endif
        </div>

        <div class="card-body">
            <form action="{{ route('admin.recruitment.update', $recruitment->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- 1. Job Title -->
                    <div class="col-12 col-md-4 mb-3">
                        <label for="title" class="form-label">Job Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                            id="title" placeholder="e.g. Senior Laravel Developer"
                            value="{{ old('title', $recruitment->title) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- 2. Company (Admin Only) -->
                    @if (Auth::user()->isAdmin())
                        <div class="col-12 col-md-4 mb-3">
                            <label for="company_id" class="form-label">Company <span class="text-danger">*</span></label>
                            {{-- Note: Usually we disable company change on edit to prevent data issues, but here we allow it --}}
                            <select class="form-select @error('company_id') is-invalid @enderror" name="company_id"
                                id="recruitment_company_id" onchange="loadRecruitmentDepartments(this.value)" required>
                                <option value="" disabled>Select Company</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}"
                                        {{ old('company_id', $recruitment->company_id) == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @else
                        <input type="hidden" name="company_id" value="{{ $recruitment->company_id }}">
                    @endif

                    <!-- 3. Department -->
                    <div class="col-12 col-md-4 mb-3">
                        <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                        <select class="form-select @error('department_id') is-invalid @enderror" name="department_id"
                            id="recruitment_department_id" required>
                            <option value="" selected disabled>Select Department</option>
                            {{-- Pre-fill for HR or if Admin has loaded page --}}
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}"
                                    {{ old('department_id', $recruitment->department_id) == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <!-- HH Category (Filter) -->
                    <div class="col-12 col-md-4 mb-3">
                        <label for="hh_category" class="form-label">
                            Professional Category
                            <span class="text-muted text-xs">(Filter)</span>
                        </label>
                        <select class="form-select" id="hh_category" onchange="filterHHRoles()">
                            <option value="" selected disabled>Select Category</option>
                            @foreach ($hhRoles as $category)
                                <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- HH Role (Actual Value) -->
                    <div class="col-12 col-md-4 mb-3">
                        <label for="hh_role" class="form-label">
                            Professional Role
                            <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('hh_professional_role_id') is-invalid @enderror"
                            name="hh_professional_role_id" id="hh_role" disabled>
                            <option value="" selected disabled>Select Category First</option>
                            {{-- Options populated via JS --}}
                        </select>
                        @error('hh_professional_role_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Billing Type -->
                    <div class="col-12 col-md-4 mb-3">
                        <label for="billing_type" class="form-label">HH.ru Vacancy Type</label>
                        <select class="form-select" name="billing_type" id="billing_type">
                            @foreach (['standard' => 'Standard (Paid)', 'standard_plus' => 'Standard Plus', 'premium' => 'Premium', 'free' => 'Free'] as $val => $label)
                                <option value="{{ $val }}"
                                    {{ old('billing_type', $recruitment->billing_type) == $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <!-- Job Type -->
                    <div class="col-12 col-md-4 mb-3">
                        <label for="job_type" class="form-label">Job Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('job_type') is-invalid @enderror" name="job_type" id="job_type"
                            required>
                            @foreach (['Full-time', 'Part-time', 'Contract', 'Internship'] as $type)
                                <option value="{{ $type }}"
                                    {{ old('job_type', $recruitment->job_type) == $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Experience -->
                    <div class="col-12 col-md-4 mb-3">
                        <label for="experience" class="form-label">Experience <span class="text-danger">*</span></label>
                        <select class="form-select @error('experience') is-invalid @enderror" name="experience"
                            id="experience" required>
                            @foreach (['No experience', '1-3 years', '3-6 years', '6+ years'] as $exp)
                                <option value="{{ $exp }}"
                                    {{ old('experience', $recruitment->experience) == $exp ? 'selected' : '' }}>
                                    {{ $exp }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Schedule -->
                    <div class="col-12 col-md-4 mb-3">
                        <label for="schedule" class="form-label">Schedule <span class="text-danger">*</span></label>
                        <select class="form-select @error('schedule') is-invalid @enderror" name="schedule"
                            id="schedule" required>
                            @foreach (['5/2', '6/1', '2/2', 'Flexible', 'Remote'] as $sch)
                                <option value="{{ $sch }}"
                                    {{ old('schedule', $recruitment->schedule) == $sch ? 'selected' : '' }}>
                                    {{ $sch }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <!-- Working Hours -->
                    <div class="col-12 col-md-4 mb-3">
                        <label for="working_hours" class="form-label">Working Hours <span
                                class="text-danger">*</span></label>
                        <input type="text" name="working_hours"
                            class="form-control @error('working_hours') is-invalid @enderror" id="working_hours"
                            value="{{ old('working_hours', $recruitment->working_hours) }}" required>
                    </div>

                    <!-- Location -->
                    <div class="col-12 col-md-4 mb-3">
                        <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                        <input type="text" name="location"
                            class="form-control @error('location') is-invalid @enderror" id="location"
                            value="{{ old('location', $recruitment->location) }}" required>
                    </div>

                    <!-- Salary -->
                    <div class="col-12 col-md-4 mb-3">
                        <label for="salary_range" class="form-label">Salary Range</label>
                        <input type="text" name="salary_range"
                            class="form-control @error('salary_range') is-invalid @enderror" id="salary_range"
                            value="{{ old('salary_range', $recruitment->salary_range) }}">
                    </div>
                </div>

                <div class="row">
                    <!-- Key Skills -->
                    <div class="col-12 col-md-4 mb-3">
                        <label for="key_skills" class="form-label">Key Skills</label>
                        <input type="text" name="key_skills_input"
                            class="form-control @error('key_skills_input') is-invalid @enderror" id="key_skills"
                            placeholder="e.g. PHP, Laravel"
                            value="{{ old('key_skills_input', implode(', ', $recruitment->key_skills ?? [])) }}">
                        <div class="form-text">Separate skills with commas.</div>
                    </div>

                    <!-- Deadline -->
                    <div class="col-12 col-md-4 mb-3">
                        <label for="deadline" class="form-label">Application Deadline</label>
                        <input type="date" name="deadline"
                            class="form-control @error('deadline') is-invalid @enderror" id="deadline"
                            value="{{ old('deadline', $recruitment->deadline?->format('Y-m-d')) }}">
                    </div>

                    <!-- Status -->
                    <div class="col-12 col-md-4 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="published" {{ $recruitment->status == 'published' ? 'selected' : '' }}>
                                Published</option>
                            <option value="draft" {{ $recruitment->status == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="closed" {{ $recruitment->status == 'closed' ? 'selected' : '' }}>Closed
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Description -->
                <div class="col-12 mb-3">
                    <label for="description" class="form-label">Job Description <span
                            class="text-danger">*</span></label>
                    <textarea class="form-control @error('description') is-invalid @enderror" name="description" id="description"
                        rows="6" required>{{ old('description', $recruitment->description) }}</textarea>
                    <div class="form-text">HH.ru requires a detailed description (minimum 200 characters).</div>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- HH.ru Sync Status -->
                <div class="col-12 mb-3">
                    @if ($recruitment->hh_vacancy_id)
                        <div class="alert alert-success d-flex align-items-center">
                            <i class="fi fi-brands-headhunter fs-3 me-3"></i>
                            <div>
                                <strong>Synced with HH.ru</strong><br>
                                This vacancy is linked to HH.ru ID: {{ $recruitment->hh_vacancy_id }}
                            </div>
                        </div>
                    @else
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="post_to_hh" value="1"
                                id="postToHH">
                            <label class="form-check-label" for="postToHH">
                                Post to HH.ru automatically (Create new posting)
                            </label>
                        </div>
                    @endif
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary waves-effect waves-light">Update Vacancy</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        /**
         * Logic for Loading Departments (Same as Create, but handles pre-selection)
         */
        function loadRecruitmentDepartments(companyId, selectedDeptId = null) {
            const departmentSelect = document.getElementById('recruitment_department_id');
            departmentSelect.innerHTML = '<option value="" selected disabled>Loading...</option>';
            departmentSelect.disabled = true;

            if (!companyId) return;

            fetch(`/admin/companies/${companyId}/departments`)
                .then(r => r.json())
                .then(data => {
                    departmentSelect.innerHTML = '<option value="" selected disabled>Select Department</option>';
                    data.forEach(dept => {
                        let option = document.createElement('option');
                        option.value = dept.id;
                        option.text = dept.name;
                        // Pre-select if matches
                        if (selectedDeptId && dept.id == selectedDeptId) {
                            option.selected = true;
                        }
                        departmentSelect.appendChild(option);
                    });
                    departmentSelect.disabled = false;
                })
                .catch(e => console.error(e));
        }

        // On Load: If Admin and Company is set, ensure departments are loaded (for validation errors or initial load)
        document.addEventListener("DOMContentLoaded", function() {
            const companySelect = document.getElementById('recruitment_company_id');
            const currentDeptId = "{{ old('department_id', $recruitment->department_id) }}";

            if (companySelect && companySelect.value) {
                // We only need to fetch if the dropdown is empty (Admin view)
                // But since we passed $departments to the view in controller, it might already be populated.
                // However, if the user changes company, we need the JS.
                // For safety, we can leave the server-side population and just let JS handle changes.
            }
        });
    </script>

    <script>
        /**
         * Logic for HH Roles (Pre-selection)
         */
        const hhData = @json($hhRoles);

        function filterHHRoles(preSelectedRoleId = null) {
            const categorySelect = document.getElementById('hh_category');
            const roleSelect = document.getElementById('hh_role');
            const selectedCategoryId = categorySelect.value;

            roleSelect.innerHTML = '<option value="" selected disabled>Select Role</option>';
            roleSelect.disabled = true;

            if (!selectedCategoryId) return;

            const categoryData = hhData.find(cat => cat.id === selectedCategoryId);

            if (categoryData && categoryData.roles) {
                categoryData.roles.forEach(role => {
                    let option = document.createElement('option');
                    option.value = role.id;
                    option.text = role.name;
                    if (preSelectedRoleId && role.id == preSelectedRoleId) {
                        option.selected = true;
                    }
                    roleSelect.appendChild(option);
                });
                roleSelect.disabled = false;
            }
        }

        // Auto-select Category and Role based on saved ID
        document.addEventListener("DOMContentLoaded", function() {
            const savedRoleId = "{{ old('hh_professional_role_id', $recruitment->hh_professional_role_id) }}";

            if (savedRoleId) {
                let foundCategoryId = null;
                for (let cat of hhData) {
                    if (cat.roles.some(role => role.id == savedRoleId)) {
                        foundCategoryId = cat.id;
                        break;
                    }
                }

                if (foundCategoryId) {
                    const categorySelect = document.getElementById('hh_category');
                    if (categorySelect) {
                        categorySelect.value = foundCategoryId;
                        filterHHRoles(savedRoleId); // Pass the role ID to select it
                    }
                }
            }
        });
    </script>
@endpush
