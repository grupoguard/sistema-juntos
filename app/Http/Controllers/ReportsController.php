<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function edp()
    {
        return view('pages.admin.reports.index');
    }

    public function financial()
    {
        return view('pages.admin.reports.financial');
    }

}
