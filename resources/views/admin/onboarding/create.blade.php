@extends('admin.layouts.master')

@section('content')
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="clearfix">
            <h1 class="app-page-title">Assign New Task</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.onboarding.index') }}">Tasks</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create</li>
                </ol>
            </nav>
        </div>
        <a class="btn btn-primary waves-effect waves-light" href="{{ route('admin.onboarding.index') }}">
            <i class="fi fi-rr-arrow-left me-2"></i> Back to Board
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
            <h5 class="card-title">Task Details</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.onboarding.store') }}" method="POST">
                @csrf

                <div class="row">
                    {{-- Admin Company Selection --}}
                    @if (Auth::user()->isAdmin())
                        <div class="mb-3 col-md-6">
                            <label for="company_id" class="form-label">Company <span class="text-danger">*</span></label>
                            <select class="form-control @error('company_id') is-invalid @enderror" id="company_id"
                                name="company_id" required onchange="loadEmployees(this.value)">
                                <option value="">Select Company</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}"
                                        {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Employee Selection --}}
                    <div class="col-md-6 mb-3">
                        <label for="employee_id" class="form-label">Assign To <span class="text-danger">*</span></label>
                        <select class="form-select @error('employee_id') is-invalid @enderror" name="employee_id"
                            id="employee_id" required>
                            <option value="" selected disabled>
                                {{ Auth::user()->isAdmin() ? 'Select Company First' : 'Select Employee' }}
                            </option>
                            @if (!Auth::user()->isAdmin())
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->id }}"
                                        {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->full_name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="title" class="form-label">Task Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                            id="title" placeholder="e.g. Sign Employment Contract" value="{{ old('title') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                        <select class="form-select @error('priority') is-invalid @enderror" name="priority">
                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date"
                            class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="due_date" class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                            value="{{ old('due_date') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="3"
                        placeholder="Enter task details...">{{ old('description') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select @error('status') is-invalid @enderror" name="status">
                        <option value="new" {{ old('status') == 'new' ? 'selected' : '' }}>New</option>
                        <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>In Progress
                        </option>
                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">Create Task</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function loadEmployees(companyId) {
            const empSelect = document.getElementById('employee_id');
            empSelect.innerHTML = '<option value="" selected disabled>Loading...</option>';
            empSelect.disabled = true;

            if (!companyId) {
                empSelect.innerHTML = '<option value="" selected disabled>Select Company First</option>';
                return;
            }

            fetch(`/admin/companies/${companyId}/employees-ajax`)
                .then(response => response.json())
                .then(data => {
                    empSelect.innerHTML = '<option value="" selected disabled>Select Employee</option>';
                    if (data.length === 0) {
                        empSelect.innerHTML += '<option value="" disabled>No employees found</option>';
                    }
                    data.forEach(emp => {
                        let option = document.createElement('option');
                        option.value = emp.id;
                        option.text = emp.name;
                        empSelect.appendChild(option);
                    });
                    empSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error:', error);
                    empSelect.innerHTML = '<option value="" selected disabled>Error loading employees</option>';
                });
        }
    </script>
@endpush
