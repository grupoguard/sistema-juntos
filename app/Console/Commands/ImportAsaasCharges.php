<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderPrice;
use App\Models\Financial;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImportAsaasCharges extends Command
{
    protected $signature = 'asaas:import-charges 
                            {--test : Modo teste - não salva no banco}
                            {--limit= : Limitar quantidade de cobranças}';
    
    protected $description = 'Importa todas as cobranças do Asaas e vincula aos pedidos';

    private $apiKey;
    private $apiUrl;
    private $stats = [
        'total_charges' => 0,
        'imported' => 0,
        'skipped_existing' => 0,
        'client_not_found' => 0,
        'order_not_found' => 0,
        'price_mismatch' => 0,
        'errors' => 0
    ];

    public function __construct()
    {
        parent::__construct();
        $this->apiKey = config('services.asaas.api_key');
        $this->apiUrl = config('services.asaas.api_url');
    }

    public function handle()
    {
        $this->info('🚀 Iniciando importação de cobranças do Asaas...');
        $this->newLine();

        $testMode = $this->option('test');
        $limit = $this->option('limit');

        if ($testMode) {
            $this->warn('⚠️  MODO TESTE ATIVO - Nenhum dado será salvo no banco!');
            $this->newLine();
        }

        // Buscar todas as cobranças do Asaas
        $charges = $this->fetchAllCharges($limit);
        
        $this->stats['total_charges'] = count($charges);
        $this->info("📊 Total de cobranças encontradas: {$this->stats['total_charges']}");
        $this->newLine();

        // Processar cada cobrança
        $progressBar = $this->output->createProgressBar(count($charges));
        $progressBar->start();

        foreach ($charges as $charge) {
            $this->processCharge($charge, $testMode);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Exibir relatório final
        $this->displayReport();

        return Command::SUCCESS;
    }

    private function fetchAllCharges($limit = null)
    {
        $allCharges = [];
        $offset = 0;
        $limitPerRequest = 100; // Limite da API Asaas

        $this->info('📡 Buscando cobranças do Asaas...');

        do {
            try {
                $response = Http::withHeaders([
                    'access_token' => $this->apiKey,
                    'Content-Type' => 'application/json'
                ])->get("{$this->apiUrl}/payments", [
                    'offset' => $offset,
                    'limit' => $limitPerRequest
                ]);

                if (!$response->successful()) {
                    $this->error('Erro ao buscar cobranças: ' . $response->body());
                    break;
                }

                $data = $response->json();
                $charges = $data['data'] ?? [];
                
                if (empty($charges)) {
                    break;
                }

                $allCharges = array_merge($allCharges, $charges);
                $offset += $limitPerRequest;

                // Respeitar limite se fornecido
                if ($limit && count($allCharges) >= $limit) {
                    $allCharges = array_slice($allCharges, 0, $limit);
                    break;
                }

            } catch (\Exception $e) {
                $this->error('Erro: ' . $e->getMessage());
                break;
            }

        } while (!empty($charges));

        return $allCharges;
    }

    private function processCharge($charge, $testMode)
    {
        try {
            $asaasPaymentId = $charge['id'];

            // Verificar se já existe
            if (Financial::where('asaas_payment_id', $asaasPaymentId)->exists()) {
                $this->stats['skipped_existing']++;
                return;
            }

            // Buscar dados do cliente no Asaas
            $customer = $this->fetchCustomer($charge['customer']);
            
            if (!$customer) {
                $this->stats['client_not_found']++;
                $this->logError($asaasPaymentId, 'Cliente não encontrado no Asaas', $charge);
                return;
            }

            // Localizar cliente pelo CPF
            $cpf = $this->cleanCpf($customer['cpfCnpj']);
            $client = Client::where('cpf', $cpf)->first();

            if (!$client) {
                $this->stats['client_not_found']++;
                $this->logError($asaasPaymentId, "Cliente com CPF {$cpf} não encontrado no banco", $charge);
                return;
            }

            // Localizar pedido do cliente
            $order = Order::where('client_id', $client->id)->first();

            if (!$order) {
                $this->stats['order_not_found']++;
                $this->logError($asaasPaymentId, "Pedido não encontrado para cliente {$client->name}", $charge);
                return;
            }

            // Buscar preço do pedido
            $orderPrice = OrderPrice::where('order_id', $order->id)->first();
            $expectedValue = $orderPrice ? $orderPrice->product_value : null;

            // Verificar divergência de valor
            $chargeValue = (float) $charge['value'];
            $hasPriceMismatch = false;

            if ($expectedValue && abs($chargeValue - $expectedValue) > 0.01) {
                $hasPriceMismatch = true;
                $this->stats['price_mismatch']++;
            }

            // Preparar dados para inserção
            $financialData = [
                'order_id' => $order->id,
                'asaas_payment_id' => $asaasPaymentId,
                'asaas_customer_id' => $charge['customer'],
                'value' => $chargeValue,
                'paid_value' => $charge['value'] ?? null,
                'due_date' => $charge['dueDate'],
                'payment_method' => $this->mapPaymentMethod($charge['billingType']),
                'status' => $charge['status'],
                'external_reference' => $charge['externalReference'] ?? null,
                'invoice_url' => $charge['invoiceUrl'] ?? null,
                'bank_slip_url' => $charge['bankSlipUrl'] ?? null,
                'pix_qr_code' => $charge['pix']['payload'] ?? null,
                'pix_qr_code_url' => $charge['pix']['qrCode']['url'] ?? null,
                'description' => $charge['description'] ?? null,
                'created_at' => $charge['dateCreated'] ?? now(),
                'updated_at' => now()
            ];

            // Adicionar flag de divergência se necessário
            if ($hasPriceMismatch) {
                // Vamos adicionar um campo obs ou flag depois
                $financialData['obs'] = "⚠️ VALOR DIVERGENTE - Esperado: R$ " . 
                    number_format($expectedValue, 2, ',', '.') . 
                    " | Cobrado: R$ " . number_format($chargeValue, 2, ',', '.');
            }

            if (!$testMode) {
                Financial::create($financialData);
            }

            $this->stats['imported']++;

        } catch (\Exception $e) {
            $this->stats['errors']++;
            $this->logError($charge['id'] ?? 'unknown', $e->getMessage(), $charge);
        }
    }

    private function fetchCustomer($customerId)
    {
        try {
            $response = Http::withHeaders([
                'access_token' => $this->apiKey,
                'Content-Type' => 'application/json'
            ])->get("{$this->apiUrl}/customers/{$customerId}");

            if ($response->successful()) {
                return $response->json();
            }

            return null;

        } catch (\Exception $e) {
            return null;
        }
    }

    private function cleanCpf($cpf)
    {
        return preg_replace('/\D/', '', $cpf);
    }

    private function mapPaymentMethod($billingType)
    {
        $map = [
            'BOLETO' => 'BOLETO',
            'CREDIT_CARD' => 'CREDIT_CARD',
            'PIX' => 'PIX',
            'DEBIT_CARD' => 'DEBIT_CARD',
            'UNDEFINED' => 'BOLETO' // Default
        ];

        return $map[$billingType] ?? 'BOLETO';
    }

    private function logError($asaasId, $message, $data)
    {
        // Salvar log de erro
        DB::table('asaas_logs')->insert([
            'action' => 'import_charge_error',
            'asaas_id' => $asaasId,
            'entity_type' => 'payment',
            'request_data' => json_encode($data),
            'status' => 'error',
            'error_message' => $message,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    private function displayReport()
    {
        $this->info('📋 RELATÓRIO DE IMPORTAÇÃO');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line("Total de cobranças: {$this->stats['total_charges']}");
        $this->line("<fg=green>✓ Importadas com sucesso: {$this->stats['imported']}</>");
        $this->line("<fg=yellow>⊘ Já existentes (puladas): {$this->stats['skipped_existing']}</>");
        $this->line("<fg=red>✗ Cliente não encontrado: {$this->stats['client_not_found']}</>");
        $this->line("<fg=red>✗ Pedido não encontrado: {$this->stats['order_not_found']}</>");
        $this->line("<fg=magenta>⚠ Divergência de preço: {$this->stats['price_mismatch']}</>");
        $this->line("<fg=red>✗ Erros gerais: {$this->stats['errors']}</>");
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        if ($this->stats['price_mismatch'] > 0) {
            $this->newLine();
            $this->warn("⚠️  {$this->stats['price_mismatch']} cobranças com valores divergentes!");
            $this->info("Execute: php artisan financial:list-mismatches para ver a lista completa");
        }

        if ($this->stats['client_not_found'] > 0 || $this->stats['order_not_found'] > 0) {
            $this->newLine();
            $this->warn("⚠️  Alguns registros não puderam ser vinculados.");
            $this->info("Verifique a tabela 'asaas_logs' para detalhes dos erros.");
        }
    }
}