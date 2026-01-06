@extends('admin.layouts.master')

@section('content')
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="clearfix">
            <h1 class="app-page-title">
                <span class="text-primary">{{ $employees->total() }}</span> Employee {{-- Display total employees --}}
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="https://gxon.layoutdrop.com/laravel/demo">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Employee</li>
                </ol>
            </nav>
        </div>
        <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
            data-bs-target="#addEmployeeModal">
            <i class="fi fi-rr-plus me-1"></i> Add Employee
        </button>
    </div>
    <div class="card d-flex flex-row flex-wrap align-items-center h-auto mb-5">
        <ul class="nav nav-underline me-auto px-3 gap-2">
            <li class="nav-item">
                <a class="nav-link border-3 py-3 px-2 active" href="javascript:void(0);">Employee</a>
            </li>
            <li class="nav-item">
                <a class="nav-link border-3 py-3 px-2" href="leave.html">Leave Request</a>
            </li>
        </ul>
        <div class="d-flex ps-3">
            <div class="d-flex align-items-center me-4">
                <button class="btn btn-link p-0 me-3 text-primary">
                    <i class="fi fi-rr-apps text-sm"></i>
                </button>
                <button class="btn btn-link p-0 text-body">
                    <i class="fi fi-br-list text-sm"></i>
                </button>
            </div>
            <div class="vr"></div>
            <form id="employeeSearchForm" class="d-flex align-items-center h-100 w-150px w-lg-300px position-relative"
                action="{{ route('admin.employee.index') }}" method="GET"> {{-- Corrected form action and method --}}
                <button type="submit" class="btn btn-sm border-0 position-absolute start-0 ms-3 p-0"> {{-- Changed to type="submit" --}}
                    <i class="fi fi-rr-search"></i>
                </button>
                <input type="text" name="search" id="employeeSearchInput" {{-- Added name="search" and id --}}
                    class="form-control form-control-lg ps-5 rounded-start-0 border-0 shadow-none bg-transparent"
                    placeholder="Search Employee" value="{{ $searchTerm }}"> {{-- Sticky search term --}}
            </form>
        </div>
    </div>

    {{-- Include the employee list partial here --}}
    <div id="employee-list-container"> {{-- Wrapper for dynamic updates --}}
        @include('admin.employee.partials._employee_list', ['employees' => $employees])
    </div>

    {{-- Modals --}}
    @include('admin.employee.partials._add_employee_modal', ['departments' => $departments])
    @include('admin.employee.partials._edit_employee_modal', ['departments' => $departments])

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Common Elements ---
            const employeeListContainer = document.getElementById('employee-list-container');
            const employeeSearchInput = document.getElementById('employeeSearchInput');
            const employeeSearchForm = document.getElementById('employeeSearchForm');
            let searchTimeout = null;

            // --- Function to fetch and update employee list ---
            function fetchEmployees(page = 1, searchTerm = '') {
                const url = `{{ route('admin.employee.index') }}?page=${page}&search=${searchTerm}`;
                // Optional: Show a loading indicator
                // employeeListContainer.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest', // Important for Laravel's request()->ajax()
                            'Accept': 'text/html' // Expect HTML response
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.text(); // Get response as HTML text
                    })
                    .then(html => {
                        employeeListContainer.innerHTML = html; // Replace the content
                        // Re-attach event listeners for dynamically loaded elements (edit/delete buttons)
                        attachDynamicEventListeners();
                    })
                    .catch(error => {
                        console.error('Error fetching employees:', error);
                        // employeeListContainer.innerHTML = '<div class="alert alert-danger">Failed to load employees. Please try again.</div>';
                    });
            }

            // --- Debounced Search Input Handler ---
            employeeSearchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const searchTerm = this.value;
                searchTimeout = setTimeout(() => {
                    fetchEmployees(1, searchTerm); // Always go to page 1 on new search
                }, 300); // Debounce for 300ms
            });

            // --- Intercept Pagination Clicks (Event Delegation) ---
            // Since pagination links are dynamically loaded, we need event delegation
            employeeListContainer.addEventListener('click', function(e) {
                if (e.target.matches('.pagination .page-link')) {
                    e.preventDefault(); // Prevent default link behavior
                    const pageUrl = new URL(e.target.href);
                    const page = pageUrl.searchParams.get('page') || 1;
                    const searchTerm = employeeSearchInput.value; // Use current search term
                    fetchEmployees(page, searchTerm);
                }
            });

            // --- Re-attach dynamic event listeners (for edit/delete buttons) ---
            function attachDynamicEventListeners() {
                // Attach Edit button listeners
                const editEmployeeModal = new bootstrap.Modal(document.getElementById('editEmployeeModal'));
                const editEmployeeForm = document.getElementById('editEmployeeForm');
                const currentProfileImageContainer = document.getElementById('current_profile_image_container');
                const currentProfileImagePreview = document.getElementById('current_profile_image_preview');
                const removeImageCheckbox = document.getElementById('removeImageCheckbox');

                document.querySelectorAll('.edit-employee-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const employeeId = this.dataset.id;
                        const url = `{{ route('admin.employee.edit', ':id') }}`.replace(':id',
                            employeeId);

                        editEmployeeForm.querySelectorAll('.is-invalid').forEach(el => el.classList
                            .remove('is-invalid'));
                        editEmployeeForm.querySelectorAll('.invalid-feedback').forEach(el => el
                            .innerHTML = '');

                        fetch(url, {
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
                                document.getElementById('edit_employee_id').value = data.id;
                                document.getElementById('edit_firstName').value = data
                                    .first_name;
                                document.getElementById('edit_lastName').value = data.last_name;
                                document.getElementById('edit_email').value = data.email;
                                document.getElementById('edit_phone').value = data
                                    .phone_number || '';
                                document.getElementById('edit_designation').value = data
                                    .job_title;
                                document.getElementById('edit_joiningDate').value = data
                                    .hire_date;
                                document.getElementById('edit_status').value = data.status;
                                document.getElementById('edit_dateOfBirth').value = data
                                    .date_of_birth || '';
                                document.getElementById('edit_address').value = data.address ||
                                    '';
                                document.getElementById('edit_salary').value = data.salary ||
                                    '';

                                document.getElementById('edit_department').value = data
                                    .department_id;

                                editEmployeeForm.action =
                                    `{{ route('admin.employee.update', ':id') }}`.replace(':id',
                                        data.id);

                                if (data.profile_image_url) {
                                    currentProfileImagePreview.src = data.profile_image_url;
                                    currentProfileImageContainer.style.display = 'block';
                                    removeImageCheckbox.checked = false;
                                } else {
                                    currentProfileImageContainer.style.display = 'none';
                                    currentProfileImagePreview.src = '';
                                }

                                editEmployeeModal.show();
                            })
                            .catch(error => {
                                console.error('Error fetching employee data:', error);
                                alert('Failed to load employee data. Please try again.');
                            });
                    });
                });

                // Attach Delete button listeners (if using AJAX delete)
                // For now, using form submission with confirm, so no specific JS listener needed unless you want AJAX delete.
            }

            // --- Initial attachment of event listeners ---
            attachDynamicEventListeners();

            // --- Reinitialize Flatpickr for the modals' date inputs ---
            // For Add Modal
            const addEmployeeModalElement = document.getElementById('addEmployeeModal');
            if (addEmployeeModalElement) {
                addEmployeeModalElement.addEventListener('shown.bs.modal', function() {
                    flatpickr("#addEmployeeModal .flatpickr-date", {
                        dateFormat: "Y-m-d",
                    });
                });
            }

            // For Edit Modal
            const editEmployeeModalElement = document.getElementById('editEmployeeModal');
            if (editEmployeeModalElement) {
                editEmployeeModalElement.addEventListener('shown.bs.modal', function() {
                    flatpickr("#editEmployeeModal .flatpickr-date", {
                        dateFormat: "Y-m-d",
                    });
                });
            }

            // --- Handle validation errors and modal reopening ---
            @if ($errors->any() && session('edit_modal_open'))
                editEmployeeModal.show();
                const employeeId = "{{ session('edit_employee_id_on_error') }}";
                if (employeeId) {
                    const editButton = document.querySelector(`.edit-employee-btn[data-id="${employeeId}"]`);
                    if (editButton) {
                        editButton.click();
                    }
                }
            @endif

            @if ($errors->any() && session('add_modal_open'))
                {{-- Assuming you set this in controller for add modal errors --}}
                var addModal = new bootstrap.Modal(document.getElementById('addEmployeeModal'));
                addModal.show();
            @endif

        });
    </script>
@endsection
