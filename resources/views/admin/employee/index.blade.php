@extends('admin.layouts.master')

@section('content')
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="clearfix">
            <h1 class="app-page-title">
                <span class="text-primary">{{ $employees->total() }}</span> Employee
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Employee</li>
                </ol>
            </nav>
        </div>
        {{-- Changed button to a link to the dedicated create page --}}
        <a href="{{ route('admin.employee.create') }}" class="btn btn-primary waves-effect waves-light">
            <i class="fi fi-rr-plus me-1"></i> Add Employee
        </a>
    </div>

    <div class="card d-flex flex-row flex-wrap align-items-center h-auto mb-5">
        <ul class="nav nav-underline me-auto px-3 gap-2">
            <li class="nav-item">
                <a class="nav-link border-3 py-3 px-2 active" href="javascript:void(0);">Employee</a>
            </li>
            <li class="nav-item">
                <a class="nav-link border-3 py-3 px-2" href="leave.html">Leave Request</a> {{-- Placeholder --}}
            </li>
        </ul>
        <div class="d-flex ps-3">
            <div class="d-flex align-items-center me-4">
                {{-- These buttons might imply a view toggle, which would require JS. Keep as placeholders for now. --}}
                <button class="btn btn-link p-0 me-3 text-primary">
                    <i class="fi fi-rr-apps text-sm"></i>
                </button>
                <button class="btn btn-link p-0 text-body">
                    <i class="fi fi-br-list text-sm"></i>
                </button>
            </div>
            <div class="vr"></div>
            <div class="d-flex align-items-center h-100 w-150px w-lg-300px position-relative m-0">
                <button type="button" class="btn btn-sm border-0 position-absolute start-0 ms-3 p-0">
                    <i class="fi fi-rr-search"></i>
                </button>
                <input type="text" name="search" id="employeeSearchInput"
                    class="form-control form-control-lg ps-5 rounded-start-0 border-0 shadow-none bg-transparent"
                    placeholder="Search Employee" value="{{ $searchTerm }}">
            </div>
        </div>
    </div>

    {{-- The container where the employee list partial will be loaded/updated --}}
    <div id="employee-list-container">
        @include(
            'admin.employee.partials._employee_list',
            compact('employees', 'departments', 'searchTerm', 'companies'))
    </div>
@endsection

@push('scripts')
    {{-- Use @push('scripts') if your master layout uses @stack('scripts') --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const employeeListContainer = document.getElementById('employee-list-container');
            const employeeSearchInput = document.getElementById('employeeSearchInput');
            let searchTimeout; // For debouncing

            // Function to fetch employees via AJAX
            function fetchEmployees(page = 1, searchTerm = '') {
                const url = new URL("{{ route('admin.employee.index') }}");
                url.searchParams.append('page', page);
                if (searchTerm) {
                    url.searchParams.append('search', searchTerm);
                }

                // Add a header to indicate an AJAX request to the server
                fetch(url.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.text();
                    })
                    .then(html => {
                        employeeListContainer.innerHTML = html; // Update the list container
                        // Optional: Update URL in browser history without reloading
                        history.pushState({
                            page: page,
                            search: searchTerm
                        }, '', url.toString());
                    })
                    .catch(error => {
                        console.error('Error fetching employees:', error);
                        // Optionally display an error message to the user
                        // For example: employeeListContainer.innerHTML = '<p class="text-danger">Failed to load employees. Please try again.</p>';
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

            // --- Pagination Click Handler ---
            // Using event delegation on the container for dynamically loaded pagination links
            employeeListContainer.addEventListener('click', function(e) {
                if (e.target.matches('.pagination .page-link')) {
                    e.preventDefault(); // Prevent default link behavior
                    const pageUrl = new URL(e.target.href);
                    const page = pageUrl.searchParams.get('page') || 1;
                    const searchTerm = employeeSearchInput.value; // Use current search term
                    fetchEmployees(page, searchTerm);
                }
            });

            // Optional: Handle browser back/forward buttons
            window.addEventListener('popstate', function(event) {
                if (event.state && event.state.page) {
                    const page = event.state.page;
                    const searchTerm = event.state.search || '';
                    // Re-fetch employees based on history state, but prevent adding to history again
                    fetchEmployees(page, searchTerm);
                    employeeSearchInput.value = searchTerm; // Update search input field
                }
            });
        });
    </script>
@endpush
