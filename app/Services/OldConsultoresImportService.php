<?php

namespace App\Services;

use App\Models\OldConsultores;
use App\Models\Seller;
use App\Helpers\FormatHelper;

class OldConsultoresImportService
{
    public function handle()
    {
        OldConsultores::chunk(500, function ($records) {
            foreach ($records as $old) {
                $cpf = FormatHelper::limparString($old->cpf);
                $rg = FormatHelper::limparString($old->rg);
                $rgValido = (!$rg || $rg === $cpf || strlen($rg) < 2) ? null : $rg;
                $phone = FormatHelper::limparString($old->telefone);
                $zipcode = FormatHelper::limparString($old->cep);
                $number = is_numeric($old->num) ? (int)$old->num : null;
                Seller::updateOrCreate(
                    ['cpf' => $cpf],
                    [
                        'group_id' => 1,
                        'migration_id' => $old->registro,
                        'name' => $old->nome,
                        'date_birth' => $old->data_nascimento ? \Carbon\Carbon::parse($old->data_nascimento)->format('Y-m-d') : null,
                        'cpf' => $cpf,
                        'rg' => $rgValido,
                        'phone' => $phone,
                        'email' => $old->email,
                        'comission_type' => 0,
                        'comission_value' => 0.00,
                        'comission_recurrence' => 0,
                        'zipcode' => $zipcode,
                        'address' => $old->endereco,
                        'number' => $number,
                        'complement' => $old->complemento,
                        'neighborhood' => $old->bairro,
                        'city' => $old->cidade,
                        'state' => $old->estado,
                        'status' => $old->status === 'ativo' ? 1 : 0,
                        'obs' => null,
                    ]
                );
            }
        });
    }
}