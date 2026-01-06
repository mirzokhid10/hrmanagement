{{-- resources/views/admin/employee/partials/_employee_list.blade.php --}}

@if ($employees->count() > 0)
    <div class="row" id="employee-cards-container"> {{-- Add an ID to easily target with JS --}}
        @foreach ($employees as $employee)
            <div class="col-xxl-3 col-lg-4 col-md-6">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0 p-3">
                        <span
                            class="badge {{ $employee->status === 'Active'
                                ? 'bg-success-subtle text-success'
                                : ($employee->status === 'Inactive'
                                    ? 'bg-secondary-subtle text-secondary'
                                    : ($employee->status === 'Probation'
                                        ? 'bg-warning-subtle text-warning'
                                        : ($employee->status === 'Terminated'
                                            ? 'bg-danger-subtle text-danger'
                                            : 'bg-primary-subtle text-primary'))) }}">{{ $employee->status }}</span>
                        <div class="clearfix">
                            <div class="btn-group">
                                <button class="btn btn-white btn-sm btn-shadow btn-icon waves-effect dropdown-toggle"
                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a href="{{ route('admin.employee.edit', $employee->id) }}"
                                            class="dropdown-item edit-employee-btn"> Edit
                                        </a>
                                    </li>
                                    <li>
                                        <form action="{{ route('admin.employee.destroy', $employee->id) }}"
                                            method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm border-none dropdown-item"
                                                onclick="return confirm('Are you sure you want to delete {{ $employee->fullName }}?');">
                                                Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-2 pt-0">
                        <div class="text-center mb-3">
                            <div class="avatar avatar-xxl rounded-4 mx-auto mb-3">
                                <img src="{{ $employee->profile_image_url ?? asset('assets/default-avatar.png') }}"
                                    alt="{{ $employee->full_name }}">
                            </div>
                            <h5 class="mb-0 fw-bold">{{ $employee->full_name }}</h5>
                            <p class="text-primary mb-0">{{ $employee->job_title }}</p>
                        </div>
                        <div class="p-3 bg-light rounded">
                            <div class="d-flex gap-3">
                                <div class="w-50">
                                    <span class="text-1xs">Department</span>
                                    <h6 class="mb-0">{{ $employee->department->name ?? 'N/A' }}</h6>
                                </div>
                                <div class="w-50">
                                    <span class="text-1xs">Hired Date</span>
                                    <h6 class="mb-0">{{ $employee->hire_date?->format('j M Y') }}</h6>
                                </div>
                            </div>
                            <hr class="border-dashed">
                            <div class="d-grid gap-2">
                                <span class="d-block text-dark">
                                    <i class="fa-regular fa-envelope me-2 text-primary"></i>
                                    {{ $employee->email }}
                                </span>
                                <span class="d-block text-dark">
                                    <i class="fa-solid fa-phone-volume me-2 text-primary"></i>
                                    {{ $employee->phone_number }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row mt-4"> {{-- Margin-top for spacing --}}
        <div class="col-lg-12">
            <nav aria-label="pagination" class="float-end" id="employee-pagination-container"> {{-- Add an ID here --}}
                {{ $employees->links() }}
            </nav>
        </div>
    </div>
@else
    <div class="alert alert-info" role="alert">
        No employees found matching your criteria.
    </div>
@endif
