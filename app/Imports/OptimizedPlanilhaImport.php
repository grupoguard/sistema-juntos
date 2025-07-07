<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class OptimizedPlanilhaImport implements ToCollection
{
    private $rows;

    public function collection(Collection $collection)
    {
        if ($collection->first() && !is_numeric($collection->first()[0] ?? null)) {
            $collection = $collection->skip(1);
        }
        
        $this->rows = $collection->map(function ($row) {
            return [
                'instalacao' => $row[0] ?? '',
                'produto' => $row[1] ?? '',
                'valor' => (float) ($row[2] ?? 0),
            ];
        })->filter(function ($row) {
            // Filtrar linhas vazias
            return !empty($row['instalacao']) && $row['valor'] > 0;
        });//
    }

    public function getRows()
    {
        return $this->rows;
    }
}
