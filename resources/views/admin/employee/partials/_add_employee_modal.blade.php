<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title">Add Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.employee.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf {{-- IMPORTANT: Add CSRF token for security --}}

                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label for="firstName" class="form-label">First Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="first_name"
                                class="form-control @error('first_name') is-invalid @enderror" id="firstName"
                                placeholder="Enter first name" value="{{ old('first_name') }}">
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="lastName" class="form-label">Last Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="last_name"
                                class="form-control @error('last_name') is-invalid @enderror" id="lastName"
                                placeholder="Enter last name" value="{{ old('last_name') }}">
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address <span
                                    class="text-danger">*</span></label>
                            <input type="email" name="email"
                                class="form-control @error('email') is-invalid @enderror" id="email"
                                placeholder="example@email.com" value="{{ old('email') }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" name="phone_number"
                                class="form-control @error('phone_number') is-invalid @enderror" id="phone"
                                placeholder="+91 9876543210" value="{{ old('phone_number') }}">
                            @error('phone_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="department" class="form-label">Department <span
                                    class="text-danger">*</span></label>
                            <select class="form-select @error('department_id') is-invalid @enderror"
                                name="department_id" id="department">
                                <option value="" selected disabled>Select Department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="designation" class="form-label">Designation <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="job_title"
                                class="form-control @error('job_title') is-invalid @enderror" id="designation"
                                placeholder="e.g. Software Engineer" value="{{ old('job_title') }}">
                            @error('job_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="joiningDate" class="form-label">Joining Date <span
                                    class="text-danger">*</span></label>
                            <input type="date" name="hire_date"
                                class="form-control @error('hire_date') is-invalid @enderror" id="joiningDate"
                                value="{{ old('hire_date') }}">
                            @error('hire_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Employment Status <span
                                    class="text-danger">*</span></label>
                            {{-- Corrected name and id to 'status' --}}
                            <select class="form-select @error('status') is-invalid @enderror" name="status"
                                id="status">
                                <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>Active
                                </option>
                                <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive
                                </option>
                                <option value="Probation" {{ old('status') == 'Probation' ? 'selected' : '' }}>
                                    Probation</option>
                                <option value="Terminated" {{ old('status') == 'Terminated' ? 'selected' : '' }}>
                                    Terminated</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="dateOfBirth" class="form-label">Date Of Birth</label>
                            <input type="date" name="date_of_birth"
                                class="form-control @error('date_of_birth') is-invalid @enderror" id="dateOfBirth"
                                value="{{ old('date_of_birth') }}">
                            @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="salary" class="form-label">Salary</label>
                            <input class="form-control @error('salary') is-invalid @enderror" name="salary"
                                type="text" id="salary" value="{{ old('salary') }}">
                            @error('salary')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" name="address" id="address" rows="2"
                            placeholder="Enter address">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label for="photo" class="form-label">Profile Photo</label>
                            <input class="form-control @error('image') is-invalid @enderror" name="image"
                                type="file" id="photo">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-success">Add Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
