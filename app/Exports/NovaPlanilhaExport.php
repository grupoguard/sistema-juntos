<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NovaPlanilhaExport implements FromCollection, WithHeadings
{
    private $dados;

    public function __construct(array $dados)
    {
        $this->dados = collect($dados); // Converte para coleção
    }

    public function collection()
    {
        return collect($this->dados);
    }

    public function headings(): array
    {
        // Converte o primeiro item de $this->dados para um array, se possível
        $primeiroDado = $this->dados->first();
        // Define os cabeçalhos da planilha
        return $primeiroDado ? array_keys((array) $primeiroDado) : [];
    }
}
