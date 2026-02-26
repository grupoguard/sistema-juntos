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

    public $successMessage = null;
    public $errorMessage = null;

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
        $this->successMessage = null;
        $this->errorMessage = null;

        $order = Order::findOrFail($id);
        $this->authorize('delete', $order);

        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function deleteOrder()
    {
        if (! $this->deleteId) return;

        $order = Order::findOrFail($this->deleteId);
        $this->authorize('delete', $order);

        // trava se tiver financeiro
        if ($order->financials()->exists()) {
            $this->errorMessage = 'Este pedido possui registros financeiros e não pode ser excluído.';
            $this->confirmingDelete = false;
            $this->deleteId = null;
            return;
        }

        $orderId = $order->id;

        DB::transaction(function () use ($orderId, $order) {
            DB::table('order_aditionals')->where('order_id', $orderId)->delete();
            DB::table('order_dependents')->where('order_id', $orderId)->delete();
            DB::table('order_prices')->where('order_id', $orderId)->delete();

            if ($order->charge_type === 'EDP') {
                DB::table('evidence_documents')->where('order_id', $orderId)->delete();
                DB::table('evidence_return')->where('order_id', $orderId)->delete();
            }

            Order::where('id', $orderId)->delete();
        });

        $this->successMessage = 'Pedido deletado com sucesso!';
        $this->confirmingDelete = false;
        $this->deleteId = null;
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
