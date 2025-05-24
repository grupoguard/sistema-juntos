<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        return view('pages.admin.orders.index');
    }

    public function create()
    {
        return view('pages.admin.orders.create');
    }

    public function edit($id)
    {
        return view('pages.admin.orders.edit', compact('id'));
    }
}
