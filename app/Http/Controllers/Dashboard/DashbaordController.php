<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashbaordController extends Controller
{
    //
    function dashboardView()
    {
        if ($route = app(\App\Services\StudentPortal\StudentWorkspace::class)->landing(auth()->user())) {
            return redirect()->route($route);
        }
        if (auth()->user()->can('offerings.view-assigned') && ! auth()->user()->can('offerings.manage')) {
            return redirect()->route('teaching.dashboard');
        }

        return view('dashboard.dashboard');
    }
}
