<?php

namespace App\Livewire;

use App\Models\Client;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class ClientsList extends Component
{
    use WithPagination, AuthorizesRequests;

    public $search = '';
    public $statusFilter = '';
    public $perPage = 10;
    public $successMessage = null;
    public $errorMessage = null;

    public $confirmingDelete = false;
    public $deleteId;

    public function mount()
    {
        $this->authorize('viewAny', Client::class);
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
        $client = Client::findOrFail($id);
        $this->authorize('delete', $client);

        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        $client = Client::findOrFail($this->deleteId);
        $this->authorize('delete', $client);

        if ($client->orders()->exists()) {
            $this->errorMessage = 'Este cliente possui pedidos vinculados e não pode ser excluído.';
            $this->confirmingDelete = false;
            $this->deleteId = null;
            return;
        }

        $client->delete();

        $this->successMessage = 'Cliente excluído com sucesso!';
        $this->confirmingDelete = false;
        $this->deleteId = null;
    }

    private function getClients()
    {
        $user = auth()->user();

        return Client::query()
            ->visibleTo($user)
            ->when($this->search, function ($q) {
                $s = trim($this->search);
                $q->where(function ($qq) use ($s) {
                    $qq->where('name', 'like', "%{$s}%")
                       ->orWhere('email', 'like', "%{$s}%");
                });
            })
            ->when($this->statusFilter !== '', function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.clients-list', [
            'clients' => $this->getClients(),
        ]);
    }
}