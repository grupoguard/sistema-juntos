<?php

namespace App\Livewire;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class OrderFinancialDivergencesList extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 20;

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getRowsProperty()
    {
        /**
         * Regra atual:
         * valor_calculado = order_prices.product_value + SUM(order_aditionals_dependents.value)
         * valor_cobrado = SUM(financial.value)
         */
        return Order::query()
            ->select([
                'orders.id',
                'orders.client_id',
                'orders.seller_id',
                'orders.group_id',
                'orders.created_at',
                'clients.name as client_name',
                'clients.cpf as client_cpf',

                DB::raw('COALESCE(op.product_value, 0) as product_value'),
                DB::raw('COALESCE(oad.total_dependents_value, 0) as dependents_additionals_value'),
                DB::raw('COALESCE(fin.total_financial_value, 0) as financial_total_value'),

                DB::raw('(COALESCE(op.product_value, 0) + COALESCE(oad.total_dependents_value, 0)) as calculated_order_total'),
                DB::raw('(
                    (COALESCE(op.product_value, 0) + COALESCE(oad.total_dependents_value, 0))
                    - COALESCE(fin.total_financial_value, 0)
                ) as difference_value'),
            ])
            ->join('clients', 'clients.id', '=', 'orders.client_id')

            // order_prices (1 registro por pedido, em geral)
            ->leftJoin(DB::raw('
                (
                    SELECT order_id, MAX(product_value) as product_value
                    FROM order_prices
                    GROUP BY order_id
                ) op
            '), 'op.order_id', '=', 'orders.id')

            // soma dos adicionais dos dependentes por pedido
            ->leftJoin(DB::raw('
                (
                    SELECT order_id, SUM(value) as total_dependents_value
                    FROM order_aditionals_dependents
                    GROUP BY order_id
                ) oad
            '), 'oad.order_id', '=', 'orders.id')

            // soma do financeiro por pedido
            ->leftJoin(DB::raw('
                (
                    SELECT order_id, SUM(value) as total_financial_value
                    FROM financial
                    GROUP BY order_id
                ) fin
            '), 'fin.order_id', '=', 'orders.id')

            // filtros de busca
            ->when($this->search, function ($q) {
                $term = trim($this->search);

                $q->where(function ($sub) use ($term) {
                    $sub->where('orders.id', 'like', "%{$term}%")
                        ->orWhere('clients.name', 'like', "%{$term}%")
                        ->orWhere('clients.cpf', 'like', "%{$term}%");
                });
            })

            // somente divergentes
            ->whereRaw('ROUND((COALESCE(op.product_value, 0) + COALESCE(oad.total_dependents_value, 0)), 2) != ROUND(COALESCE(fin.total_financial_value, 0), 2)')

            ->orderByDesc('orders.id')
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.order-financial-divergences-list', [
            'rows' => $this->rows,
        ]);
    }
}