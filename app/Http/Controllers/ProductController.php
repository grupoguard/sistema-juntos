<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return view('pages.admin.products.index');
    }

    public function create()
    {
        return view('pages.admin.products.create');
    }

    public function edit($id)
    {
        return view('pages.admin.products.create', compact('id'));
    }
}
