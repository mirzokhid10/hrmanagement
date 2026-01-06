<div class="modal fade" id="editTimeOffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title">Edit Leave Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editTimeOffForm" action="" method="POST">
                    @csrf
                    @method('PUT') {{-- Always PUT for updates --}}

                    <input type="hidden" name="time_off_id" id="edit_time_off_id_field"> {{-- Hidden field for time off ID --}}

                    <div class="mb-3">
                        <label for="edit_employee_id" class="form-label">Employee Name <span
                                class="text-danger">*</span></label>
                        <select name="employee_id" id="edit_employee_id"
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
                        <label for="edit_time_off_type_id" class="form-label">Leave Type <span
                                class="text-danger">*</span></label>
                        <select name="time_off_type_id" id="edit_time_off_type_id"
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
                        <label for="edit_start_date" class="form-label">Start Date <span
                                class="text-danger">*</span></label>
                        <input type="date" name="start_date" id="edit_start_date"
                            class="form-control @error('start_date') is-invalid @enderror" required
                            value="{{ old('start_date') }}">
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="edit_end_date" class="form-label">End Date <span
                                class="text-danger">*</span></label>
                        <input type="date" name="end_date" id="edit_end_date"
                            class="form-control @error('end_date') is-invalid @enderror" required
                            value="{{ old('end_date') }}">
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="edit_reason" class="form-label">Reason</label>
                        <textarea name="reason" id="edit_reason" class="form-control @error('reason') is-invalid @enderror" rows="3"
                            placeholder="Enter leave reason">{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" id="edit_status"
                            class="form-select @error('status') is-invalid @enderror">
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3" id="edit_rejection_reason_group" style="display: none;">
                        <label for="edit_rejection_reason" class="form-label">Rejection Reason <span
                                class="text-danger">*</span></label>
                        <textarea name="rejection_reason" id="edit_rejection_reason"
                            class="form-control @error('rejection_reason') is-invalid @enderror" rows="3"
                            placeholder="Enter reason for rejection">{{ old('rejection_reason') }}</textarea>
                        @error('rejection_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success">Update Leave</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
