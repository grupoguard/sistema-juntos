<?php

namespace App\Livewire;

use App\Models\Group;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class GroupsList extends Component
{
    use WithPagination, AuthorizesRequests;

    public $search;
    public $statusFilter = '';
    public $confirmingDelete = false;
    public $deleteId;

    public function mount()
    {
        $this->authorize('viewAny', Group::class);
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
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        if (! $this->deleteId) return;

        $group = Group::findOrFail($this->deleteId);
        $this->authorize('delete', $group);

        // Se tiver relação orders()
        if (method_exists($group, 'orders') && $group->orders()->exists()) {
            $this->addError('delete', 'Esta cooperativa possui pedidos vinculados e não pode ser excluída.');
            return;
        }

        $group->delete();

        $this->confirmingDelete = false;
        $this->deleteId = null;

        session()->flash('message', 'Cooperativa excluída com sucesso!');
    }

    private function getGroups()
    {
        $user = auth()->user();

        return Group::query()
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
        return view('livewire.groups-list', [
            'groups' => $this->getGroups(),
        ]);
    }
}
