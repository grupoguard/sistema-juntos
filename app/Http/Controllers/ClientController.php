<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        return view('pages.admin.clients.index');
    }

    public function create()
    {
        return view('admin.clients.create');
    }
}
