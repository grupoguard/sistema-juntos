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

        $client = Client::findOrFail($this->deleteId);

        if ($client) {
            // Verifica se o cliente tem pedidos vinculados
            if ($client->orders()->exists()) {
                $this->addError('delete', 'Este cliente possui pedidos vinculados e não pode ser excluído.');
                return;
            }

            $client->delete();
            session()->flash('message', 'Cliente excluído com sucesso!');
            $this->confirmingDelete = false;
        } else {
            session()->flash('error', 'Cliente não encontrado.');
            $this->confirmingDelete = false;
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
