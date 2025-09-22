<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

class EvidenciaController extends Controller
{
    public function upload(Request $request)
    {
        // Validação do arquivo
        $request->validate([
            'Arquivo' => 'required|file|mimes:jpg,jpeg,png|max:2048', // Ajuste conforme necessário
        ]);

        // Armazenar o arquivo
        if ($request->hasFile('Arquivo')) {
            $path = $request->file('Arquivo')->store('evidencias', 'local'); // 'local' é o disco padrão
            return response()->json(['path' => $path], 200);
        }

        return response()->json(['message' => 'Arquivo não enviado.'], 400);
    }
}