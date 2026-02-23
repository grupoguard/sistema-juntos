<?php

namespace App\Livewire;

use App\Models\Seller;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SellersList extends Component
{
    use WithPagination, AuthorizesRequests;

    public $search = '';
    public $statusFilter = '';
    public $confirmingDelete = false;
    public $deleteId;

    public function mount()
    {
        $this->authorize('viewAny', Seller::class);
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
        $seller = Seller::findOrFail($id);
        $this->authorize('delete', $seller);

        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        if (! $this->deleteId) return;

        $seller = Seller::findOrFail($this->deleteId);
        $this->authorize('delete', $seller);

        $seller->delete();

        $this->confirmingDelete = false;
        $this->deleteId = null;

        session()->flash('message', 'Consultor excluído com sucesso!');
    }

    private function getSellers()
    {
        $user = auth()->user();

        return Seller::query()
            ->visibleTo($user)
            ->where(function ($query) {
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
        return view('livewire.sellers-list', [
            'sellers' => $this->getSellers(),
        ]);
    }
}
