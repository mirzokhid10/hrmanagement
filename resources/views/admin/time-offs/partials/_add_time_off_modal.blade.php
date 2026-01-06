<div class="modal fade" id="addTimeOffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title">Add Leave Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addTimeOffForm" action="{{ route('admin.time-offs.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="add_employee_id" class="form-label">Employee Name <span
                                class="text-danger">*</span></label>
                        <select name="employee_id" id="add_employee_id"
                            class="form-select @error('employee_id') is-invalid @enderror" required>
                            <option value="">Select Employee</option>
                            @foreach ($employeesForDropdown as $id => $name)
                                <option value="{{ $id }}" {{ old('employee_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="add_time_off_type_id" class="form-label">Leave Type <span
                                class="text-danger">*</span></label>
                        <select name="time_off_type_id" id="add_time_off_type_id"
                            class="form-select @error('time_off_type_id') is-invalid @enderror" required>
                            <option value="">Select leave type</option>
                            @foreach ($timeOffTypesForDropdown as $type)
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

                    <div class="mb-3">
                        <label for="add_start_date" class="form-label">Start Date <span
                                class="text-danger">*</span></label>
                        <input type="date" name="start_date" id="add_start_date"
                            class="form-control @error('start_date') is-invalid @enderror" required
                            value="{{ old('start_date') }}">
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="add_end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" id="add_end_date"
                            class="form-control @error('end_date') is-invalid @enderror" required
                            value="{{ old('end_date') }}">
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="add_reason" class="form-label">Reason</label>
                        <textarea name="reason" id="add_reason" class="form-control @error('reason') is-invalid @enderror" rows="3"
                            placeholder="Enter leave reason">{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Status for new requests is always Pending and disabled --}}
                    <div class="mb-3">
                        <label for="add_status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" id="add_status" class="form-select" disabled>
                            <option value="Pending" selected>Pending</option>
                        </select>
                        <input type="hidden" name="status" value="Pending"> {{-- Hidden field to ensure 'Pending' is sent --}}
                    </div>

                    {{-- Rejection reason is not applicable for new requests --}}
                    <div class="mb-3" style="display: none;">
                        <label for="add_rejection_reason" class="form-label">Rejection Reason</label>
                        <textarea name="rejection_reason" id="add_rejection_reason" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success">Submit Leave</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
