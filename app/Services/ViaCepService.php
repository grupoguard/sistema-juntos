<?php 

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ViaCepService
{
    public static function buscarCep($cep)
    {
        $cep = preg_replace('/[^0-9]/', '', $cep); // Remove caracteres não numéricos

        if (strlen($cep) !== 8) {
            return ['error' => 'CEP inválido'];
        }

        $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");

        if ($response->failed()) {
            return ['error' => 'Erro ao consultar o CEP'];
        }

        return $response->json();
    }
}
