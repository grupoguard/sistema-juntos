<?php

namespace App\Services;

use App\Models\OldClients;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderPrice;
use App\Models\Dependent;
use App\Models\OrderDependent;
use App\Helpers\FormatHelper;
use App\Models\Seller;

class OldClientsImportService
{
    public function handle()
    {
        OldClients::chunk(500, function ($records) {
            foreach ($records as $old) {
                //Verifica se é titular, se não for pula para o próximo
                if($old->titular === 's' && $old->valor_plano > 0) {
                    //Separa só os dados do cliente que são necessários
                    $clientData = $this->getClientData($old);

                    //Separa só os dados do order que são necessários
                    $orderData = $this->getOrderData($old);
                    
                    $dependents = $this->getDependents($old);
                    $numberDependents = $dependents->count();

                    //Cria o cliente na table
                    $client = $this->storeClient($clientData);

                    //Cria o pedido
                    $order = $this->storeOrder($orderData, $client);

                    //Cria os dependentes e vincula ao order_dependents
                   if ($dependents->count() > 0) {
                        $this->storeDependentsAndOrderDependent($dependents, $order, $client);
                    }

                    //Armazena o valor do dependente
                    $this->storeOrderPrice($order, $orderData, $numberDependents);
                }
            }
        });
    }

    public function getClientData($data)
    {
        $return = array();
        $return['name'] = $data->nome;
        $return['mom_name'] = 'NAO INFORMADO';
        $return['date_birth'] = $this->validateAndFixDate($data->data_nascimento);
        $return['cpf'] = FormatHelper::limparString($data->cpf);
        $return['rg'] = $this->validateRg($data->rg, $data->cpf);
        $return['gender'] = $data->sexo;
        $return['marital_status'] = $data->estado_civil;
        $return['phone'] = $this->validatePhone($data->ddd, $data->telefone);
        $return['email'] = $data->email;
        $return['zipcode'] = FormatHelper::limparString($data->cep);
        $return['address'] = $data->endereco;
        $return['number'] = is_numeric($data->num) ? (int)$data->num : null;;
        $return['complement'] = $data->complemento;
        $return['neighborhood'] = $data->bairro;
        $return['city'] = $data->cidade;
        $return['state'] = $data->estado;
        $return['obs'] = $data->obs01 . ' ' . $data->obs02;
        $return['status'] = $data->status === 'ativo' ? 1 : 0;

        return $return;
    }

    public function getOrderData($data) 
    {
        $seller = Seller::where('migration_id', $data->cadastrado_por)->first();

        $products = array(
            '408 - JUNTOS SAUDE' => 6,
            '408 - JUNTOS SAUDE ' => 6,
            'Saúde E' => 6,
            'Saúde E ' => 6,
            'Juntos Saúde' => 6,
            'Juntos Saúde ' => 6,
            '409 - JUNTOS BEM ESTAR' => 2,
            '409 - JUNTOS BEM ESTAR ' => 2,
            'Bem Estar E' => 2,
            'Bem Estar E ' => 2,
            'Bem Estar Promocional' => 2,
            'Bem Estar Promocional ' => 2,
            'Juntos Bem Estar I' => 2,
            'Juntos Bem Estar I ' => 2,
            'Juntos Bem-estar' => 2,
            'Juntos Bem-estar ' => 2,
            '410 - JUNTOS VIDA' => 3,
            '410 - JUNTOS VIDA ' => 3,
            '410 - VIDA' => 3,
            '410 - VIDA ' => 3,
            'Juntos Vida' => 3,
            'Juntos Vida ' => 3,
            'Juntos Vida I' => 3,
            'Juntos Vida I ' => 3,
            '411 - JUNTOS SORRISO' => 7,
            '411 - JUNTOS SORRISO ' => 7,
            'Juntos Sorriso' => 7,
            'Juntos Sorriso ' => 7,
            '412 - JUNTOS FAMILIA' => 8,
            '412 - JUNTOS FAMILIA ' => 8,
            'Juntos Família Premium' => 8,
            'Juntos Família Premium ' => 8,
            'Funeral + Telemedicina' => 9,
            'Funeral + Telemedicina ' => 9,
            'Juntos Consultores' => 10,
            'Juntos Consultores ' => 10,
            'Juntos Premium P' => 11,
            'Juntos Premium P ' => 11,
            'Juntos Sorriso P' => 12,
            'Juntos Sorriso P ' => 12,
            'Juntos Saúde P' => 13,
            'Juntos Saúde P ' => 13,
        );

        return [
            'seller_id' => $seller ? $seller->id : null, // CORREÇÃO: Verificar se seller existe
            'installation_number' => (int)$data->num_instalacao,
            'charge_type' => $data->idpEdp === 'edp' ? 'EDP' : 'BOLETO',
            'approval_by' => $data->autorizado_por === 'conjuge' ? 'Conjuge' : 'Titular',
            'evidence_date' => $this->validateAndFixDate($data->dataEvidencia),
            'product_id' => $products[$data->plano] ?? null,
            'product_value' => $data->valor_plano > 0 ? $data->valor_plano / 100 : 0,
        ];
    }

