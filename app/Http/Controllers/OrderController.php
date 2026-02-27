<?php

namespace App\Http\Controllers;

use App\Models\Dependent;
use App\Models\Order;
use App\Models\OrderAditionalDependent;
use Barryvdh\DomPDF\Facade\Pdf;
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

    public function show(\App\Models\Order $order)
    {
        $this->authorize('view', $order);

        return view('pages.admin.orders.show', compact('order'));
    }

    public function easyform()
    {
        $this->authorize('create', \App\Models\Order::class);
        
        return view('pages.admin.orders.easyform');
    }

    public function financialDivergences()
    {
        return view('pages.admin.orders.financial-divergences');
    }

    public function contractPreview(Order $order)
    {
        $this->authorize('view', $order);
        $data = $this->buildContractData($order);
        return view('contracts.order-contract', $data);
    }

    public function contractPdf(Order $order)
    {
        $this->authorize('view', $order);
        $data = $this->buildContractData($order);
        $pdf = Pdf::loadView('pdf.contract', $data)
            ->setPaper('a4');
        return $pdf->download("contrato-pedido-{$order->id}.pdf");
    }

    private function buildContractData(Order $order): array
    {
        $order->load(['client', 'product', 'seller']);

        $dependentIds = OrderAditionalDependent::where('order_id', $order->id)
            ->distinct()
            ->pluck('dependent_id');

        $deps = Dependent::whereIn('id', $dependentIds)->get()->keyBy('id');

        $addsGrouped = OrderAditionalDependent::query()
            ->where('order_id', $order->id)
            ->join('aditionals', 'aditionals.id', '=', 'order_aditionals_dependents.aditional_id')
            ->orderBy('aditionals.name')
            ->get([
                'order_aditionals_dependents.dependent_id',
                'aditionals.name as name',
                'order_aditionals_dependents.value as value',
            ])
            ->groupBy('dependent_id');

        $dependents = $dependentIds->map(function ($id) use ($deps, $addsGrouped) {
            $dep = $deps->get($id);

            $adds = ($addsGrouped->get($id) ?? collect())->map(fn ($a) => [
                'name' => $a->name,
                'value' => (float) $a->value,
            ])->values()->toArray();

            return [
                'dependent_id'   => (int) $id,
                'name'           => $dep->name ?? '',
                'relationship'   => $dep->relationship ?? '',
                'cpf'            => $dep->cpf ?? '',
                'rg'             => $dep->rg ?? '',
                'date_birth'     => $dep->date_birth ?? '',
                'mom_name'       => $dep->mom_name ?? '',
                'marital_status' => $dep->marital_status ?? '',
                'additionals'    => $adds,
            ];
        })->values()->toArray();

        return compact('order', 'dependents');
    }
}
