<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ProductsList extends Component
{
    use WithPagination;

    public $search;
    public $statusFilter = '';
    public $confirmingDelete = false;
    public $deleteId;
    
    public function updated($type, $value)
    {
        if (in_array($type, ['search', 'statusFilter'])) {
            $this->resetPage();
        }
        $this->{$type} = $value;
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        if (!$this->deleteId) return;

        $product = Product::findOrFail($this->deleteId);

        if ($product->orders()->exists()) {
            $this->addError('delete', 'Este produto possui pedidos vinculados e não pode ser excluído.');
            return;
        }

        $product->delete();
        session()->flash('message', 'Produto excluído com sucesso!');
        
        // Resetando as variáveis
        $this->confirmingDelete = false;
        $this->deleteId = null;
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
