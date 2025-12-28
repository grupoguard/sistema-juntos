<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PartnerCategoryController extends Controller
{
    public function index()
    {
        return view('pages.admin.partner_categories.index');
    }
}
