<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\HHruService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class HHIntegrationController extends Controller
{
    public function __construct(protected HHruService $hhService) {}

    /**
     * 1. Redirect to HH.ru
     * Accepts optional ?company_id=X for Admins
     */
    public function connect(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $targetCompanyId = null;

        if ($user->isAdmin()) {
            // Admin MUST provide a company_id via URL
            $request->validate(['company_id' => 'required|exists:companies,id']);
            $targetCompanyId = $request->company_id;
        } else {
            // HR Manager uses their own company
            $targetCompanyId = $user->company_id;
        }

        // Store the target company ID in the session so we remember it after the redirect
        Session::put('hh_connecting_company_id', $targetCompanyId);

        return redirect($this->hhService->getAuthUrl());
    }

    /**
     * 2. Handle return from HH.ru
     */
    public function callback(Request $request)
    {
        if (!$request->has('code')) {
            return redirect()->route('admin.recruitment.index')
                ->with('error', 'HH.ru authorization failed.');
        }

        // Retrieve the company ID we saved in step 1
        $companyId = Session::get('hh_connecting_company_id');
        $company = Company::find($companyId);

        if (!$company) {
            return redirect()->route('admin.recruitment.index')
                ->with('error', 'Could not determine which company to connect.');
        }

        // Perform the token exchange
        if ($this->hhService->authenticateCompany($company, $request->code)) {
            // Clear session
            Session::forget('hh_connecting_company_id');

            return redirect()->route('admin.recruitment.index')
                ->with('success', "HH.ru connected successfully for {$company->name}!");
        }

        return redirect()->route('admin.recruitment.index')
            ->with('error', 'Failed to exchange token with HH.ru.');
    }
}
