<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToCollection;

class PlanilhaImport implements ToCollection, WithHeadingRow
{
    /**
    * @param Collection $collection
    */

    public $rows = [];

    public function collection(Collection $collection)
    {
        $this->rows = $collection->toArray(); // Apenas para teste
    }
}