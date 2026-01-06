@extends('admin.layouts.master') {{-- Assuming you have an admin layout --}}

@section('content')
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="clearfix">
            <h1 class="app-page-title">Leaves</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Leaves</li>
                </ol>
            </nav>
        </div>
        {{-- Button to open the ADD modal --}}
        <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
            data-bs-target="#addTimeOffModal">
            <i class="fi fi-rr-plus me-1"></i> Add Leave
        </button>
    </div>

    <div class="row">

        {{-- These stats blocks are static for now, you might want to fetch dynamic data for them --}}
        <div class="col-xxl-3 col-md-6">
            <div class="card card-action action-border-primary">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div class="clearfix ps-2">
                        <div class="d-flex text-dark align-items-end gap-1 lh-1 mb-1">
                            <span class="fs-2 fw-bold">1192</span>
                            <span class="mb-1">/1206</span>
                        </div>
                        <span class="text-primary">Today Presents</span>
                    </div>
                    <div class="clearfix">
                        <div id="leavesPresentsScore"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-md-6">
            <div class="card card-action action-border-danger">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div class="clearfix ps-2">
                        <div class="d-flex text-dark align-items-end gap-1 lh-1 mb-1">
                            <span class="fs-2 fw-bold">128</span>
                            <span class="mb-1">1206</span>
                        </div>
                        <span class="text-danger">Planned Leaves</span>
                    </div>
                    <div class="clearfix">
                        <div id="leavesPlannedScore"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-md-6">
            <div class="card card-action action-border-info">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div class="clearfix ps-2">
                        <div class="d-flex text-dark align-items-end gap-1 lh-1 mb-1">
                            <span class="fs-2 fw-bold">12</span>
                            <span class="mb-1">/1206</span>
                        </div>
                        <span class="text-info">Unplanned Leaves</span>
                    </div>
                    <div class="clearfix">
                        <div id="leavesUnplannedScore"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-md-6">
            <div class="card card-action action-border-secondary">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div class="clearfix ps-2">
                        <div class="d-flex text-dark align-items-end gap-1 lh-1 mb-1">
                            <span class="fs-2 fw-bold">{{ $timeOffs->where('status', 'Pending')->count() }}</span>
                            <span class="mb-1">/{{ $timeOffs->total() }}</span> {{-- Total from current pagination --}}
                        </div>
                        <span class="text-secondary">Pending Requests</span>
                    </div>
                    <div class="clearfix">
                        <div id="leavesPendingScore"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card overflow-hidden">
                <div class="card-header d-flex gap-3 flex-wrap align-items-center justify-content-between border-0 pb-0">
                    <h6 class="card-title mb-0">Employee’s Leave</h6>
                    <div class="d-flex gap-3 flex-wrap">
                        <div id="dt_PageEmployeeLeave_Search"></div>
                        <button type="button" class="btn btn-sm btn-outline-light btn-shadow waves-effect">Download
                            Report</button>
                        <select class="selectpicker" data-style="btn-sm btn-outline-light btn-shadow waves-effect">
                            <option value="pending">2024</option>
                            <option>2023</option>
                            <option>2022</option>
                            <option>2021</option>
                        </select>
                    </div>
                </div>
                <div class="card-body p-2">


                    <div class="table-responsive">
                        <table id="dt_PageEmployeeLeave" class="table display table-row-rounded">
                            <thead class="table-light">
                                <tr>
                                    <th class="minw-200px">Name</th>
                                    <th class="minw-150px">Leave Type</th>
                                    <th class="minw-200px">Department</th>
                                    <th class="minw-150px">Days</th>
                                    <th class="minw-150px">Start</th>
                                    <th class="minw-150px">End</th>
                                    <th class="minw-100px">Status</th>
                                    <th class="minw-100px text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($timeOffs as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center mw-175px">
                                                <div class="avatar avatar-xxs rounded-circle">
                                                    <img src="{{ $item->employee->profile_image_url ?? asset('assets/default-avatar.png') }}"
                                                        alt="no">

                                                </div>
                                                <div class="ms-2 me-auto">{{ $item->employee?->full_name }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            <span
                                                class="
                                                {{ $item->type->name === 'Vacation'
                                                    ? 'custom-green'
                                                    : ($item->type->name === 'Sick Leave'
                                                        ? 'custom-red'
                                                        : ($item->type->name === 'Personal Leave'
                                                            ? 'custom-orange'
                                                            : 'custom-default')) }}
                                            ">
                                                {{ $item->type->name }}
                                            </span>
                                        </td>
                                        <td> {{ $item->employee->job_title }}</td>
                                        <td>{{ (int) $item->total_days }} days Days</td>
                                        <td>{{ $item->start_date?->format('j M Y') }}</td>
                                        <td>{{ $item->end_date?->format('j M Y') }}</td>
                                        <td>
                                            {{--    Rule::in(['Pending', 'Approved', 'Rejected', 'Cancelled']), --}}
                                            <div class="dropdown select-status">
                                                <button
                                                    class="btn btn-sm {{ $item->status === 'Pending'
                                                        ? 'btn-subtle-success'
                                                        : ($item->status === 'Approved'
                                                            ? 'btn-subtle-primary'
                                                            : ($item->status === 'Rejected'
                                                                ? 'btn-subtle-secondary'
                                                                : 'btn-outline-light')) }} dropdown-toggle waves-effect waves-light"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    {{ $item->status }}
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    {{-- These dropdown items could trigger AJAX status updates --}}
                                                    <li><a class="dropdown-item" href="javascript:void(0);"
                                                            data-status="Pending">Pending</a></li>
                                                    <li><a class="dropdown-item" href="javascript:void(0);"
                                                            data-status="Approved">Approved</a></li>
                                                    <li><a class="dropdown-item" href="javascript:void(0);"
                                                            data-status="Rejected">Rejected</a></li>
                                                    <li><a class="dropdown-item" href="javascript:void(0);"
                                                            data-status="Cancelled">Cancelled</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group d-flex justify-content-center">
                                                <button
                                                    class="btn btn-white btn-sm btn-shadow btn-icon waves-effect dropdown-toggle"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a href="javascript:void(0);"
                                                            class="dropdown-item edit-time-off-btn" data-bs-toggle="modal"
                                                            data-bs-target="#editTimeOffModal"
                                                            data-time-off-id="{{ $item->id }}">
                                                            Edit
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('admin.time-offs.destroy', $item->id) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item"
                                                                onclick="return confirm('Are you sure you want to delete this time-off request?')">Delete</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Rejection Modals (kept separate as they are specific to index page actions) --}}
        @foreach ($timeOffs as $timeOffItem)
            @if ($timeOffItem->isPending())
                <div class="modal fade" id="rejectModal-{{ $timeOffItem->id }}" tabindex="-1"
                    aria-labelledby="rejectModalLabel-{{ $timeOffItem->id }}" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="rejectModalLabel-{{ $timeOffItem->id }}">Reject Time-Off
                                    Request
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <form action="{{ route('admin.time-offs.reject', $timeOffItem) }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="rejection_reason-{{ $timeOffItem->id }}">Reason for rejection:</label>
                                        <textarea name="rejection_reason" id="rejection_reason-{{ $timeOffItem->id }}" class="form-control" rows="3"
                                            required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-danger">Reject Request</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        {{-- Include the new dynamic Time Off Modals --}}
        @include('admin.time-offs.partials._add_time_off_modal', [
            'employeesForDropdown' => $employeesForDropdown,
            'timeOffTypesForDropdown' => $timeOffTypesForDropdown,
        ])
        @include('admin.time-offs.partials._edit_time_off_modal', [
            'employeesForDropdown' => $employeesForDropdown,
            'timeOffTypesForDropdown' => $timeOffTypesForDropdown,
        ])
    @endsection

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // --- Common Elements ---
                const employeesData = @json($employeesForDropdown); // Employee ID -> Name (Job Title)
                const timeOffTypesData = @json($timeOffTypesForDropdown); // Full type objects for paid/unpaid info

                let flatpickrInstances = []; // To store Flatpickr instances

                const updateTimeOffRouteTemplate =
                    "{{ route('admin.time-offs.update', ['time_off' => ':timeOffId']) }}";

                function initFlatpickr(selector, modalElement) {
                    // Destroy existing instances only within the specific modal to avoid conflicts
                    flatpickrInstances = flatpickrInstances.filter(instance => {
                        if (modalElement.contains(instance.element)) {
                            instance.destroy();
                            return false;
                        }
                        return true;
                    });
                    const elements = modalElement.querySelectorAll(selector);
                    elements.forEach(el => {
                        const fp = flatpickr(el, {
                            dateFormat: "Y-m-d",
                        });
                        flatpickrInstances.push(fp);
                    });
                }

                // --- Add Time Off Modal Logic ---
                const addTimeOffModalElement = document.getElementById('addTimeOffModal');
                const addTimeOffForm = document.getElementById('addTimeOffForm');
                const addStatusSelect = addTimeOffForm.querySelector('#add_status');
                const addEmployeeIdSelect = addTimeOffForm.querySelector('#add_employee_id');
                const addTimeOffTypeIdSelect = addTimeOffForm.querySelector('#add_time_off_type_id');

                addTimeOffModalElement.addEventListener('show.bs.modal', function() {
                    addTimeOffForm.reset();
                    addTimeOffForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove(
                        'is-invalid'));
                    addTimeOffForm.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

                    addStatusSelect.value = 'Pending';
                    addStatusSelect.disabled = true;

                    addEmployeeIdSelect.innerHTML = '<option value="">Select Employee</option>';
                    for (const id in employeesData) {
                        const option = document.createElement('option');
                        option.value = id;
                        option.textContent = employeesData[id];
                        addEmployeeIdSelect.appendChild(option);
                    }
                    addTimeOffTypeIdSelect.innerHTML = '<option value="">Select leave type</option>';
                    timeOffTypesData.forEach(type => {
                        const option = document.createElement('option');
                        option.value = type.id;
                        option.textContent = `${type.name} (${type.is_paid ? 'Paid' : 'Unpaid'})`;
                        addTimeOffTypeIdSelect.appendChild(option);
                    });

                    initFlatpickr('.flatpickr-date', addTimeOffModalElement);
                });

                // --- Edit Time Off Modal Logic ---
                const editTimeOffModalElement = document.getElementById('editTimeOffModal');
                const editTimeOffForm = document.getElementById('editTimeOffForm');
                const editTimeOffIdField = editTimeOffForm.querySelector('#edit_time_off_id_field');
                const editEmployeeIdSelect = editTimeOffForm.querySelector('#edit_employee_id');
                const editTimeOffTypeIdSelect = editTimeOffForm.querySelector('#edit_time_off_type_id');
                const editStartDateInput = editTimeOffForm.querySelector('#edit_start_date');
                const editEndDateInput = editTimeOffForm.querySelector('#edit_end_date');
                const editReasonTextarea = editTimeOffForm.querySelector('#edit_reason');
                const editStatusSelect = editTimeOffForm.querySelector('#edit_status');
                const editRejectionReasonGroup = editTimeOffForm.querySelector('#edit_rejection_reason_group');
                const editRejectionReasonTextarea = editTimeOffForm.querySelector('#edit_rejection_reason');

                function toggleEditRejectionReason() {
                    if (editStatusSelect.value === 'Rejected') {
                        editRejectionReasonGroup.style.display = 'block';
                        editRejectionReasonTextarea.setAttribute('required', 'required');
                    } else {
                        editRejectionReasonGroup.style.display = 'none';
                        editRejectionReasonTextarea.removeAttribute('required');
                    }
                }
                editStatusSelect.addEventListener('change', toggleEditRejectionReason);


                editTimeOffModalElement.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget; // Button that triggered the modal
                    const timeOffId = button.getAttribute('data-time-off-id'); // Get time-off ID

                    console.log('Opening Edit Modal for TimeOff ID:', timeOffId);

                    if (!timeOffId) {
                        console.error('Error: timeOffId is null or undefined when opening edit modal!');
                        // Potentially disable the form or show an error to the user
                        return; // Stop execution if ID is missing
                    }
                    // Reset form and validation errors
                    editTimeOffForm.reset();
                    editTimeOffForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove(
                        'is-invalid'));
                    editTimeOffForm.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

                    editTimeOffIdField.value = timeOffId;
                    // --- THIS IS THE CRUCIAL CHANGE ---
                    const formAction = updateTimeOffRouteTemplate.replace(':timeOffId', timeOffId);
                    editTimeOffForm.setAttribute('action', formAction);
                    console.log('Edit form action set to:', formAction);
                    // --- END CRUCIAL CHANGE --- //
                    editStatusSelect.disabled = false; // Status can be changed in edit mode

                    // Re-populate dropdowns
                    editEmployeeIdSelect.innerHTML = '<option value="">Select Employee</option>';
                    for (const id in employeesData) {
                        const option = document.createElement('option');
                        option.value = id;
                        option.textContent = employeesData[id];
                        editEmployeeIdSelect.appendChild(option);
                    }
                    editTimeOffTypeIdSelect.innerHTML = '<option value="">Select leave type</option>';
                    timeOffTypesData.forEach(type => {
                        const option = document.createElement('option');
                        option.value = type.id;
                        option.textContent = `${type.name} (${type.is_paid ? 'Paid' : 'Unpaid'})`;
                        editTimeOffTypeIdSelect.appendChild(option);
                    });

                    fetch(`/admin/time-offs/${timeOffId}/edit`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            editEmployeeIdSelect.value = data.employee_id;
                            editTimeOffTypeIdSelect.value = data.time_off_type_id;
                            editStartDateInput.value = data.start_date;
                            editEndDateInput.value = data.end_date;
                            editReasonTextarea.value = data.reason || '';
                            editStatusSelect.value = data.status;
                            editRejectionReasonTextarea.value = data.rejection_reason || '';
                            toggleEditRejectionReason(); // Ensure rejection reason visibility is correct
                        })
                        .catch(error => console.error('Error fetching time off data:', error));

                    // Init Flatpickr for this modal
                    initFlatpickr('.flatpickr-date', editTimeOffModalElement);
                });

                // --- Handle validation errors and modal reopening (for ADD modal) ---
                @if ($errors->any() && session('add_time_off_modal_open_on_error'))
                    var addModalInstance = new bootstrap.Modal(addTimeOffModalElement);
                    addModalInstance.show();
                    // Old input values are already rendered by Blade's old() helper
                    // We just need to ensure Flatpickr is initialized
                    initFlatpickr('.flatpickr-date', addTimeOffModalElement);
                @endif

                // --- Handle validation errors and modal reopening (for EDIT modal) ---
                @if ($errors->any() && session('edit_time_off_modal_open_on_error'))
                    var editModalInstance = new bootstrap.Modal(editTimeOffModalElement);
                    editModalInstance.show();
                    // We need to re-fetch data for edit modal if it's reopened due to error
                    const timeOffIdOnError = "{{ session('edit_time_off_id_on_error') }}";
                    if (timeOffIdOnError) {
                        // --- IMPORTANT: Set the form action here too when re-opening on error ---
                        const formActionOnError = updateTimeOffRouteTemplate.replace(':timeOffId', timeOffIdOnError);
                        editTimeOffForm.setAttribute('action', formActionOnError);
                        console.log('Edit form action re-set on error to:', formActionOnError);
                        // --- END IMPORTANT ---
                        // Manually trigger the fetch logic as if the edit button was clicked
                        fetch(`/admin/time-offs/${timeOffIdOnError}/edit`, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                editEmployeeIdSelect.value = old('employee_id', data
                                    .employee_id); // Use old() for errors
                                editTimeOffTypeIdSelect.value = old('time_off_type_id', data.time_off_type_id);
                                editStartDateInput.value = old('start_date', data.start_date);
                                editEndDateInput.value = old('end_date', data.end_date);
                                editReasonTextarea.value = old('reason', data.reason || '');
                                editStatusSelect.value = old('status', data.status);
                                editRejectionReasonTextarea.value = old('rejection_reason', data.rejection_reason ||
                                    '');
                                toggleEditRejectionReason(); // Ensure rejection reason visibility is correct

                                // Re-apply invalid classes and feedback from server-side errors
                                @foreach ($errors->keys() as $key)
                                    const input = editTimeOffForm.querySelector(`[name="{{ $key }}"]`);
                                    if (input) {
                                        input.classList.add('is-invalid');
                                        let feedback = input.parentNode.querySelector('.invalid-feedback');
                                        if (!feedback) {
                                            feedback = document.createElement('div');
                                            feedback.classList.add('invalid-feedback');
                                            input.parentNode.appendChild(feedback);
                                        }
                                        feedback.textContent = "{{ $errors->first($key) }}";
                                    }
                                @endforeach
                            })
                            .catch(error => console.error('Error re-fetching time off data on error:', error));
                    }
                    initFlatpickr('.flatpickr-date', editTimeOffModalElement);
                @endif

                // --- Global employee list search and pagination (from your template) ---
                const employeeListContainer = document.getElementById(
                    'dt_PageEmployeeLeave'); // Assuming this is your table wrapper or a parent of it
                const filterStatusSelect = document.getElementById('filter_status');
                const filterEmployeeIdSelect = document.getElementById('filter_employee_id');

                function fetchTimeOffs(page = 1) {
                    const status = filterStatusSelect.value;
                    const employeeId = filterEmployeeIdSelect.value;
                    const url =
                        `{{ route('admin.time-offs.index') }}?page=${page}&status=${status}&employee_id=${employeeId}`;

                    fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'text/html'
                            }
                        })
                        .then(response => response.text())
                        .then(html => {
                            // This part is tricky. If you want to update *only the table body*,
                            // you'd need the server to return just the table body HTML.
                            // For now, this will replace the entire #dt_PageEmployeeLeave element or its container.
                            // You might need a dedicated partial for just the table to make this truly dynamic.
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newTableBody = doc.querySelector('#dt_PageEmployeeLeave tbody');
                            const newPagination = doc.querySelector(
                                '.pagination'); // Assuming pagination is inside a div with this class

                            if (newTableBody) {
                                employeeListContainer.querySelector('tbody').innerHTML = newTableBody.innerHTML;
                            }
                            if (newPagination) {
                                // Find the existing pagination wrapper and replace its content
                                const existingPaginationWrapper = employeeListContainer.closest('.card-body')
                                    .querySelector('.d-flex.justify-content-center');
                                if (existingPaginationWrapper) {
                                    existingPaginationWrapper.innerHTML = newPagination.outerHTML;
                                }
                            }
                            // Re-attach event listeners for newly loaded elements
                            attachDynamicEventListeners();
                        })
                        .catch(error => console.error('Error fetching time-offs:', error));
                }

                // Event listeners for filters
                filterStatusSelect.addEventListener('change', () => fetchTimeOffs(1));
                filterEmployeeIdSelect.addEventListener('change', () => fetchTimeOffs(1));

                // Event delegation for pagination links (assuming they are inside a .d-flex.justify-content-center container)
                document.querySelector('.d-flex.justify-content-center').addEventListener('click', function(e) {
                    if (e.target.matches('.pagination .page-link')) {
                        e.preventDefault();
                        const pageUrl = new URL(e.target.href);
                        const page = pageUrl.searchParams.get('page') || 1;
                        fetchTimeOffs(page);
                    }
                });


                // --- Re-attach dynamic event listeners (for edit/delete buttons, status dropdowns) ---
                function attachDynamicEventListeners() {
                    // Re-attach Edit button listeners for newly loaded rows
                    document.querySelectorAll('.edit-time-off-btn').forEach(button => {
                        button.removeEventListener('click',
                            handleEditButtonClick); // Prevent multiple listeners
                        button.addEventListener('click', handleEditButtonClick);
                    });

                    // Re-attach Delete button listeners (if using AJAX delete)
                    // For now, using form submission with confirm, so no specific JS listener needed unless you want AJAX delete.

                    // Re-attach status dropdown listeners (if you want AJAX updates for status directly from dropdown)
                    document.querySelectorAll('.select-status .dropdown-item').forEach(item => {
                        item.removeEventListener('click', handleStatusChange); // Prevent multiple listeners
                        item.addEventListener('click', handleStatusChange);
                    });
                }

                function handleEditButtonClick(event) {
                    const button = event.currentTarget;
                    const timeOffId = button.getAttribute('data-time-off-id');
                    // This will trigger the show.bs.modal event listener on editTimeOffModalElement
                    const editModalInstance = new bootstrap.Modal(editTimeOffModalElement);
                    editModalInstance.show(button); // Pass the button to access data- attributes
                }

                function handleStatusChange(event) {
                    event.preventDefault();
                    const newStatus = event.target.getAttribute('data-status');
                    const timeOffRow = event.target.closest('tr');
                    const timeOffId = event.target.getAttribute(
                        'data-time-off-id'); // Get ID directly from dropdown item

                    let rejectionReason = null;
                    if (newStatus === 'Rejected') {
                        rejectionReason = prompt('Please enter the reason for rejection:');
                        if (!rejectionReason) {
                            alert('Rejection cancelled. Status not changed.');
                            return;
                        }
                    }

                    fetch(`{{ url('admin/time-offs') }}/${timeOffId}/status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                status: newStatus,
                                rejection_reason: rejectionReason
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const statusButton = timeOffRow.querySelector('.select-status button');
                                statusButton.textContent = data.new_status;
                                statusButton.className = `btn btn-sm dropdown-toggle waves-effect waves-light ${
                            data.new_status === 'Pending' ? 'btn-subtle-warning' :
                            (data.new_status === 'Approved' ? 'btn-subtle-primary' :
                            (data.new_status === 'Rejected' ? 'btn-subtle-secondary' : 'btn-subtle-info'))
                        }`;
                                alert(data.message);
                            } else {
                                alert(data.message || 'Failed to update status.');
                                if (data.errors) {
                                    console.error('Validation errors:', data.errors);
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error updating status:', error);
                            alert('An error occurred while updating status.');
                        });
                }

                attachDynamicEventListeners();
            });
        </script>
    @endpush
