<?php

namespace App\Livewire;

use App\Models\Aditional;
use App\Models\Dependent;
use App\Models\Order;
use App\Models\OrderDependent;
use App\Models\Product;
use App\Models\Seller;
use App\Traits\OrderFormTrait;
use Illuminate\Routing\Route;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OrderEdit extends Component
{
    use WithFileUploads, OrderFormTrait, AuthorizesRequests;

    public $data;
    public $charge_type;

    public function mount($orderId)
    {
        $this->orderId = $orderId;

        $this->order = Order::findOrFail($orderId);

        $this->authorize('view', $this->order);

        $user = auth()->user();

        if ($user->isAdmin()) {
            $this->order->markAsViewedBy($user);
            $this->order->refresh();
        }

        // trava acesso ao pedido específico
        $this->authorize('view', $this->order);
        $this->authorize('update', $this->order);

        $this->dependents = OrderDependent::where('order_id', $orderId)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->dependent->name ?? '',
                'relationship' => $item->dependent->relationship ?? '',
                'cpf' => $item->dependent->cpf ?? '',
                'rg' => $item->dependent->rg ?? '',
                'date_birth' => $item->dependent->date_birth ?? '',
                'marital_status' => $item->dependent->marital_status ?: 'nao_informado',
                'mom_name' => $item->dependent->mom_name ?? '',
            ])
            ->toArray();

        $this->client = $this->order->client->toArray();

        // seller/coop/admin devem enxergar listas limitadas também
        $user = auth()->user();

        $this->sellers = Seller::query()
            ->when($user->isCoop(), fn ($q) => $q->whereIn('group_id', $user->getAccessibleGroupIds()))
            ->when($user->isSeller(), fn ($q) => $q->whereIn('id', $user->getAccessibleSellerIds()))
            ->orderBy('name')
            ->get();

        // produtos por enquanto como está (depois refinamos)
        $this->products = Product::orderByDesc('status')->orderBy('name')->get();

        $this->charge_date = $this->order->charge_date;
        $this->seller_id = (string) $this->order->seller_id;
        $this->product_id = (string) $this->order->product_id;
        $this->charge_type = $this->order->charge_type;
        $this->installation_number = $this->order->installation_number;
        $this->approval_name = $this->order->approval_name;
        $this->approval_by = $this->order->approval_by;
        $this->evidence_date = $this->order->evidence_date;
        $this->accession_payment = $this->order->accession_payment ?? 'Não cobrada';
        $this->total = $this->order->orderPrice->product_value + $this->order->dependents_value;

        $this->dispatch('order-loaded');
    }

    public function approveOrder()
    {
        $order = Order::findOrFail($this->orderId);
        $this->authorize('update', $order); // ou policy específica de revisão

        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $order->update([
            'review_status' => 'APROVADO',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
            'review_notes' => $this->review_notes ?? null,
        ]);

        session()->flash('message', 'Pedido aprovado com sucesso!');
    }

    public function rejectOrder()
    {
        $order = Order::findOrFail($this->orderId);
        $this->authorize('update', $order);

        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $order->update([
            'review_status' => 'REJEITADO',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
            'review_notes' => $this->review_notes ?? null,
        ]);

        session()->flash('message', 'Pedido rejeitado com sucesso!');
    }

    public function render()
    {
        return view('livewire.order-edit', [
            'order' => $this->data,
            'charge_type' => $this->charge_type,
        ]);
    }
}
