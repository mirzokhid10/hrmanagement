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
                        <a href="{{ route('admin.recruitment.index') }}">Recruitment</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Add</li>
                </ol>
            </nav>
        </div>
        <a class="btn btn-primary waves-effect waves-light" href="{{ route('admin.employee.index') }}">
            <i class="fa-brands fa-linkedin me-2"></i> Back to Recruitment List
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
            <h5 class="card-title">Recruitment Details</h5>
        </div>

        <div class="card-content">
            <div class="card-body">
                <form action="{{ route('admin.recruitment.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <!-- 1. Job Title -->
                        <div class="col-12 col-md-4 mb-3">
                            <label for="title" class="form-label">Job Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                id="title" placeholder="e.g. Senior Laravel Developer" value="{{ old('title') }}"
                                required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 2. Company (Admin Only) vs Read-only (HR) -->
                        @if (Auth::user()->isAdmin())
                            <div class="col-12 col-md-4 mb-3">
                                <label for="company_id" class="form-label">Company <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('company_id') is-invalid @enderror" name="company_id"
                                    id="recruitment_company_id" onchange="loadRecruitmentDepartments(this.value)" required>
                                    <option value="" selected disabled>Select Company</option>
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
                            {{-- Hidden input for HR --}}
                            <input type="hidden" name="company_id" value="{{ Auth::user()->company_id }}">
                        @endif

                        <!-- 3. Department -->
                        <div class="col-12 col-md-4 mb-3">
                            <label for="department_id" class="form-label">Department <span
                                    class="text-danger">*</span></label>
                            <select class="form-select @error('department_id') is-invalid @enderror" name="department_id"
                                id="recruitment_department_id" required>
                                <option value="" selected disabled>
                                    {{ Auth::user()->isAdmin() ? 'Select Company First' : 'Select Department' }}
                                </option>
                                {{-- For HR, pre-fill departments immediately --}}
                                @if (!Auth::user()->isAdmin())
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}"
                                            {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
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
                        <!-- 2. HH.ru Role (Actual Value) -->
                        <div class="col-12 col-md-4 mb-3">
                            <label for="hh_role" class="form-label">
                                Professional Role
                                <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('hh_professional_role_id') is-invalid @enderror"
                                name="hh_professional_role_id" id="hh_role" disabled>
                                <!-- Disabled until category is chosen -->
                                <option value="" selected disabled>Select Category First</option>
                            </select>

                            @error('hh_professional_role_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- 2.1. Vacancy Type (Actual Value) -->
                        <div class="col-12 col-md-4 mb-3">
                            <label for="billing_type" class="form-label">HH.ru Vacancy Type</label>
                            <select class="form-select" name="billing_type" id="billing_type">
                                <option value="standard" selected>Standard (Paid)</option>
                                <option value="standard_plus">Standard Plus</option>
                                <option value="premium">Premium</option>
                                <option value="free">Free (If available)</option>
                            </select>
                        </div>

                    </div>
                    <div class="row">
                        <!-- 4. Job Type -->
                        <div class="col-12 col-md-4 mb-3">
                            <label for="job_type" class="form-label">Job Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('job_type') is-invalid @enderror" name="job_type"
                                id="job_type" required>
                                <option selected disabled>Select Type</option>
                                @foreach (['Full-time', 'Part-time', 'Contract', 'Internship'] as $type)
                                    <option value="{{ $type }}" {{ old('job_type') == $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                            @error('job_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- 5. Experience (New Field) -->
                        <div class="col-12 col-md-4 mb-3">
                            <label for="experience" class="form-label">Experience <span
                                    class="text-danger">*</span></label>
                            <select class="form-select @error('experience') is-invalid @enderror" name="experience"
                                id="experience" required>
                                <option selected disabled>Select Experience</option>
                                @foreach (['No experience', '1-3 years', '3-6 years', '6+ years'] as $exp)
                                    <option value="{{ $exp }}"
                                        {{ old('experience') == $exp ? 'selected' : '' }}>
                                        {{ $exp }}
                                    </option>
                                @endforeach
                            </select>
                            @error('experience')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- 6. Schedule (New Field) -->
                        <div class="col-12 col-md-4 mb-3">
                            <label for="schedule" class="form-label">Schedule <span class="text-danger">*</span></label>
                            <select class="form-select @error('schedule') is-invalid @enderror" name="schedule"
                                id="schedule" required>
                                <option selected disabled>Select Schedule</option>
                                @foreach (['5/2', '6/1', '2/2', 'Flexible', 'Remote'] as $sch)
                                    <option value="{{ $sch }}" {{ old('schedule') == $sch ? 'selected' : '' }}>
                                        {{ $sch }}
                                    </option>
                                @endforeach
                            </select>
                            @error('schedule')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                    <div class="row">
                        <!-- 7. Working Hours (New Field) -->
                        <div class="col-12 col-md-4 mb-3">
                            <label for="working_hours" class="form-label">Working Hours <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="working_hours"
                                class="form-control @error('working_hours') is-invalid @enderror" id="working_hours"
                                placeholder="e.g. 09:00 - 18:00" value="{{ old('working_hours') }}" required>
                            @error('working_hours')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- 8. Location -->
                        <div class="col-12 col-md-4 mb-3">
                            <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                            <input type="text" name="location"
                                class="form-control @error('location') is-invalid @enderror" id="location"
                                placeholder="e.g. Tashkent, Chilonzor" value="{{ old('location') }}" required>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- 9. Salary -->
                        <div class="col-12 col-md-4 mb-3">
                            <label for="salary_range" class="form-label">Salary Range</label>
                            <input type="text" name="salary_range"
                                class="form-control @error('salary_range') is-invalid @enderror" id="salary_range"
                                placeholder="e.g. 8M - 12M UZS" value="{{ old('salary_range') }}">
                            @error('salary_range')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <!-- 10. Key Skills (New Field) -->
                        <div class="col-12 col-md-4 mb-3">
                            <label for="key_skills" class="form-label">Key Skills</label>
                            <input type="text" name="key_skills_input"
                                class="form-control @error('key_skills_input') is-invalid @enderror" id="key_skills"
                                placeholder="e.g. PHP, Laravel, MySQL, Git (Comma separated)"
                                value="{{ old('key_skills_input') }}">
                            <div class="form-text">Separate skills with commas.</div>
                            @error('key_skills_input')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- 11. Deadline -->
                        <div class="col-12 col-md-4 mb-3">
                            <label for="deadline" class="form-label">Application Deadline</label>
                            <input type="date" name="deadline"
                                class="form-control @error('deadline') is-invalid @enderror" id="deadline"
                                value="{{ old('deadline') }}">
                            @error('deadline')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <!-- 12. Description -->
                    <div class="col-12 mb-3">
                        <label for="description" class="form-label">
                            Job Description
                            <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror" name="description" id="description"
                            rows="6" placeholder="Describe responsibilities, requirements, and conditions (Min 200 characters)..."
                            required>{{ old('description') }}</textarea>

                        <div class="form-text">
                            HH.ru requires a detailed description (minimum 200 characters).
                        </div>

                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- HH.ru Sync -->
                    <div class="col-12 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="post_to_hh" value="1"
                                id="postToHH">
                            <label class="form-check-label" for="postToHH">
                                Post to HH.ru automatically
                            </label>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary waves-effect waves-light">Create
                            Vacancy</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    </div>
@endsection

@push('scripts')
    <script>
        /**
         * Fetch departments for the selected company via AJAX
         */
        function loadRecruitmentDepartments(companyId) {
            const departmentSelect = document.getElementById('recruitment_department_id');

            // 1. Reset dropdown and show loading state
            departmentSelect.innerHTML = '<option value="" selected disabled>Loading...</option>';
            departmentSelect.disabled = true;

            // 2. Handle empty selection
            if (!companyId) {
                departmentSelect.innerHTML = '<option value="" selected disabled>Select Company First</option>';
                return;
            }

            // 3. Fetch Data
            // Matches Route: /admin/companies/{company_id}/departments
            fetch(`/admin/companies/${companyId}/departments`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Reset to default state
                    departmentSelect.innerHTML = '<option value="" selected disabled>Select Department</option>';

                    if (data.length === 0) {
                        departmentSelect.innerHTML +=
                            '<option value="" disabled>No departments found for this company</option>';
                    }

                    // Populate options
                    data.forEach(dept => {
                        let option = document.createElement('option');
                        option.value = dept.id;
                        option.text = dept.name;
                        departmentSelect.appendChild(option);
                    });

                    // Enable the dropdown
                    departmentSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error fetching departments:', error);
                    departmentSelect.innerHTML =
                        '<option value="" selected disabled>Error loading departments</option>';
                });
        }

        /**
         * Handle "Old Input" (Re-population after validation error)
         */
        document.addEventListener("DOMContentLoaded", function() {
            const companySelect = document.getElementById('recruitment_company_id');
            const departmentSelect = document.getElementById('recruitment_department_id');

            // Blade injects the old input value here
            const oldDepartmentId = "{{ old('department_id') }}";

            // Only run if we are in Admin mode (company select exists) and a company is selected
            if (companySelect && companySelect.value) {

                // 1. Trigger the load immediately
                loadRecruitmentDepartments(companySelect.value);

                // 2. If there was an old department selected, try to re-select it
                if (oldDepartmentId) {
                    // We need to wait for the AJAX to finish.
                    // A simple interval check is robust enough for this UI interaction.
                    const checkInterval = setInterval(() => {
                        // Check if dropdown is enabled (meaning AJAX finished)
                        if (!departmentSelect.disabled) {
                            departmentSelect.value = oldDepartmentId;
                            clearInterval(checkInterval); // Stop checking
                        }
                    }, 100); // Check every 100ms
                }
            }
        });
    </script>
@endpush

@push('scripts')
    <script>
        // 1. Pass PHP data to JavaScript
        const hhData = @json($hhRoles);

        function filterHHRoles() {
            const categorySelect = document.getElementById('hh_category');
            const roleSelect = document.getElementById('hh_role');
            const selectedCategoryId = categorySelect.value;

            // Reset Role Dropdown
            roleSelect.innerHTML = '<option value="" selected disabled>Select Role</option>';
            roleSelect.disabled = true;

            if (!selectedCategoryId) return;

            // Find the selected category object in the JSON data
            const categoryData = hhData.find(cat => cat.id === selectedCategoryId);

            if (categoryData && categoryData.roles) {
                // Populate Roles
                categoryData.roles.forEach(role => {
                    let option = document.createElement('option');
                    option.value = role.id;
                    option.text = role.name;
                    roleSelect.appendChild(option);
                });

                roleSelect.disabled = false;
            }
        }

        // Handle "Old Input" (If validation fails, restore the selection)
        document.addEventListener("DOMContentLoaded", function() {
            const oldRoleId = "{{ old('hh_professional_role_id') }}";

            if (oldRoleId) {
                // Find which category this role belongs to
                let foundCategoryId = null;

                // Loop through data to find the parent category
                for (let cat of hhData) {
                    if (cat.roles.some(role => role.id == oldRoleId)) {
                        foundCategoryId = cat.id;
                        break;
                    }
                }

                // If found, select the category and trigger the update
                if (foundCategoryId) {
                    const categorySelect = document.getElementById('hh_category');
                    const roleSelect = document.getElementById('hh_role');

                    if (categorySelect) {
                        categorySelect.value = foundCategoryId;
                        filterHHRoles(); // Load options

                        // Select the specific role
                        setTimeout(() => {
                            roleSelect.value = oldRoleId;
                        }, 50);
                    }
                }
            }
        });
    </script>
@endpush
