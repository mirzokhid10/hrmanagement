<?php

namespace App\Services\Interfaces;

use App\Models\Recruitment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface RecruitmentServiceInterface
{
    /**
     * Get dashboard statistics (Openings, Hired, Rejected, etc.)
     */
    public function getStats(?int $companyId): array;

    /**
     * Get paginated jobs.
     */
    public function getPaginatedRecruitments(?int $companyId, int $perPage = 10): LengthAwarePaginator;

    /**
     * Get recent candidates (applications) for the table.
     */
    public function getRecentCandidates(?int $companyId, int $limit = 50): Collection;

    /**
     * Create a new job vacancy.
     */
    public function createRecruitment(array $data, ?int $companyId = null): Recruitment;

    /**
     * Delete a recruitment.
     */
    public function deleteRecruitment(Recruitment $recruitment): bool;
}
