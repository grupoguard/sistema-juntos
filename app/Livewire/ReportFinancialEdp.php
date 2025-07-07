<?php

namespace App\Livewire;

use App\Models\LogMovement;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinancialReportExport;

class ReportFinancialEdp extends Component
{
    use WithPagination;

    public string $searchInicialDate = '';
    public string $searchFinalDate = '';
    public string $reportType = '';
    public string $reportExport = '';
    public float $totalValue = 0.0;
    public array $results = [];

    public function updated($property)
    {
        // Limpa os resultados quando qualquer filtro muda
        if (in_array($property, ['searchInicialDate', 'searchFinalDate', 'reportType'])) {
            $this->results = [];
            $this->totalValue = 0.0;
        }
    }

    public function search()
    {
        $this->validate([
            'searchInicialDate' => 'required|date',
            'searchFinalDate' => 'required|date|after_or_equal:searchInicialDate',
            'reportType' => 'required|in:01,06',
            'reportExport' => 'required|in:0,1',
        ]);

        // Converte as datas do formato Y-m-d para Ymd (formato do banco)
        $dataInicial = str_replace('-', '', $this->searchInicialDate);
        $dataFinal = str_replace('-', '', $this->searchFinalDate);

        // Debug: verificar se há registros no período
        $totalRegistros = LogMovement::query()
            ->whereBetween('date_movement', [$dataInicial, $dataFinal])
            ->where('code_return', $this->reportType)
            ->count();

        // Se não há registros, define arrays vazios
        if ($totalRegistros === 0) {
            $this->results = [];
            $this->totalValue = 0.0;
            return;
        }

        // Consulta única para obter dados e total
        $queryResults = LogMovement::query()
            ->whereBetween('date_movement', [$dataInicial, $dataFinal])
            ->where('code_return', $this->reportType)
            ->selectRaw("
                CONCAT(SUBSTRING(date_movement, 1, 4), '-', SUBSTRING(date_movement, 5, 2)) AS mes,
                installation_number,
                code_return,
                SUM(CAST(value AS SIGNED)) AS total_raw
            ")
            ->groupBy('mes', 'installation_number', 'code_return')
            ->orderBy('mes')
            ->orderBy('installation_number')
            ->get();

        // Processa os resultados
        $this->results = $queryResults->map(function ($item) {
            return [
                'mes' => $item->mes,
                'installation_number' => $item->installation_number,
                'code_return' => $item->code_return,
                'valor' => number_format($item->total_raw / 100, 2, ',', '.'),
            ];
        })->toArray();

        // Calcula o total
        $this->totalValue = $queryResults->sum('total_raw') / 100;

        // Se for para exportar Excel
        if ($this->reportExport === '1') {
            return Excel::download(
                new FinancialReportExport($this->results), 
                'relatorio_financeiro_' . date('Y-m-d_H-i-s') . '.xlsx'
            );
        }
    }

    // Método para debug - remover após teste
    public function testQuery()
    {
        $dataInicial = '20250401';
        $dataFinal = '20250531';
        
        $teste = LogMovement::query()
            ->whereBetween('date_movement', [$dataInicial, $dataFinal])
            ->where('code_return', '01')
            ->selectRaw('date_movement, installation_number, code_return, value')
            ->limit(5)
            ->get();
            
        dd($teste->toArray());
    }

    public function render()
    {
        return view('livewire.report-financial-edp', [
            'results' => $this->results,
        ]);
    }
}