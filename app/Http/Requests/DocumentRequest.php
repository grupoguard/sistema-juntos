<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DocumentRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Permite a validação sem autenticação
    }

    public function rules()
    {
        return [
            'cpf' => ['required', 'string', function ($attribute, $value, $fail) {
                $cpf = preg_replace('/[^0-9]/', '', $value); // Remove caracteres não numéricos
                if (strlen($cpf) !== 11 || !$this->validarCpf($cpf)) {
                    $fail('O CPF informado é inválido.');
                }
            }],
            'rg' => ['required', 'string', function ($attribute, $value, $fail) {
                $rg = preg_replace('/[^0-9]/', '', $value); // Remove caracteres não numéricos
                if (strlen($rg) < 7 || !$this->validarRg($rg)) {
                    $fail('O RG informado é inválido.');
                }
            }],
        ];
    }

    private function validarCpf($cpf)
    {
        // Remove caracteres não numéricos
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Cálculo dos dígitos verificadores
        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }

    private function validarRg($rg)
    {
        return strlen(preg_replace('/[^0-9]/', '', $rg)) >= 7; // Simples, mas pode ser ajustado para regras estaduais
    }
}
