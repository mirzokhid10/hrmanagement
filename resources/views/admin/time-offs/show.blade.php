@extends('admin.layouts.master') {{-- Assuming you have an admin layout --}}

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Time-Off Request Details</h1>
            <a href="{{ route('admin.time-offs.index') }}"
                class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Requests
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Request from {{ $timeOff->employee->user->name ?? 'N/A' }}
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Employee:</strong> {{ $timeOff->employee->user->name ?? 'N/A' }}</p>
                        <p><strong>Department:</strong> {{ $timeOff->employee->department->name ?? 'N/A' }}</p>
                        <p><strong>Position:</strong> {{ $timeOff->employee->position->name ?? 'N/A' }}</p>
                        <p><strong>Type of Leave:</strong> {{ $timeOff->type->name ?? 'N/A' }}
                            ({{ $timeOff->type->is_paid ? 'Paid' : 'Unpaid' }})</p>
                        <p><strong>Dates:</strong> {{ $timeOff->start_date->format('M d, Y') }} -
                            {{ $timeOff->end_date->format('M d, Y') }}</p>
                        <p><strong>Total Days:</strong> {{ $timeOff->total_days }}</p>
                        <p><strong>Reason:</strong> {{ $timeOff->reason }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Current Status:</strong>
                            <span
                                class="badge {{ $timeOff->status === 'Pending'
                                    ? 'badge-warning'
                                    : ($timeOff->status === 'Approved'
                                        ? 'badge-success'
                                        : ($timeOff->status === 'Rejected'
                                            ? 'badge-danger'
                                            : 'badge-secondary')) }}">
                                {{ $timeOff->status }}
                            </span>
                        </p>
                        @if ($timeOff->approver)
                            <p><strong>Approved/Rejected By:</strong> {{ $timeOff->approver->name }}</p>
                            <p><strong>On:</strong> {{ $timeOff->approved_at->format('M d, Y H:i') }}</p>
                        @endif
                        @if ($timeOff->rejection_reason)
                            <p><strong>Rejection Reason:</strong> {{ $timeOff->rejection_reason }}</p>
                        @endif
                        {{-- Display employee's current time-off balance for this type --}}
                        @php
                            $balance = $timeOff->employee->timeOffBalances
                                ->where('time_off_type_id', $timeOff->time_off_type_id)
                                ->where('year', $timeOff->start_date->year)
                                ->first();
                        @endphp
                        @if ($balance)
                            <p><strong>Employee's {{ $timeOff->type->name }} Balance:</strong>
                                {{ $balance->days_remaining }} of {{ $balance->allocated_days }} days remaining (Current
                                Year)</p>
                        @else
                            <p><strong>Employee's {{ $timeOff->type->name }} Balance:</strong> Not yet allocated for this
                                year.</p>
                        @endif
                    </div>
                </div>

                @if ($timeOff->isPending())
                    <hr>
                    <h6 class="font-weight-bold text-primary mb-3">Actions:</h6>
                    <form action="{{ route('admin.time_offs.approve', $timeOff) }}" method="POST" class="d-inline mr-2">
                        @csrf
                        <button type="submit" class="btn btn-success">Approve Request</button>
                    </form>

                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#rejectModal">Reject
                        Request</button>

                    {{-- Rejection Modal --}}
                    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog"
                        aria-labelledby="rejectModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="rejectModalLabel">Reject Time-Off Request</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form action="{{ route('admin.time_offs.reject', $timeOff) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="rejection_reason">Reason for rejection:</label>
                                            <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="3" required></textarea>
                                            @error('rejection_reason')
                                                <div class="text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-danger">Reject Request</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
