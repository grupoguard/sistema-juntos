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
    public $successMessage = null;
    public $errorMessage = null;

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
        $group = Group::findOrFail($id);
        $this->authorize('delete', $group);

        $this->successMessage = null;
        $this->errorMessage = null;
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        if (! $this->deleteId) return;

        $group = Group::findOrFail($this->deleteId);
        $this->authorize('delete', $group);

        // checagens prévias (ótimo manter)
        if ($group->orders()->exists()) {
            $this->errorMessage = 'Esta cooperativa possui pedidos vinculados e não pode ser excluída.';
            $this->confirmingDelete = false;
            $this->deleteId = null;
            return;
        }

        if ($group->sellers()->exists()) {
            $this->errorMessage = 'Esta cooperativa possui consultores vinculados e não pode ser excluída.';
            $this->confirmingDelete = false;
            $this->deleteId = null;
            return;
        }

        try {
            $group->delete();

            $this->successMessage = 'Cooperativa excluída com sucesso!';
            $this->confirmingDelete = false;
            $this->deleteId = null;

        } catch (QueryException $e) {
            // FK violation MySQL: 1451
            $errorCode = $e->errorInfo[1] ?? null;

            if ($errorCode === 1451) {
                $this->errorMessage = 'Não é possível excluir esta cooperativa porque existem registros vinculados (ex.: pedidos/consultores).';
            } else {
                $this->errorMessage = 'Erro ao excluir cooperativa: ' . $e->getMessage();
            }

            $this->confirmingDelete = false;
            $this->deleteId = null;
        }
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
