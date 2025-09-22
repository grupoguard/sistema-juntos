<?php

namespace App\Traits;

use Carbon\Carbon;

trait TxtLayoutTrait
{
    public function generateHeader()
    {
        return sprintf(
            "A2%-20s%-20s408%-20s%s%-76s\n",
            "", 
            "BANDEIRANTE",
            substr("JUNTOS CLUBE DE BENEFICIO",0, 20),
            Carbon::now()->format('Ymd'),
            ""
        );
    }

    public function generateFooter($totalRecords, $totalAmount)
    {
        return sprintf(
            "Z%06d%017d%-126s\n",
            $totalRecords,
            $totalAmount * 100, // Multiplica por 100 para ajustar o valor como solicitado
            ""
        );
    }
}