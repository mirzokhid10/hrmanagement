@extends('admin.layouts.master')

@section('content')
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="clearfix">
            <h1 class="app-page-title">Edit Task</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.onboarding.index') }}">Tasks</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
                </ol>
            </nav>
        </div>
        <a class="btn btn-primary waves-effect waves-light" href="{{ route('admin.onboarding.index') }}">
            <i class="fi fi-rr-arrow-left me-2"></i> Back to Board
        </a>
    </div>

    <div class="card p-4">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card-header py-3 d-flex justify-content-between">
            <h5 class="card-title">Edit Task Details</h5>
            <span class="badge bg-primary">Assigned to: {{ $onboarding->employee->full_name }}</span>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.onboarding.update', $onboarding->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="title" class="form-label">Task Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                            id="title" value="{{ old('title', $onboarding->title) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                        <select class="form-select @error('priority') is-invalid @enderror" name="priority">
                            <option value="low" {{ old('priority', $onboarding->priority) == 'low' ? 'selected' : '' }}>
                                Low</option>
                            <option value="medium"
                                {{ old('priority', $onboarding->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ old('priority', $onboarding->priority) == 'high' ? 'selected' : '' }}>
                                High</option>
                            <option value="urgent"
                                {{ old('priority', $onboarding->priority) == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date"
                            class="form-control @error('start_date') is-invalid @enderror"
                            value="{{ old('start_date', $onboarding->start_date?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="due_date" class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                            value="{{ old('due_date', $onboarding->due_date?->format('Y-m-d')) }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="3">{{ old('description', $onboarding->content) }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select @error('status') is-invalid @enderror" name="status">
                        <option value="new" {{ old('status', $onboarding->status) == 'new' ? 'selected' : '' }}>New
                        </option>
                        <option value="in_progress"
                            {{ old('status', $onboarding->status) == 'in_progress' ? 'selected' : '' }}>In Progress
                        </option>
                        <option value="pending" {{ old('status', $onboarding->status) == 'pending' ? 'selected' : '' }}>
                            Pending</option>
                        <option value="completed"
                            {{ old('status', $onboarding->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    {{-- Delete Button --}}
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                        <i class="fi fi-rr-trash me-1"></i> Delete Task
                    </button>

                    <button type="submit" class="btn btn-primary">Update Task</button>
                </div>
            </form>

            {{-- Hidden Delete Form --}}
            <form id="delete-form" action="{{ route('admin.onboarding.destroy', $onboarding->id) }}" method="POST"
                style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function confirmDelete() {
            if (confirm('Are you sure you want to delete this task? This cannot be undone.')) {
                document.getElementById('delete-form').submit();
            }
        }
    </script>
@endpush
