@extends('admin.layouts.master')

@section('content')
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="clearfix">
            <h1 class="app-page-title">Office Locations Management</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Office Locations</li>
                </ol>
            </nav>
        </div>
        <a class="btn btn-primary waves-effect waves-light" href="{{ route('admin.office-location.create') }}">
            <i class="fa-solid fa-plus me-1"></i> Add Office Location
        </a>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card overflow-hidden">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="card-title mb-0">Office Locations</h6>
                </div>
                <div class="card-body p-0 pb-2">
                    <div class="table-responsive"> {{-- Added for better responsiveness --}}
                        <table id="dt_basic" class="table display">
                            <thead class="table-light">
                                <tr>
                                    @if (auth()->user()->isAdmin())
                                        {{-- Only show Company Name for Super Admins --}}
                                        <th class="minw-200px">Company Name</th>
                                    @endif
                                    <th class="minw-200px">Office Name</th>
                                    <th class="minw-50px">Latitude</th>
                                    <th class="minw-50px">Longitude</th>
                                    <th class="minw-120px">Radius (m)</th>
                                    <th class="minw-200px">Address</th>
                                    <th class="minw-100px">Status</th>
                                    <th class="minw-100px">Is Primary</th>
                                    <th class="text-end minw-100px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($locations as $location)
                                    <tr>
                                        @if (auth()->user()->isAdmin())
                                            <td>{{ $location->company->name ?? 'N/A' }}</td>
                                        @endif
                                        <td>{{ $location->name }}</td>
                                        <td>{{ number_format($location->latitude, 6) }}</td> {{-- Format for readability --}}
                                        <td>{{ number_format($location->longitude, 6) }}</td> {{-- Format for readability --}}
                                        <td>{{ $location->radius_meters }}</td>
                                        <td>{{ $location->address ?? 'N/A' }}</td>
                                        <td>
                                            @if ($location->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($location->is_primary)
                                                <span class="badge bg-primary">Yes</span>
                                            @else
                                                <span class="badge bg-secondary">No</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group float-end">


                                                <a href="{{ route('admin.office-location.edit', $location->id) }}"
                                                    class="btn btn-icon btn-subtle-telegram waves-effect waves-light me-1">
                                                    <i class="fa-regular fa-pen-to-square"></i>
                                                </a>

                                                <form action="{{ route('admin.office-location.destroy', $location->id) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm(\'Are you sure?\')">
                                                    <button type="submit"
                                                        class="btn btn-icon btn-subtle-youtube waves-effect waves-light">
                                                        <i class="fa-regular fa-trash-can"></i>
                                                    </button>
                                                </form>


                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ auth()->user()->isAdmin() ? '9' : '8' }}" class="text-center py-4">
                                            No office locations found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div> {{-- End table-responsive --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- If you're using DataTables, initialize it here --}}
@endpush
