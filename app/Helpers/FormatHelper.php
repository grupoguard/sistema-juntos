<?php

namespace App\Helpers;

class FormatHelper
{
    public static function limparString(string $valor): string
    {
        return preg_replace('/[^A-Za-z0-9]/', '', $valor);
    }
}