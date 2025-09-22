<?php

namespace App\Livewire;

use App\Models\LogMovement;
use App\Models\LogRegister;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class RelatorioEdp extends Component
{
    use WithPagination;

    public $statusFilter = '';
    public $searchEvidences = '';
    public $searchFinancial = '';
    public $activeTab = '#financial-data-tab';

    public function mount()
    {
        $this->activeTab = session('activeTab', '#evidence-data-tab');
    }
    
    public function updated($type, $value)
    {
        if (in_array($type, ['search', 'statusFilter'])) {
            $this->resetPage();
        }
        $this->{$type} = $value;
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->dispatch('tabChanged', $tab);
    }

    public function updateTab($tab)
    {
        $this->activeTab = $tab;
        session(['activeTab' => $tab]);
    }

    public function render()
    {
        // Filtrar registros do log_register (Evidências)
        $evidenceReturns = LogRegister::with(['anomalyCode', 'moveCode'])
            ->when($this->searchEvidences, function ($query) {
                $query->where('installation_number', 'like', '%' . $this->searchEvidences . '%');
            })
            ->orderBy('id')
            ->paginate(25, ['*'], 'evidencePage'); // Paginação separada

        // Filtrar registros do log_movement (Financeiro)
        $financialReturns = LogMovement::with(['returnCode'])
            ->when($this->searchFinancial, function ($query) {
                $query->where('installation_number', 'like', '%' . $this->searchFinancial . '%');
            })
            ->orderBy('date_movement')
            ->paginate(25, ['*'], 'financialPage'); // Paginação separada

        return view('livewire.relatorio-edp', [
            'evidenceReturns' => $evidenceReturns,
            'financialReturns' => $financialReturns,
        ]);
    }
}
