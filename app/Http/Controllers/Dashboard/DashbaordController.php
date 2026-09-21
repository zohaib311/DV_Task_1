<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashbaordController extends Controller
{
    //
    function dashboardView()
    {
        return view('dashboard.dashboard');
    }
}
