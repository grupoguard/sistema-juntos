<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AditionalController extends Controller
{
    public function index()
    {
        return view('pages.admin.aditionals.index');
    }
}
