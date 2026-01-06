<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminDashboardController extends Controller
{
    public function dashboard()
    {
        try {
            if (!Auth::check()) {

                return redirect()->route('login');
            }

            /** @var User $user */
            $user = Auth::user();

            $user->load(['company', 'employee']); // Eager load

            $employees = Employee::where('status', '!=', 'inactive')
                ->with('department')
                ->limit(8)
                ->get();


            return view('admin.dashboard', [
                'user' => $user,
                'employees' => $employees,

            ]);
        } catch (\Exception $e) {
            Log::error('Error loading admin dashboard: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', 'An error occurred while loading the dashboard.');
        }
    }
}
