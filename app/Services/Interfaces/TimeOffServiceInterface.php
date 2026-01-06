<?php

namespace App\Services\Interfaces;

use App\Models\TimeOff;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TimeOffServiceInterface
{
    public function getPaginatedTimeOffs(?int $companyId, int $perPage = 10, array $filters = []): LengthAwarePaginator;
    public function createTimeOff(array $data, int $companyId, int $requestingUserId): TimeOff;
    public function updateTimeOff(TimeOff $timeOff, array $data, int $updatingUserId): TimeOff;
    public function deleteTimeOff(TimeOff $timeOff): bool;
    public function getTimeOffTypes(int $companyId): Collection;
    public function getCompanyEmployees(int $companyId): Collection;
    public function approveTimeOff(TimeOff $timeOff, int $approverId): TimeOff;
    public function rejectTimeOff(TimeOff $timeOff, int $approverId, string $rejectionReason): TimeOff;
}
