<?php

namespace App\Livewire;

use App\Models\Client;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class ClientsList extends Component
{
    use WithPagination;

    public $search;
    public $statusFilter = '';
    
    public function updated($type, $value)
    {
        if (in_array($type, ['search', 'statusFilter'])) {
            $this->resetPage();
        }
        $this->{$type} = $value;
    }

    public function delete($clientId)
    {
        $client = Client::find($clientId);
        if ($client) {
            $client->delete();
            session()->flash('success', 'Cliente excluído com sucesso.');
        } else {
            session()->flash('error', 'Cliente não encontrado.');
        }
    }

    private function getClients()
    {
        return Client::where(function ($query) {
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
        return view('livewire.clients-list',[
            'clients' => $this->getClients(),
            'search' => $this->search,
            'statusFilter' => $this->statusFilter,
        ]);
    }
}
