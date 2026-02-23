<?php

namespace App\Livewire;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OrdersList extends Component
{
    use WithPagination, AuthorizesRequests;

    public $search = '';
    public $perPage = 10;
    public $confirmingDelete = false;
    public $deleteId;

    public function mount()
    {
        $this->authorize('viewAny', Order::class);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $order = Order::findOrFail($id);
        $this->authorize('delete', $order);

        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function deleteOrder()
    {
        if (!$this->deleteId) return;

        $orderId = $this->deleteId;

        DB::transaction(function () use ($orderId) {
            $order = Order::findOrFail($orderId);
            $this->authorize('delete', $order);

            // Deletando registros relacionados ao pedido
            DB::table('order_aditionals')->where('order_id', $orderId)->delete();
            DB::table('order_dependents')->where('order_id', $orderId)->delete();
            DB::table('order_prices')->where('order_id', $orderId)->delete();
    
            if ($order && $order->charge_type === 'EDP') {
                DB::table('evidence_documents')->where('order_id', $orderId)->delete();
                DB::table('evidence_return')->where('order_id', $orderId)->delete();
            }
    
            // Deletando o pedido principal
            Order::where('id', $orderId)->delete();
            
        });

        $this->confirmingDelete = false;
        $this->deleteId = null;
    
        session()->flash('message', 'Pedido deletado com sucesso!');
    }

    public function render()
    {
        $user = auth()->user();

        $orders = Order::query()
            ->with(['client', 'product', 'seller'])
            ->visibleTo($user)
            ->whereHas('client', function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.orders-list', compact('orders'));
    }
}
