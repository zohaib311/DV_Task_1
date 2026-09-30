<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashbaordController extends Controller
{
    //
    function dashboardView()
    {
        if (auth()->user()->can('offerings.view-assigned') && ! auth()->user()->can('offerings.manage')) {
            return redirect()->route('teaching.dashboard');
        }

        return view('dashboard.dashboard');
    }
}
