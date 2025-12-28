<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function index()
    {
        return view('pages.admin.partners.index');
    }

    public function create()
    {
        return view('pages.admin.partners.create');
    }

    public function edit($id)
    {
        return view('pages.admin.partners.create', compact('id'));
    }
}
