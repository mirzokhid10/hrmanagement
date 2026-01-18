@extends('admin.layouts.master')

@section('content')
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="clearfix">
            <h1 class="app-page-title">Company Policies</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Policies</li>
                </ol>
            </nav>
        </div>
        <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
            data-bs-target="#uploadPolicyModal">
            <i class="fa-solid fa-plus"></i> Add Policy
        </button>
    </div>

    <div class="row">
        @forelse($documents as $doc)
            <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                <div class="card h-100 hover-elevate">
                    <div class="card-body text-center">
                        <div
                            class="avatar avatar-xl bg-light rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                            @if (Str::contains($doc->mime_type, 'pdf'))
                                <i class="fa-regular fa-file-pdf text-danger fs-1"></i>
                            @else
                                <i class="fa-regular fa-file-pdf text-primary fs-1"></i>
                            @endif
                        </div>
                        <h5 class="card-title text-truncate" title="{{ $doc->name }}">{{ $doc->name }}</h5>
                        <p class="text-muted small mb-3">
                            Updated: {{ $doc->created_at->format('M d, Y') }} <br>
                            Size: {{ $doc->size_kb }} KB
                        </p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="{{ route('admin.document.download', $doc->id) }}"
                                class="btn btn-sm btn-outline-primary">
                                Download
                            </a>
                            @if (auth()->user()->isAdmin())
                                <form action="{{ route('admin.document.destroy', $doc->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Delete this policy?')">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info d-flex align-items-center" role="alert">
                    <i class="fi fi-rr-info me-2 fs-4"></i>
                    <div>
                        No company policies uploaded yet. Click "Add Policy" to upload your Employee Handbook or Safety
                        Guidelines.
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Upload Policy Modal --}}
    <div class="modal fade" id="uploadPolicyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Company Policy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.document.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    {{-- No employee_id means it is a policy --}}
                    <input type="hidden" name="type" value="policy">

                    <div class="modal-body">
                        @if (auth()->user()->isAdmin())
                            <div class="mb-3">
                                <label class="form-label">Company <span class="text-danger">*</span></label>
                                <select name="company_id" class="form-select" required>
                                    <option value="" selected disabled>Select Company</option>
                                    {{-- Assuming you share $companies to views via ViewComposer or manually --}}
                                    @foreach (\App\Models\Company::all() as $company)
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Select which company this policy applies to.</small>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">Policy Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                placeholder="e.g. Employee Handbook 2025" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">File <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control" required accept=".pdf,.doc,.docx">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload Policy</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
