<?php

namespace App\Services\Telegram;

use App\Models\Employee;
use App\Models\User;
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
        // Try cache first (5 min cache to reduce DB hits)
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
        // Find employee by telegram_chat_id (across ALL companies for admin access)
        $employee = Employee::withoutGlobalScopes()
            ->where('telegram_chat_id', $chatId)
            ->with(['user', 'company'])
            ->first();

        // Case 1: Not registered at all
        if (!$employee) {
            return [
                'type' => 'public',
                'employee' => null,
                'user' => null,
                'company_id' => null,
                'can_manage_all' => false,
            ];
        }

        $user = $employee->user;

        // Case 2: Employee exists but no user account (regular employee only)
        if (!$user) {
            return [
                'type' => 'employee',
                'employee' => $employee,
                'user' => null,
                'company_id' => $employee->company_id,
                'can_manage_all' => false,
            ];
        }

        // Case 3: Super Admin (user has NO company_id)
        if (!$user->company_id) {
            return [
                'type' => 'admin',
                'employee' => $employee,
                'user' => $user,
                'company_id' => null, // Can manage ANY company
                'can_manage_all' => true,
            ];
        }

        // Case 4: HR Manager (has company_id + has 'hr' role)
        if ($user->hasRole('hr') || $user->hasRole('admin')) {
            return [
                'type' => 'hr',
                'employee' => $employee,
                'user' => $user,
                'company_id' => $user->company_id,
                'can_manage_all' => false,
            ];
        }

        // Case 5: Regular employee with user account
        return [
            'type' => 'employee',
            'employee' => $employee,
            'user' => $user,
            'company_id' => $employee->company_id,
            'can_manage_all' => false,
        ];
    }

    /**
     * Clear cached auth for a chat ID (call after role changes)
     */
    public function clearCache(int $chatId): void
    {
        Cache::forget("tg_auth_{$chatId}");
    }

    /**
     * Check if user can manage a specific company
     */
    public function canManageCompany(array $auth, int $companyId): bool
    {
        // Admin can manage all
        if ($auth['can_manage_all']) {
            return true;
        }

        // HR can only manage their own company
        if ($auth['type'] === 'hr') {
            return $auth['company_id'] === $companyId;
        }

        return false;
    }

    /**
     * Get active company for admin (from session or return null)
     */
    public function getActiveCompanyForAdmin(int $chatId): ?int
    {
        return Cache::get("admin_active_company_{$chatId}");
    }

    /**
     * Set active company for admin
     */
    public function setActiveCompanyForAdmin(int $chatId, int $companyId): void
    {
        Cache::put("admin_active_company_{$chatId}", $companyId, now()->addHours(24));
    }

    /**
     * Get the working company ID for current context
     * For admin: returns active selected company or null
     * For HR: returns their company_id
     * For employee: returns their company_id
     */
    public function getWorkingCompanyId(array $auth, int $chatId): ?int
    {
        if ($auth['type'] === 'admin') {
            return $this->getActiveCompanyForAdmin($chatId);
        }

        return $auth['company_id'];
    }
}
