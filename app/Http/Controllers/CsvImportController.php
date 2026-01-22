<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CsvImportController extends Controller
{
    private $errorsLog = [];

    public function showForm()
    {
        return view('pages.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt'
        ]);

        try {
            $file = $request->file('csv_file');
            $csvData = $this->parseCsv($file->getRealPath());

            $this->processData($csvData);

            $message = 'Importação concluída com sucesso!';
            
            if (!empty($this->errorsLog)) {
                return redirect()->back()
                    ->with('success', $message)
                    ->with('errors_log', implode("\n", $this->errorsLog));
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Erro na importação: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro na importação: ' . $e->getMessage());
        }
    }

    private function parseCsv($filePath)
    {
        $data = [];
        $header = null;

        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                // Converter encoding de cada campo de ISO-8859-1 para UTF-8
                $row = array_map(function($value) {
                    return mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
                }, $row);
                
                if (!$header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        return $data;
    }

    private function processData($csvData)
    {
        // Agrupar por titular
        $titulares = array_filter($csvData, function($row) {
            return trim($row['relationship']) === 'TITULAR';
        });

        foreach ($titulares as $titularRow) {
            try {
                $this->processTitular($titularRow, $csvData);
            } catch (\Exception $e) {
                $errorMsg = "Erro ao processar titular {$titularRow['user_name']}: " . $e->getMessage();
                Log::error($errorMsg);
                $this->errorsLog[] = $errorMsg;
            }
        }
    }

    private function processTitular($titularRow, $allData)
    {
        $cpf = $this->sanitizeCpf($titularRow['cpf']);
        $name = trim($titularRow['user_name']);
        
        Log::info("Titular {$name} identificado");

        // Verificar se cliente existe
        $client = DB::table('clients')->where('cpf', $cpf)->first();

        if ($client) {
            Log::info("Titular {$name} com CPF {$cpf} já existe, atualizando cadastro na tabela clients");
            
            DB::table('clients')->where('id', $client->id)->update([
                'name' => $name,
                'mom_name' => trim($titularRow['mom_name']),
                'date_birth' => $this->formatDate($titularRow['date_birth']),
                'gender' => $this->formatGender($titularRow['gender']),
                'marital_status' => strtoupper(trim($titularRow['marital_status'])),
                'phone' => $this->formatPhone($titularRow['ddd'], $titularRow['phone']),
                'email' => $this->sanitizeEmail($titularRow['email']),
                'zipcode' => $this->sanitizeZipcode($titularRow['zipcode']),
                'address' => trim($titularRow['address']),
                'number' => trim($titularRow['number']),
                'complement' => $this->sanitizeComplement($titularRow['complement']),
                'neighborhood' => $this->sanitizeNeighborhood($titularRow['neighborhood']),
                'city' => $this->sanitizeCity($titularRow['city']),
                'state' => $this->sanitizeState($titularRow['state']),
                'status' => (int)$titularRow['status'],
                'group_id' => 1,
                'updated_at' => now()
            ]);
            
            $clientId = $client->id;
        } else {
            Log::info("Titular {$name} com CPF {$cpf} não existe, criando cadastro na tabela clients");
            
            $clientId = DB::table('clients')->insertGetId([
                'name' => $name,
                'mom_name' => trim($titularRow['mom_name']),
                'date_birth' => $this->formatDate($titularRow['date_birth']),
                'cpf' => $cpf,
                'gender' => $this->formatGender($titularRow['gender']),
                'marital_status' => strtoupper(trim($titularRow['marital_status'])),
                'phone' => $this->formatPhone($titularRow['ddd'], $titularRow['phone']),
                'email' => $this->sanitizeEmail($titularRow['email']),
                'zipcode' => $this->sanitizeZipcode($titularRow['zipcode']),
                'address' => trim($titularRow['address']),
                'number' => trim($titularRow['number']),
                'complement' => $this->sanitizeComplement($titularRow['complement']),
                'neighborhood' => $this->sanitizeNeighborhood($titularRow['neighborhood']),
                'city' => $this->sanitizeCity($titularRow['city']),
                'state' => $this->sanitizeState($titularRow['state']),
                'status' => (int)$titularRow['status'],
                'group_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            Log::info("Cliente cadastrado com ID {$clientId}");
        }

        // Processar dependentes
        $dependentIds = $this->processDependents($titularRow['name'], $allData, $clientId);

        // Criar/atualizar pedido
        $this->processOrder($clientId, $dependentIds);
    }

    private function processDependents($titularName, $allData, $clientId)
    {
        $dependentIds = [];
        
        // Buscar dependentes pelo nome do titular
        $dependentes = array_filter($allData, function($row) use ($titularName) {
            return trim($row['name']) === trim($titularName) && 
                   trim($row['relationship']) !== 'TITULAR';
        });

        foreach ($dependentes as $depRow) {
            try {
                $userName = trim($depRow['user_name']);
                $cpf = $this->sanitizeCpf($depRow['cpf']);
                $status = (int)$depRow['status'];
                
                Log::info("Dependente {$userName} do titular {$titularName} identificado");

                // Verificar se dependente existe
                $dependent = DB::table('dependents')
                    ->where('cpf', $cpf)
                    ->where('client_id', $clientId)
                    ->first();

                if ($dependent) {
                    Log::info("Dependente {$userName} com CPF {$cpf} já existe, atualizando");
                    
                    DB::table('dependents')->where('id', $dependent->id)->update([
                        'name' => $userName,
                        'mom_name' => trim($depRow['mom_name']),
                        'date_birth' => $this->formatDate($depRow['date_birth']),
                        'marital_status' => strtoupper(trim($depRow['marital_status'])),
                        'relationship' => strtoupper(trim($depRow['relationship'])),
                        'updated_at' => now()
                    ]);
                    
                    $dependentId = $dependent->id;
                } else {
                    Log::info("Dependente {$userName} com CPF {$cpf} não existe na tabela dependents, cadastrando");
                    
                    $dependentId = DB::table('dependents')->insertGetId([
                        'client_id' => $clientId,
                        'name' => $userName,
                        'mom_name' => trim($depRow['mom_name']),
                        'date_birth' => $this->formatDate($depRow['date_birth']),
                        'cpf' => $cpf,
                        'marital_status' => strtoupper(trim($depRow['marital_status'])),
                        'relationship' => strtoupper(trim($depRow['relationship'])),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    Log::info("Dependente CPF {$cpf} cadastrado com ID {$dependentId}");
                }

                // Adicionar à lista apenas se status = 1
                if ($status === 1) {
                    $dependentIds[] = $dependentId;
                }

            } catch (\Exception $e) {
                $errorMsg = "Erro ao processar dependente {$depRow['user_name']}: " . $e->getMessage();
                Log::error($errorMsg);
                $this->errorsLog[] = $errorMsg;
            }
        }

        return $dependentIds;
    }

    private function processOrder($clientId, $dependentIds)
    {
        // Verificar se já existe order
        $order = DB::table('orders')->where('client_id', $clientId)->first();

        if ($order) {
            Log::info("Order já existe para o cliente ID {$clientId}, atualizando");
            $orderId = $order->id;
        } else {
            Log::info("Order não existe para o cliente ID {$clientId}, criando");
            
            $orderId = DB::table('orders')->insertGetId([
                'client_id' => $clientId,
                'product_id' => 4,
                'group_id' => 1,
                'seller_id' => 1,
                'charge_type' => 'ASAAS',
                'charge_date' => 33,
                'accession' => 0.00,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            Log::info("Order criado com ID {$orderId}");
        }

        // Processar order_prices
        $this->processOrderPrices($orderId, $dependentIds);

        // Processar order_dependents
        $this->processOrderDependents($orderId, $dependentIds);

        // Processar order_aditionals_dependents
        $this->processOrderAdditionalsDependents($orderId, $dependentIds);
    }

    private function processOrderPrices($orderId, $dependentIds)
    {
        $dependentsCount = count($dependentIds);
        
        $existing = DB::table('order_prices')->where('order_id', $orderId)->first();

        $data = [
            'order_id' => $orderId,
            'product_id' => 4,
            'product_value' => 49.90,
            'dependent_value' => $dependentsCount > 0 ? 23.90 : null,
            'dependents_count' => $dependentsCount,
            'updated_at' => now()
        ];

        if ($existing) {
            DB::table('order_prices')->where('id', $existing->id)->update($data);
            Log::info("Registro em order_prices atualizado para o pedido {$orderId}");
        } else {
            $data['created_at'] = now();
            DB::table('order_prices')->insert($data);
            Log::info("Registro em order_prices criado para o pedido {$orderId}");
        }
    }

    private function processOrderDependents($orderId, $dependentIds)
    {
        // Remover dependentes existentes
        DB::table('order_dependents')->where('order_id', $orderId)->delete();

        foreach ($dependentIds as $dependentId) {
            DB::table('order_dependents')->insert([
                'order_id' => $orderId,
                'dependent_id' => $dependentId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            Log::info("Dependente {$dependentId} vinculado ao pedido {$orderId} em order_dependents");
        }
    }

    private function processOrderAdditionalsDependents($orderId, $dependentIds)
    {
        // Remover registros existentes
        DB::table('order_aditionals_dependents')->where('order_id', $orderId)->delete();

        foreach ($dependentIds as $dependentId) {
            DB::table('order_aditionals_dependents')->insert([
                'order_id' => $orderId,
                'dependent_id' => $dependentId,
                'aditional_id' => 1,
                'value' => 23.90,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            Log::info("Adicional criado para dependente {$dependentId} no pedido {$orderId}");
        }
    }

    // Funções de sanitização
    private function sanitizeCpf($cpf)
    {
        return preg_replace('/\D/', '', $cpf);
    }

    private function sanitizeZipcode($zipcode)
    {
        return preg_replace('/\D/', '', $zipcode);
    }

    private function formatPhone($ddd, $phone)
    {
        $ddd = preg_replace('/\D/', '', $ddd);
        $phone = preg_replace('/\D/', '', $phone);
        
        if (empty($ddd) || empty($phone)) {
            return '12999999999';
        }
        
        $fullPhone = $ddd . $phone;
        
        // Limitar a 11 caracteres (DDD 2 dígitos + telefone 9 dígitos)
        return substr($fullPhone, 0, 11);
    }

    private function formatDate($date)
    {
        try {
            // Formato do CSV: dd/mm/yyyy
            return Carbon::createFromFormat('d/m/Y', trim($date))->format('Y-m-d');
        } catch (\Exception $e) {
            Log::warning("Erro ao formatar data: {$date}");
            return null;
        }
    }

    private function formatGender($gender)
    {
        $gender = strtoupper(trim($gender));
        return $gender === 'F' ? 'FEMININO' : 'MASCULINO';
    }

    private function sanitizeEmail($email)
    {
        $email = trim($email);
        return empty($email) ? 'nao@informado.com' : $email;
    }

    private function sanitizeNeighborhood($neighborhood)
    {
        $neighborhood = trim($neighborhood);
        return empty($neighborhood) ? 'NAO INFORMADO' : $neighborhood;
    }

    private function sanitizeCity($city)
    {
        $city = trim($city);
        return empty($city) ? 'NAO INFORMADO' : $city;
    }

    private function sanitizeState($state)
    {
        $state = trim($state);
        return empty($state) ? 'XX' : $state;
    }

    private function sanitizeComplement($complement)
    {
        $complement = trim($complement);
        return empty($complement) ? null : $complement;
    }
}