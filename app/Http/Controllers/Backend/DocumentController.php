<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Models\Document;
use App\Models\Employee;
use App\Services\Interfaces\DocumentServiceInterface;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    protected DocumentServiceInterface $documentService;

    public function __construct(DocumentServiceInterface $documentService)
    {
        $this->documentService = $documentService;
    }


    public function index(Employee $employee)
    {
        // TenantScope automatically protects access to this employee
        $documents = $this->documentService->getEmployeeDocuments($employee->id);

        return view('admin.document.index', compact('employee', 'documents'));
    }

    // LIST COMPANY POLICIES
    public function policies()
    {
        $documents = $this->documentService->getCompanyPolicies();
        return view('admin.document.policies', compact('documents'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDocumentRequest $request)
    {
        try {
            if ($request->filled('employee_id')) {
                $this->documentService->uploadEmployeeDocument(
                    $request->employee_id,
                    $request->validated(),
                    $request->file('file')
                );
            } else {
                $this->documentService->uploadCompanyPolicy(
                    $request->validated(),
                    $request->file('file')
                );
            }

            notify()->success('Document uploaded successfully.');
            return redirect()->back();
        } catch (\Exception $e) {
            notify()->error('Upload failed: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function download(Document $document)
    {
        // TenantScope protects this access
        return response()->download(storage_path('app/public/' . $document->file_path), $document->name);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
        try {
            $this->documentService->deleteDocument($document);
            notify()->success('Document deleted.');
            return redirect()->back();
        } catch (\Exception $e) {
            notify()->error('Error deleting document.');
            return redirect()->back();
        }
    }
}
