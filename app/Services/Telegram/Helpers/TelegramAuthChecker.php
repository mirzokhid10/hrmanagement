<?php

namespace App\Services\Telegram\Helpers;

use App\Models\Employee;
use Illuminate\Support\Facades\Cache;

class TelegramAuthChecker
{
    /**
     * Get user type and permissions for a Telegram chat ID
     *
     * Returns array with:
     * - type: 'public' | 'employee' | 'hr' | 'admin'
     * - employee: Employee model or null
     * - user: User model or null
     * - company_id: int or null
     * - can_manage_all: bool (true only for admin)
     */
    public function getUserType(int $chatId): array
    {
        $cacheKey = "tg_auth_{$chatId}";

        return Cache::remember($cacheKey, 300, function () use ($chatId) {
            return $this->detectUserType($chatId);
        });
    }

    /**
     * Detect user type from database
     */
    protected function detectUserType(int $chatId): array
    {
        $employee = Employee::withoutGlobalScopes()
            ->where('telegram_chat_id', $chatId)
            ->with(['user', 'company'])
            ->first();

        if (!$employee) {
            return [
                'type' => 'public',
                'employee' => null,
                'user' => null,
                'company_id' => null,
                'can_manage_all' => false,
            ];
        }

        // Update last interaction timestamp
        $employee->update(['last_interaction_at' => now()]);

        $user = $employee->user;

        if (!$user) {
            return [
                'type' => 'employee',
                'employee' => $employee,
                'user' => null,
                'company_id' => $employee->company_id,
                'can_manage_all' => false,
            ];
        }

        // Super Admin: user has NO company_id
        if (!$user->company_id) {
            return [
                'type' => 'admin',
                'employee' => $employee,
                'user' => $user,
                'company_id' => null,
                'can_manage_all' => true,
            ];
        }

        // HR Manager: has company_id + has 'hr' or 'admin' role
        if ($user->hasRole('hr') || $user->hasRole('admin')) {
            return [
                'type' => 'hr',
                'employee' => $employee,
                'user' => $user,
                'company_id' => $user->company_id,
                'can_manage_all' => false,
            ];
        }

        return [
            'type' => 'employee',
            'employee' => $employee,
            'user' => $user,
            'company_id' => $employee->company_id,
            'can_manage_all' => false,
        ];
    }

    public function clearCache(int $chatId): void
    {
        Cache::forget("tg_auth_{$chatId}");
    }

    public function canManageCompany(array $auth, int $companyId): bool
    {
        if ($auth['can_manage_all']) {
            return true;
        }

        if ($auth['type'] === 'hr') {
            return $auth['company_id'] === $companyId;
        }

        return false;
    }

    public function getActiveCompanyForAdmin(int $chatId): ?int
    {
        return Cache::get("admin_active_company_{$chatId}");
    }

    public function setActiveCompanyForAdmin(int $chatId, int $companyId): void
    {
        Cache::put("admin_active_company_{$chatId}", $companyId, now()->addHours(24));
    }

    public function getWorkingCompanyId(array $auth, int $chatId): ?int
    {
        if ($auth['type'] === 'admin') {
            return $this->getActiveCompanyForAdmin($chatId);
        }

        return $auth['company_id'];
    }
}
