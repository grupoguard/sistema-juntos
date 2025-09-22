<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function index()
    {
        return view('pages.admin.sellers.index');
    }

    public function create()
    {
        return view('pages.admin.sellers.create');
    }

    public function edit($id)
    {
        return view('pages.admin.sellers.create', compact('id'));
    }
}
