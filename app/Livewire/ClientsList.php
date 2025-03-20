<?php

namespace App\Livewire;

use App\Models\Client;
use Livewire\Component;
use Livewire\WithPagination;

class ClientsList extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    public function updated($propertyName)
    {
        /*if (in_array($propertyName, ['search', 'statusFilter'])) {
            $this->resetPage(); // Reseta a paginação sempre que um filtro mudar
        }*/
        dump($propertyName);
        $this->resetPage();
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

    public function render()
    {
        dump($this->search, $this->statusFilter);

        $clients = Client::query()
            ->when(!empty($this->search), function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->paginate(10);

        return view('livewire.clients-list', compact('clients'));
    }
}
