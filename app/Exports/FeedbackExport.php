<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FeedbackExport implements FromCollection, WithHeadings
{
    protected $feedbackData;

    public function __construct($feedbackData)
    {
        $this->feedbackData = $feedbackData;
    }

    public function collection()
    {
        return collect($this->feedbackData);
    }

    public function headings(): array
    {
        return [
            'Cliente',
            'Instalação',
            'Status',
            'Mensagem'
        ];
    }
}
