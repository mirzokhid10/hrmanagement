@extends('admin.layouts.master')

@section('content')
    {{-- Page Header --}}
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="clearfix">
            <h1 class="app-page-title">
                Documents for <span class="text-primary">{{ $employee->full_name }}</span>
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.employee.index') }}">Employees</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Documents</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.employee.index') }}" class="btn btn-outline-light waves-effect">
                <i class="fi fi-rr-arrow-left me-1"></i> Back
            </a>
            {{-- Trigger Modal for Upload --}}
            <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
                data-bs-target="#uploadDocumentModal">
                <i class="fi fi-rr-upload me-1"></i> Upload Document
            </button>
        </div>
    </div>

    {{-- Stats / Info Card --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary-subtle border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar avatar-lg rounded-circle me-3 bg-white text-primary">
                        <i class="fi fi-rr-folder-open fs-3"></i>
                    </div>
                    <div>
                        <h5 class="mb-1 text-primary fw-bold">Employee File</h5>
                        <p class="mb-0 text-dark opacity-75">
                            Manage contracts, ID cards, and other confidential files for
                            <strong>{{ $employee->full_name }}</strong>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Documents List --}}
    <div class="card">
        <div class="card-header border-0 pt-3 pb-0">
            <h5 class="card-title">Uploaded Files ({{ $documents->count() }})</h5>
        </div>
        <div class="card-body">
            @if ($documents->count() > 0)
                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Document Name</th>
                                <th scope="col">Type</th>
                                <th scope="col">Size</th>
                                <th scope="col">Uploaded At</th>
                                <th scope="col">Expiry Date</th>
                                <th scope="col" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($documents as $doc)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3 bg-light rounded text-center pt-1">
                                                @if (Str::contains($doc->mime_type, 'pdf'))
                                                    <i class="fa-regular fa-file-pdf text-danger fs-5"></i>
                                                @elseif(Str::contains($doc->mime_type, 'image'))
                                                    <i class="fa-regular fa-file-image text-info fs-5"></i>
                                                @elseif(Str::contains($doc->mime_type, 'word'))
                                                    <i class="fa-regular fa-file-word text-primary fs-5"></i>
                                                @else
                                                    <i class="fa-regular fa-file text-secondary fs-5"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <h6 class="mb-0 text-dark">{{ $doc->name }}</h6>
                                                <small
                                                    class="text-muted">{{ Str::upper($doc->extension ?? 'FILE') }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                            {{ ucfirst(str_replace('_', ' ', $doc->type)) }}
                                        </span>
                                    </td>
                                    <td>{{ $doc->size_kb }} KB</td>
                                    <td>{{ $doc->created_at->format('d M, Y') }}</td>
                                    <td>
                                        @if ($doc->expiry_date)
                                            <span class="{{ $doc->is_expired ? 'text-danger fw-bold' : 'text-success' }}">
                                                {{ $doc->expiry_date->format('d M, Y') }}
                                                @if ($doc->is_expired)
                                                    <i class="fi fi-rr-exclamation ms-1"></i>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.document.download', $doc->id) }}"
                                                class="btn btn-sm btn-icon btn-subtle-primary" title="Download">
                                                <i class="fa-solid fa-download"></i>
                                            </a>
                                            <form action="{{ route('admin.document.destroy', $doc->id) }}" method="POST"
                                                class="d-inline-block ms-1">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-icon btn-subtle-danger"
                                                    onclick="confirmDelete(this)" title="Delete">
                                                    <i class="fa-regular fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fi fi-rr-folder text-muted fs-1 opacity-50"></i>
                    </div>
                    <h5 class="text-muted">No documents found</h5>
                    <p class="text-muted small">Upload contracts, ID proofs, or resumes for this employee.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Upload Modal --}}
    <div class="modal fade" id="uploadDocumentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.document.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}">

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Document Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                placeholder="e.g. Employment Contract 2024" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Document Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="" selected disabled>Select Type</option>
                                <option value="contract">Contract / Offer Letter</option>
                                <option value="id_card">ID Card / Passport</option>
                                <option value="resume">Resume / CV</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">File <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control" required
                                accept=".pdf,.doc,.docx,.jpg,.png">
                            <small class="text-muted">Max size: 10MB. Allowed: PDF, DOC, JPG, PNG.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Expiry Date (Optional)</label>
                            <input type="date" name="expiry_date" class="form-control">
                            <small class="text-muted">Useful for Passports, Visas, or Contracts.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function confirmDelete(btn) {
            if (confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
                btn.closest('form').submit();
            }
        }
    </script>
@endpush
