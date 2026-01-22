<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;

class OrgChartController extends Controller
{
    public function index()
    {
        return view('admin.org-chart.index');
    }

    public function data(): JsonResponse
    {
        // 1. Get employees for current tenant (handled by TenantScoped)
        // 2. Find roots (people with no manager OR manager is not in this company/deleted)
        $employees = Employee::with(['department'])->get();

        // Build tree in memory to avoid N+1 recursion queries
        $tree = $this->buildTree($employees);

        return response()->json($tree);
    }

    private function buildTree($employees, $parentId = null)
    {
        $branch = [];

        foreach ($employees as $employee) {
            if ($employee->reports_to == $parentId) {
                $node = [
                    'id' => $employee->id,
                    'name' => $employee->full_name,
                    'title' => $employee->job_title,
                    'image' => $employee->profile_image_url,
                    'department' => $employee->department?->name,
                    'children' => $this->buildTree($employees, $employee->id)
                ];
                $branch[] = $node;
            }
        }

        // Edge case: If no one has reports_to = null (circular or bad data),
        // logic might need adjustment, but this is standard.
        return $branch;
    }
}
