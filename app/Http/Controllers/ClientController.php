<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        return view('pages.admin.clients.index');
    }

    public function create()
    {
        return view('pages.admin.clients.create');
    }

    public function edit($id)
    {
        return view('pages.admin.clients.create', compact('id'));
    }
}
