<?php

namespace App\Services;

use App\Models\Recruitment;
use App\Models\Candidate;
use App\Models\Company;
use App\Services\Interfaces\RecruitmentServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Scopes\TenantScope;

class RecruitmentService implements RecruitmentServiceInterface
{
    /**
     * Calculate stats for the dashboard cards.
     */
    public function getStats(?int $companyId): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Base queries
        $recruitmentQuery = Recruitment::query();
        $candidateQuery = Candidate::query();

        // 1. Apply Tenancy Logic manually if Admin wants specific company, or remove scope if Admin wants all
        if ($user->isAdmin()) {
            if ($companyId === null) {
                // Show ALL data from ALL companies
                $recruitmentQuery->withoutGlobalScope(TenantScope::class);
                $candidateQuery->withoutGlobalScope(TenantScope::class);
            } else {
                // Show data for specific company
                $recruitmentQuery->where('company_id', $companyId);
                $candidateQuery->where('company_id', $companyId);
            }
        } else {
            // HR Managers are automatically scoped by the Trait/GlobalScope.
            // No extra code needed here.
        }

        // 2. Execute Queries
        return [
            'total_openings'     => $recruitmentQuery->where('status', 'published')->count(),
            'total_applications' => $candidateQuery->count(),
            'shortlisted'        => (clone $candidateQuery)->where('status', 'shortlisted')->count(),
            'interviewed'        => (clone $candidateQuery)->where('status', 'interviewed')->count(),
            'rejected'           => (clone $candidateQuery)->where('status', 'rejected')->count(),
            'hired'              => (clone $candidateQuery)->where('status', 'hired')->count(),
        ];
    }

    public function getPaginatedRecruitments(?int $companyId, int $perPage = 10): LengthAwarePaginator
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = Recruitment::withCount(['candidates', 'candidates as new_candidates_count' => function ($q) {
            $q->where('created_at', '>=', now()->subDays(7));
        }]);

        if ($user->isAdmin()) {
            if ($companyId === null) {
                $query->withoutGlobalScope(TenantScope::class);
            } else {
                $query->where('company_id', $companyId);
            }
        }

        return $query->latest()->paginate($perPage);
    }

    public function getRecentCandidates(?int $companyId, int $limit = 50): Collection
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = Candidate::with('recruitment');

        if ($user->isAdmin()) {
            if ($companyId === null) {
                $query->withoutGlobalScope(TenantScope::class);
            } else {
                $query->where('company_id', $companyId);
            }
        }

        return $query->latest()->limit($limit)->get();
    }

    public function createRecruitment(array $data, ?int $companyId = null): Recruitment
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $targetCompanyId = null;

        // 1. Determine Company Context
        if ($user->isAdmin()) {
            $targetCompanyId = $companyId ?? ($data['company_id'] ?? null);
            if (!$targetCompanyId) {
                throw new \InvalidArgumentException("Admin must specify a company ID.");
            }
        } else {
            $targetCompanyId = $user->company_id;
        }

        // 2. Process Key Skills (String -> Array)
        // The controller passes raw data, so we handle the logic here or in controller.
        // Ideally, data passed to service should be clean, but we can handle the transform here.
        if (isset($data['key_skills_input']) && is_string($data['key_skills_input'])) {
            $data['key_skills'] = array_map('trim', explode(',', $data['key_skills_input']));
            unset($data['key_skills_input']);
        }

        // 3. Create
        return Recruitment::create(array_merge($data, [
            'company_id' => $targetCompanyId,
            'status' => 'published' // Default
        ]));
    }

    public function deleteRecruitment(Recruitment $recruitment): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Security check
        if (!$user->isAdmin() && $recruitment->company_id !== $user->company_id) {
            throw new \Exception("Unauthorized access.");
        }

        return $recruitment->delete();
    }
}
