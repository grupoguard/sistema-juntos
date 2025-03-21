<?php

namespace App\Http\Controllers;
use App\Services\ViaCepService;

use Illuminate\Http\Request;

class ViaCepController extends Controller
{
    public function buscarCep($cep)
    {
        $data = ViaCepService::buscarCep($cep);

        return response()->json($data, isset($data['error']) ? 400 : 200);
    }
}
