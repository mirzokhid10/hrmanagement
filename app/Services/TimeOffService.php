<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Employee;
use App\Models\TimeOff;
use App\Models\TimeOffBalance;
use App\Models\TimeOffType;
use App\Services\Interfaces\TimeOffServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TimeOffService implements TimeOffServiceInterface
{
    public function getPaginatedTimeOffs(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = TimeOff::with(['employee.user', 'employee.department', 'type', 'approver.user']);

        if (isset($filters['status']) && $filters['status'] !== 'All') {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['employee_id']) && $filters['employee_id']) {
            $query->where('employee_id', $filters['employee_id']);
        }
        // Add more filters as needed (e.g., date range)

        return $query->latest('start_date')->paginate($perPage);
    }

    public function createTimeOff(array $data, int $companyId, int $requestingUserId): TimeOff
    {
        return DB::transaction(function () use ($data, $companyId, $requestingUserId) {
            $timeOff = TimeOff::create(array_merge($data, [
                'company_id' => $companyId,
                'status' => 'Pending', // New requests are always pending
                // approver_id and approved_at are null initially
            ]));

            // TODO: Send notification to manager/approver
            // event(new TimeOffRequested($timeOff));

            return $timeOff;
        });
    }

    public function updateTimeOff(TimeOff $timeOff, array $data, int $updatingUserId): TimeOff
    {
        return DB::transaction(function () use ($timeOff, $data, $updatingUserId) {
            // If status is being changed to Rejected, ensure rejection_reason is provided
            if (isset($data['status']) && $data['status'] === 'Rejected' && empty($data['rejection_reason'])) {
                throw new \InvalidArgumentException("Rejection reason is required when status is 'Rejected'.");
            }

            $originalStatus = $timeOff->status;
            $newStatus = $data['status'] ?? $originalStatus;

            $timeOff->fill($data);

            // Handle balance updates based on status changes
            if ($originalStatus !== 'Approved' && $newStatus === 'Approved') {
                // Request is now approved
                $timeOff->approver_id = $updatingUserId;
                $timeOff->approved_at = now();
                $this->updateTimeOffBalance($timeOff, true); // Deduct days
            } elseif ($originalStatus === 'Approved' && $newStatus !== 'Approved') {
                // An approved request is being changed (e.g., to Cancelled or Rejected)
                $this->updateTimeOffBalance($timeOff, false); // Add days back
                $timeOff->approver_id = $updatingUserId;
                $timeOff->approved_at = now(); // Timestamp when changed
            } elseif ($newStatus === 'Rejected') {
                // If status is changed to Rejected (and wasn't previously approved)
                $timeOff->approver_id = $updatingUserId;
                $timeOff->approved_at = now(); // Timestamp when rejected
            }

            $timeOff->save();

            // TODO: Send notification if status changed

            return $timeOff;
        });
    }

    public function deleteTimeOff(TimeOff $timeOff): bool
    {
        return DB::transaction(function () use ($timeOff) {
            // If an approved time-off is deleted, revert the balance
            if ($timeOff->isApproved()) {
                $this->updateTimeOffBalance($timeOff, false); // Add days back
            }
            return $timeOff->delete();
        });
    }

    public function getTimeOffTypes(int $companyId): Collection
    {
        $types = TimeOffType::where('company_id', $companyId)->get();

        return $types;
    }

    public function getCompanyEmployees(int $companyId): Collection
    {

        return Employee::where('company_id', $companyId)->with('user', 'department')->get();
    }

    public function approveTimeOff(TimeOff $timeOff, int $approverId): TimeOff
    {
        if (!$timeOff->isPending()) {
            throw new \InvalidArgumentException('Only pending time-off requests can be approved.');
        }

        return DB::transaction(function () use ($timeOff, $approverId) {
            $timeOff->status = 'Approved';
            $timeOff->approver_id = $approverId;
            $timeOff->approved_at = now();
            $timeOff->save();

            $this->updateTimeOffBalance($timeOff, true); // Deduct days

            // TODO: Send notification
            // event(new TimeOffApproved($timeOff));

            return $timeOff;
        });
    }

    public function rejectTimeOff(TimeOff $timeOff, int $approverId, string $rejectionReason): TimeOff
    {
        if (!$timeOff->isPending()) {
            throw new \InvalidArgumentException('Only pending time-off requests can be rejected.');
        }

        return DB::transaction(function () use ($timeOff, $approverId, $rejectionReason) {
            $timeOff->status = 'Rejected';
            $timeOff->approver_id = $approverId;
            $timeOff->approved_at = now(); // Timestamp when rejected
            $timeOff->rejection_reason = $rejectionReason;
            $timeOff->save();

            // TODO: Send notification
            // event(new TimeOffRejected($timeOff));

            return $timeOff;
        });
    }

    /**
     * Helper to update time off balances.
     * @param TimeOff $timeOff The time off request.
     * @param bool $deduct If true, deducts days; if false, adds days back.
     */
    protected function updateTimeOffBalance(TimeOff $timeOff, bool $deduct): void
    {
        $balance = TimeOffBalance::firstOrNew([
            'company_id' => $timeOff->company_id,
            'employee_id' => $timeOff->employee_id,
            'time_off_type_id' => $timeOff->time_off_type_id,
            'year' => $timeOff->start_date->year,
        ]);

        if (!$balance->exists) {
            $balance->allocated_days = $timeOff->type->default_days_per_year ?? 0;
            $balance->days_taken = 0; // Initialize if new
        }

        if ($deduct) {
            // Ensure days_taken doesn't exceed allocated_days if strict
            $balance->days_taken += $timeOff->total_days;
        } else {
            // Ensure days_taken doesn't go below zero
            $balance->days_taken = max(0, $balance->days_taken - $timeOff->total_days);
        }
        $balance->save();
    }
}
