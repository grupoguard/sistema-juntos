<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

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

        $financialDivergencesCount = Order::query()
            ->leftJoin(DB::raw('
                (
                    SELECT order_id, MAX(product_value) as product_value
                    FROM order_prices
                    GROUP BY order_id
                ) op
            '), 'op.order_id', '=', 'orders.id')
            ->leftJoin(DB::raw('
                (
                    SELECT order_id, SUM(value) as total_dependents_value
                    FROM order_aditionals_dependents
                    GROUP BY order_id
                ) oad
            '), 'oad.order_id', '=', 'orders.id')
            ->leftJoin(DB::raw('
                (
                    SELECT order_id, SUM(value) as total_financial_value
                    FROM financial
                    GROUP BY order_id
                ) fin
            '), 'fin.order_id', '=', 'orders.id')
            ->whereRaw('ROUND((COALESCE(op.product_value, 0) + COALESCE(oad.total_dependents_value, 0)), 2) != ROUND(COALESCE(fin.total_financial_value, 0), 2)')
            ->count();

        return view('dashboard', [
            'pendingOrdersCount' => $pendingOrdersCount ?? 0,
            'financialDivergencesCount' => $financialDivergencesCount,
        ]);
    }
}
