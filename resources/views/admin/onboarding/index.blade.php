@extends('admin.layouts.master') {{-- Or whatever your admin layout is named --}}

@section('content')
    <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="clearfix">
            <h1 class="app-page-title">Onboarding Tasks</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Task Board</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.onboarding.create') }}" class="btn btn-primary waves-effect waves-light">
            <i class="fa-solid fa-plus me-1"></i> Add Task
        </a>
    </div>

    {{-- Filters (Optional, kept from your HTML) --}}
    <div class="card d-flex flex-row flex-wrap align-items-center h-auto mb-5">
        <ul class="nav nav-underline me-auto px-3 gap-2">
            <li class="nav-item"><a class="nav-link border-3 py-3 px-2 active" href="#">Overview</a></li>
        </ul>
        <div class="d-flex ps-3">
            <form class="d-flex align-items-center h-100 w-150px w-lg-300px position-relative">
                <button type="button" class="btn btn-sm border-0 position-absolute start-0 ms-3 p-0">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                <input type="text"
                    class="form-control form-control-lg ps-5 rounded-start-0 border-0 shadow-none bg-transparent"
                    placeholder="Search Task">
            </form>
        </div>
    </div>

    <div class="row" id="taskWrapper">

        {{-- 1. NEW TASKS --}}
        @include('admin.onboarding.partials.column', [
            'title' => 'New Task',
            'id' => 'new',
            'color' => 'primary',
            'tasks' => $groupedTasks['new'],
        ])

        {{-- 2. IN PROGRESS --}}
        @include('admin.onboarding.partials.column', [
            'title' => 'In Progress',
            'id' => 'in_progress',
            'color' => 'info',
            'tasks' => $groupedTasks['in_progress'],
        ])

        {{-- 3. PENDING --}}
        @include('admin.onboarding.partials.column', [
            'title' => 'Pending',
            'id' => 'pending',
            'color' => 'secondary',
            'tasks' => $groupedTasks['pending'],
        ])

        {{-- 4. COMPLETE --}}
        @include('admin.onboarding.partials.column', [
            'title' => 'Complete',
            'id' => 'completed',
            'color' => 'success',
            'tasks' => $groupedTasks['completed'],
        ])

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Select all status links
            const statusButtons = document.querySelectorAll('.status-changer');

            statusButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault(); // Stop the link from jumping to top of page

                    // Get data from attributes
                    const taskId = this.getAttribute('data-id');
                    const newStatus = this.getAttribute('data-status');

                    // Visual feedback (optional)
                    const dropdownBtn = this.closest('.dropdown').querySelector('button');
                    const originalText = dropdownBtn.innerText;
                    dropdownBtn.innerText = 'Saving...';

                    // Send AJAX request
                    fetch(`/admin/onboarding/${taskId}/status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}' // Laravel CSRF Token
                            },
                            body: JSON.stringify({
                                status: newStatus
                            })
                        })
                        .then(response => {
                            if (response.ok) {
                                // Success! Reload page to see task in new column
                                window.location.reload();
                            } else {
                                // Error handling
                                alert('Failed to update status.');
                                dropdownBtn.innerText = originalText;
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Something went wrong.');
                            dropdownBtn.innerText = originalText;
                        });
                });
            });
        });
    </script>
@endpush
