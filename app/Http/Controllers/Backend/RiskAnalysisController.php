<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployeeInsight;
use App\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;

class RiskAnalysisController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = EmployeeInsight::query();

        if ($user->isAdmin()) {
            $query->withoutGlobalScope(TenantScope::class);
            $query->with(['employee' => function ($q) {
                $q->withoutGlobalScope(TenantScope::class)->with('company');
            }]);
        } else {
            $query->with(['employee.company']);
        }

        $risks = $query->orderByDesc('score')->paginate(10);

        return view('admin.risks.index', compact('risks'));
    }
}
