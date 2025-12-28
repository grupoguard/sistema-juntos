<?php

namespace App\Livewire;

use App\Models\Partner;
use Livewire\Component;
use Livewire\WithPagination;

class PartnersList extends Component
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

    public function delete($partnerId)
    {
        if (!$this->deleteId) return;

        $partner = Partner::findOrFail($partnerId);

        if ($partner) {
            $partner->delete();
            session()->flash('message', 'Parceiro excluído com sucesso!');
            $this->confirmingDelete = false;
        } else {
            session()->flash('error', 'Parceiro não encontrado.');
            $this->confirmingDelete = false;
        }
    }

    private function getPartners()
    {
        return Partner::where(function ($query) {
            if (!empty($this->search)) {
                $query->where('company_name', 'like', "%{$this->search}%")
                      ->orWhere('fantasy_name', 'like', "%{$this->search}%")
                      ->orWhere('cnpj', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
            }
        })
        ->when($this->statusFilter !== '', function ($query) {
            $query->where('status', $this->statusFilter);
        })
        ->orderBy('fantasy_name')
        ->paginate(10);
    }

    public function render()
    {
        return view('livewire.partners-list',[
            'partners' => $this->getPartners(),
            'search' => $this->search,
            'statusFilter' => $this->statusFilter,
        ]);
    }
}
