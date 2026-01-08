<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Employee;
use App\Models\TimeOff;
use App\Models\TimeOffBalance;
use App\Models\TimeOffType;
use App\Scopes\TenantScope;
use App\Services\Interfaces\TimeOffServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TimeOffService implements TimeOffServiceInterface
{
    public function getPaginatedTimeOffs(?int $companyId, int $perPage = 8, array $filters = []): LengthAwarePaginator
    {
        $query = TimeOff::query();

        if ($companyId !== null) {
            $query->where('company_id', $companyId);
            $query->with(['employee.user', 'employee.department', 'type', 'approver.user']);
        } else {
            $query->withoutGlobalScope(\App\Scopes\TenantScope::class);

            $query->with([
                'employee' => function ($q) {
                    $q->withoutGlobalScope(\App\Scopes\TenantScope::class)->with('user', 'department');
                },
                'type' => function ($q) {
                    $q->withoutGlobalScope(\App\Scopes\TenantScope::class);
                },
                'approver.user'
            ]);
        }

        if (isset($filters['status']) && $filters['status'] !== 'All') {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['employee_id']) && $filters['employee_id']) {
            $query->where('employee_id', $filters['employee_id']);
        }

        return $query->latest('start_date')->paginate($perPage);
    }

    public function createTimeOff(array $data, int $companyId, int $requestingUserId): TimeOff
    {
        return DB::transaction(function () use ($data, $companyId, $requestingUserId) {
            // Validate that employee and time-off type belong to the same company
            $employee = Employee::withoutGlobalScope(TenantScope::class)
                ->findOrFail($data['employee_id']);

            $timeOffType = TimeOffType::withoutGlobalScope(TenantScope::class)
                ->findOrFail($data['time_off_type_id']);

            // Extra validation: ensure employee and type belong to the target company
            if ($employee->company_id !== $companyId) {
                throw new \InvalidArgumentException('The selected employee does not belong to the specified company.');
            }

            if ($timeOffType->company_id !== $companyId) {
                throw new \InvalidArgumentException('The selected time-off type does not belong to the specified company.');
            }

            $timeOff = TimeOff::create(array_merge($data, [
                'company_id' => $companyId,
                'status' => 'Pending',
            ]));

            return $timeOff;
        });
    }

    public function updateTimeOff(TimeOff $timeOff, array $data, int $updatingUserId): TimeOff
    {
        return DB::transaction(function () use ($timeOff, $data, $updatingUserId) {
            if (isset($data['status']) && $data['status'] === 'Rejected' && empty($data['rejection_reason'])) {
                throw new \InvalidArgumentException("Rejection reason is required when status is 'Rejected'.");
            }

            $originalStatus = $timeOff->status;
            $newStatus = $data['status'] ?? $originalStatus;

            $timeOff->fill($data);

            if ($originalStatus !== 'Approved' && $newStatus === 'Approved') {
                $timeOff->approver_id = $updatingUserId;
                $timeOff->approved_at = now();
                $this->updateTimeOffBalance($timeOff, true);
            } elseif ($originalStatus === 'Approved' && $newStatus !== 'Approved') {
                $this->updateTimeOffBalance($timeOff, false);
                $timeOff->approver_id = $updatingUserId;
                $timeOff->approved_at = now();
            } elseif ($newStatus === 'Rejected') {
                $timeOff->approver_id = $updatingUserId;
                $timeOff->approved_at = now();
            }

            $timeOff->save();
            return $timeOff;
        });
    }

    public function deleteTimeOff(TimeOff $timeOff): bool
    {
        return DB::transaction(function () use ($timeOff) {
            if ($timeOff->isApproved()) {
                $this->updateTimeOffBalance($timeOff, false);
            }
            return $timeOff->delete();
        });
    }

    public function getTimeOffTypes(?int $companyId): Collection
    {
        $query = TimeOffType::query();

        if ($companyId) {
            $query->where('company_id', $companyId);
        } else {
            $query->withoutGlobalScope(TenantScope::class);
        }

        return $query->get();
    }

    public function getCompanyEmployees(?int $companyId): Collection
    {
        $query = Employee::with(['user', 'department']);

        if ($companyId) {
            $query->where('company_id', $companyId);
        } else {
            $query->withoutGlobalScope(TenantScope::class);
        }

        return $query->get();
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

            $this->updateTimeOffBalance($timeOff, true);
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
            $timeOff->approved_at = now();
            $timeOff->rejection_reason = $rejectionReason;
            $timeOff->save();
            return $timeOff;
        });
    }

    protected function updateTimeOffBalance(TimeOff $timeOff, bool $deduct): void
    {
        // For super admins creating time-offs across companies,
        // we need to bypass tenant scope when creating/updating balances
        $balance = TimeOffBalance::withoutGlobalScope(TenantScope::class)
            ->firstOrNew([
                'company_id' => $timeOff->company_id,
                'employee_id' => $timeOff->employee_id,
                'time_off_type_id' => $timeOff->time_off_type_id,
                'year' => $timeOff->start_date->year,
            ]);

        if (!$balance->exists) {
            // Load the type without scope to get default days
            $type = TimeOffType::withoutGlobalScope(TenantScope::class)
                ->find($timeOff->time_off_type_id);

            $balance->allocated_days = $type->default_days_per_year ?? 0;
            $balance->days_taken = 0;
        }

        if ($deduct) {
            $balance->days_taken += $timeOff->total_days;
        } else {
            $balance->days_taken = max(0, $balance->days_taken - $timeOff->total_days);
        }

        $balance->save();
    }
}
