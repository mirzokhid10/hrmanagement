<?php

namespace App\Services\Interfaces;

use App\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

interface DocumentServiceInterface
{
    /**
     * Upload a document for a specific employee.
     */
    public function uploadEmployeeDocument(int $employeeId, array $data, UploadedFile $file): Document;

    /**
     * Upload a generic company policy document.
     */
    public function uploadCompanyPolicy(array $data, UploadedFile $file): Document;

    /**
     * Get all documents for a specific employee.
     */
    public function getEmployeeDocuments(int $employeeId): Collection;

    /**
     * Get all company policy documents.
     */
    public function getCompanyPolicies(): Collection;

    /**
     * Delete a document.
     */
    public function deleteDocument(Document $document): bool;
}
