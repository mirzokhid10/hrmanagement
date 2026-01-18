<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Employee;
use App\Services\Interfaces\DocumentServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class DocumentService implements DocumentServiceInterface
{
    public function uploadEmployeeDocument(int $employeeId, array $data, UploadedFile $file): Document
    {
        // Security: Ensure employee belongs to current tenant (handled by TenantScope lookup)
        $employee = Employee::withoutGlobalScope(\App\Scopes\TenantScope::class)->findOrFail($employeeId);

        // Security Check (Optional but good):
        // If user is NOT admin, ensure they belong to the same company
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->isAdmin() && $user->company_id !== $employee->company_id) {
            notify()->error('Unauthorized access to this employee');
        }

        return $this->handleUpload($data, $file, $employee->company_id, $employeeId);
    }

    public function uploadCompanyPolicy(array $data, UploadedFile $file): Document
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Try to get ID from the logged-in user
        $companyId = $user->company_id;

        // 2. If user is Admin (null ID), try to get it from the form data
        if (!$companyId && isset($data['company_id'])) {
            $companyId = (int) $data['company_id'];
        }

        // 3. If still null, check if a global tenant is set
        if (!$companyId && function_exists('tenant') && tenant()) {
            $companyId = tenant()->id;
        }

        // 4. Safety Check
        if (!$companyId) {
            throw new \InvalidArgumentException("A Company must be selected to upload a policy.");
        }

        return $this->handleUpload($data, $file, $companyId, null);
    }

    protected function handleUpload(array $data, UploadedFile $file, int $companyId, ?int $employeeId): Document
    {
        return DB::transaction(function () use ($data, $file, $companyId, $employeeId) {
            // Determine folder path
            $folder = $employeeId
                ? "documents/employees/{$employeeId}"
                : "documents/policies";

            $path = $file->store($folder, 'public');

            return Document::create([
                'company_id' => $companyId,
                'employee_id' => $employeeId,
                'name' => $data['name'],
                'type' => $data['type'],
                'file_path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size_kb' => round($file->getSize() / 1024),
                'expiry_date' => $data['expiry_date'] ?? null,
            ]);
        });
    }

    public function getEmployeeDocuments(int $employeeId): Collection
    {
        // Use withoutGlobalScope to find documents even if they belong to a different company
        // (This is necessary for Super Admins viewing employees of specific companies)
        return Document::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('employee_id', $employeeId)
            ->get();
    }

    public function getCompanyPolicies(): Collection
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = Document::query()->whereNull('employee_id');

        // If Admin, show ALL policies (or you can filter by request input if you prefer)
        // If Regular User, the TenantScope will automatically apply via the Model,
        // but if we want to be explicit or if Admin needs bypass:
        if ($user->isAdmin()) {
            $query->withoutGlobalScope(\App\Scopes\TenantScope::class);
        }

        return $query->get();
    }

    public function deleteDocument(Document $document): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Security check
        if (!$user->isAdmin() && $document->company_id !== $user->company_id) {
            throw new \Exception("Unauthorized");
        }

        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        return $document->delete();
    }
}
