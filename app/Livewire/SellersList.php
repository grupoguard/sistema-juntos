<?php

namespace App\Livewire;

use App\Models\Seller;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;

class SellersList extends Component
{
    use WithPagination, AuthorizesRequests;

    public ?string $successMessage = null;
    public ?string $errorMessage = null;    
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
        // limpa mensagens antigas ao tentar excluir novamente
        $this->successMessage = null;
        $this->errorMessage = null;

        if (! $this->deleteId) {
            $this->setError('Nenhum consultor foi selecionado para exclusão.');
            return;
        }

        try {
            $seller = Seller::findOrFail($this->deleteId);

            $this->authorize('delete', $seller);

            $seller->delete();

            $this->confirmingDelete = false;
            $this->deleteId = null;

            $this->setSuccess('Consultor excluído com sucesso!');
            return;

        } catch (ModelNotFoundException $e) {
            $this->confirmingDelete = false;
            $this->deleteId = null;

            $this->setError('Consultor não encontrado.');
            return;

        } catch (AuthorizationException $e) {
            // aqui você decide se fecha ou mantém aberto
            $this->confirmingDelete = false; // <- fecha para mostrar melhor
            $this->setError('Você não tem permissão para excluir este consultor.');
            return;

        } catch (QueryException $e) {
            $sqlState   = $e->errorInfo[0] ?? null;
            $driverCode = $e->errorInfo[1] ?? null;

            // FK constraint
            if ($sqlState === '23000' && (int) $driverCode === 1451) {
                // RECOMENDO fechar o modal no erro de vínculo
                $this->confirmingDelete = false;
                $this->setError('Não é possível excluir este consultor, pois existem registros vinculados.');
                return;
            }

            report($e);
            $this->confirmingDelete = false;
            $this->setError('Erro no banco de dados ao excluir o consultor.');
            return;

        } catch (\Throwable $e) {
            report($e);
            $this->confirmingDelete = false;
            $this->setError('Ocorreu um erro inesperado ao excluir o consultor.');
            return;
        }
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

    private function setSuccess(string $message): void
    {
        $this->successMessage = $message;
        $this->errorMessage = null;
    }

    private function setError(string $message): void
    {
        $this->errorMessage = $message;
        $this->successMessage = null;
    }

    public function render()
    {
        return view('livewire.sellers-list', [
            'sellers' => $this->getSellers(),
        ]);
    }
}
