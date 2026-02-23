<?php

namespace App\Livewire;

use App\Models\Financial;
use App\Models\Order;
use App\Models\OrderAditionalDependent;
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
        $latestFinancialPerOrder = Financial::query()
            ->selectRaw('MAX(id) as id, order_id')
            ->groupBy('order_id');

        $rows = Order::query()
            ->from('orders')
            ->join('clients', 'clients.id', '=', 'orders.client_id')
            ->leftJoin('order_prices', 'order_prices.order_id', '=', 'orders.id')

            // Soma dos adicionais de dependentes por pedido
            ->leftJoinSub(
                OrderAditionalDependent::query()
                    ->selectRaw('order_id, COALESCE(SUM(value),0) as dependents_additionals_value')
                    ->groupBy('order_id'),
                'oad_sum',
                function ($join) {
                    $join->on('oad_sum.order_id', '=', 'orders.id');
                }
            )

            // Último financeiro por pedido
            ->leftJoinSub($latestFinancialPerOrder, 'lf', function ($join) {
                $join->on('lf.order_id', '=', 'orders.id');
            })
            ->leftJoin('financial', 'financial.id', '=', 'lf.id')

            ->selectRaw('
                orders.id,
                clients.name as client_name,
                clients.cpf as client_cpf,
                COALESCE(order_prices.product_value, 0) as product_value,
                COALESCE(oad_sum.dependents_additionals_value, 0) as dependents_additionals_value,
                (COALESCE(order_prices.product_value, 0) + COALESCE(oad_sum.dependents_additionals_value, 0)) as calculated_order_total,
                COALESCE(financial.value, 0) as financial_total_value,
                ROUND(
                    (COALESCE(order_prices.product_value, 0) + COALESCE(oad_sum.dependents_additionals_value, 0))
                    - COALESCE(financial.value, 0),
                    2
                ) as difference_value
            ')
            ->whereNotNull('financial.id')
            ->when($this->search, function ($query) {
                $search = trim($this->search);

                $query->where(function ($q) use ($search) {
                    $q->where('clients.name', 'like', "%{$search}%")
                      ->orWhere('clients.cpf', 'like', "%{$search}%")
                      ->orWhere('orders.id', 'like', "%{$search}%");
                });
            })
            ->whereRaw('ROUND((COALESCE(order_prices.product_value, 0) + COALESCE(oad_sum.dependents_additionals_value, 0)) - COALESCE(financial.value, 0), 2) <> 0')
            ->orderByDesc('orders.id')
            ->paginate($this->perPage);

        return $rows;
    }

    public function render()
    {
        return view('livewire.order-financial-divergences-list', [
            'rows' => $this->rows,
        ]);
    }
}