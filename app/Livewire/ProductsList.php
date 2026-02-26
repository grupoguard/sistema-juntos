<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Database\QueryException;

class ProductsList extends Component
{
    use WithPagination, AuthorizesRequests;

    public $search;
    public $statusFilter = '';
    public $confirmingDelete = false;
    public $deleteId;
    public $successMessage = null;
    public $errorMessage = null;

    public function mount()
    {
        $this->authorize('viewAny', Product::class);
    }
    
    public function updated($type, $value)
    {
        if (in_array($type, ['search', 'statusFilter'])) {
            $this->resetPage();
        }
        $this->{$type} = $value;
    }

    public function confirmDelete($id)
    {
        $this->successMessage = null;
        $this->errorMessage = null;

        $product = Product::findOrFail($id);
        $this->authorize('delete', $product);

        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        if (! $this->deleteId) return;

        $product = Product::findOrFail($this->deleteId);
        $this->authorize('delete', $product);

        // bloqueio amigável (regra de negócio)
        if (method_exists($product, 'orders') && $product->orders()->exists()) {
            $this->errorMessage = 'Este produto possui pedidos vinculados e não pode ser excluído.';
            $this->confirmingDelete = false;
            $this->deleteId = null;
            return;
        }

        try {
            $product->delete();

            $this->successMessage = 'Produto excluído com sucesso!';
            $this->confirmingDelete = false;
            $this->deleteId = null;

        } catch (QueryException $e) {
            $errorCode = $e->errorInfo[1] ?? null;

            if ($errorCode === 1451) {
                $this->errorMessage = 'Não é possível excluir este produto porque existem registros vinculados (ex.: pedidos).';
            } else {
                $this->errorMessage = 'Erro ao excluir produto: ' . $e->getMessage();
            }

            $this->confirmingDelete = false;
            $this->deleteId = null;
        }
    }

    private function getProducts()
    {
        return Product::where(function ($query) {
            if (!empty($this->search)) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('code', 'like', "%{$this->search}%")
                      ->orWhere('value', 'like', "%{$this->search}%");
            }
        })
        ->when($this->statusFilter !== '', function ($query) {
            $query->where('status', $this->statusFilter);
        })
        ->orderBy('name')
        ->paginate(10);
    }

    public function render()
    {
        return view('livewire.products-list',[
            'products' => $this->getProducts(),
            'search' => $this->search,
            'statusFilter' => $this->statusFilter,
        ]);
    }
}
