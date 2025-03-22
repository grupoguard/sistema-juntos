<?php

namespace App\Livewire;

use App\Models\Seller;
use Livewire\Component;
use Livewire\WithPagination;

class SellersList extends Component
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

        $seller = Seller::findOrFail($this->deleteId);

        if ($seller) {
            $seller->delete();
            session()->flash('message', 'Consultor excluído com sucesso!');
            $this->confirmingDelete = false;
        } else {
            session()->flash('error', 'Consultor não encontrado.');
            $this->confirmingDelete = false;
        }
    }

    private function getSellers()
    {
        return Seller::where(function ($query) {
            if (!empty($this->search)) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
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
        return view('livewire.sellers-list',[
            'sellers' => $this->getSellers(),
            'search' => $this->search,
            'statusFilter' => $this->statusFilter,
        ]);
    }
}
