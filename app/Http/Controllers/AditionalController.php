<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AditionalController extends Controller
{
    public function index()
    {
        return view('pages.admin.aditionals.index');
    }

    public function create()
    {
        return view('pages.admin.aditionals.create');
    }

    public function edit($id)
    {
        return view('pages.admin.aditionals.create', compact('id'));
    }
}
