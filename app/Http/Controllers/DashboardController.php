<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $pendingOrdersCount = 0;

        if (auth()->check() && auth()->user()->isAdmin()) {
            $pendingOrdersCount = Order::query()
                ->where('review_status', 'PENDENTE')
                ->whereNull('admin_viewed_at')
                ->count();
        }

        return view('dashboard', compact('pendingOrdersCount'));
    }
}