    public function getDependents($data)
    {
        return OldClients::where('num_card_titular', $data->num_card)
            ->where('id', '!=', $data->id) // Evita incluir o próprio titular
            ->get();
    }

    public function storeClient($data)
    {
        return Client::updateOrCreate(
            ['cpf' => $data['cpf']],
            array_merge($data, [
                'group_id' => 1,
            ])
        );
    }

    public function storeOrder($data, $client)
    {
        return Order::updateOrCreate(
            ['client_id' => $client->id],
            array_merge($data, [
                'group_id' => 1,
                'accession' => 0.00,
            ])
        );
    }

    public function storeDependentsAndOrderDependent($data, $order, $client)
    {
        foreach($data as $dependent) {
            $dependentCpf = FormatHelper::limparString($dependent->cpf);

            if (empty($dependentCpf)) {
                continue; // Pula dependente sem CPF
            }

            $idDependent = Dependent::updateOrCreate(
                ['cpf' => $dependentCpf],
                [
                    'client_id' => $client->id,
                    'name' => $dependent->nome,
                    'mom_name' => 'NAO INFORMADO',
                    'date_birth' => $this->validateAndFixDate($dependent->data_nascimento),
                    'cpf' => $dependentCpf,
                    'rg' => $this->validateRg($dependent->rg, $dependent->cpf),
                    'marital_status' => $dependent->estado_civil,
                    'relationship' => $dependent->parentesco,
                ]
            );
            OrderDependent::firstOrCreate([
                'order_id' => $order->id,
                'dependent_id' => $idDependent->id,
            ]);
        }
    }

    public function storeOrderPrice($order, $data, $numberDependents)
    {
        OrderPrice::updateOrCreate(
            ['order_id' => $order->id],
            [
                'product_id' => $data['product_id'],
                'product_value' => $data['product_value'],
                'dependent_value' => 0.00,
                'dependents_count' => $numberDependents,
            ]
        );
    }

    private function validateAndFixDate($date)
    {
        if (!$date) {
            return '1920-01-01';
        }

        try {
            $carbonDate = \Carbon\Carbon::parse($date);
            
            // Verifica se a data é muito antiga ou futura demais
            if ($carbonDate->year < 1900 || $carbonDate->year > date('Y')) {
                return '1920-01-01';
            }
            
            return $carbonDate->format('Y-m-d');
            
        } catch (\Exception $e) {
            // Se não conseguir fazer parse da data, retorna data padrão
            return '1920-01-01';
        }
    }

    private function validateRg($rg, $cpf)
    {
        $rg = FormatHelper::limparString($rg);
        $cpf = FormatHelper::limparString($cpf);

        // Se RG está vazio, retorna null
        if (empty($rg)) {
            return null;
        }

        // Se RG é igual ao CPF, retorna null (inválido)
        if ($rg === $cpf) {
            return null;
        }

        // Se RG tem mais de 9 dígitos, retorna null (inválido)
        if (strlen($rg) > 9) {
            return null;
        }

        // Se RG tem menos de 7 dígitos, provavelmente está incompleto
        if (strlen($rg) < 7) {
            return null;
        }

        // Se passou por todas as validações, retorna o RG limpo
        return $rg;
    }

    private function validatePhone($ddd, $phone)
    {
        $ddd = FormatHelper::limparString($ddd);
        $phone = FormatHelper::limparString($phone);
        $complete = $ddd . $phone;

        if (empty($complete)) {
            return null;
        }

        if (strlen($complete) > 11) {
            return null;
        }

        if (strlen($complete) < 8) {
            return null;
        }

        return $complete;
    }
}