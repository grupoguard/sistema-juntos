<?php

namespace App\Livewire;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class OrdersList extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $confirmingDelete = false;
    public $deleteId;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function deleteOrder()
    {
        if (!$this->deleteId) return;

        $orderId = $this->deleteId;

        DB::transaction(function () use ($orderId) {
            // Deletando registros relacionados ao pedido
            DB::table('order_aditionals')->where('order_id', $orderId)->delete();
            DB::table('order_dependents')->where('order_id', $orderId)->delete();
            DB::table('order_prices')->where('order_id', $orderId)->delete();
    
            // Verifica se o pedido é do tipo EDP para deletar evidências
            $order = Order::find($orderId);
            if ($order && $order->charge_type === 'EDP') {
                DB::table('evidence_documents')->where('order_id', $orderId)->delete();
                DB::table('evidence_return')->where('order_id', $orderId)->delete();
            }
    
            // Deletando o pedido principal
            Order::where('id', $orderId)->delete();
            
        });

        $this->confirmingDelete = false;
    
        session()->flash('message', 'Pedido deletado com sucesso!');
    }

    public function render()
    {
        $orders = Order::with(['client', 'product', 'seller'])
            ->whereHas('client', function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.orders-list', compact('orders'));
    }
}
