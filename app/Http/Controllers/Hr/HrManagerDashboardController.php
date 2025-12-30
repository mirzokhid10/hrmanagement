<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;


class HrManagerDashboardController extends Controller
{
    public function index()
    {
        return view('hr.dashboard');
    }
}
