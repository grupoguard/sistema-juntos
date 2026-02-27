<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Order;
use App\Models\Financial;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class AsaasImportAndNormalize extends Command
{
    protected $signature = 'asaas:import-and-normalize
        {--test : Não grava no banco}
        {--limit= : Limita quantidade de cobranças}
        {--fromOffset=0 : Começa de um offset específico}';

    protected $description = 'Importa 100% cobranças do Asaas (upsert em financial) e normaliza orders (discount, charge_date, charge_type)';

    private string $apiKey;
    private string $apiUrl;

    /** @var array<string, array|null> */
    private array $customerCache = [];

    private array $paidStatuses = ['RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH'];

    private array $stats = [
        'payments_total' => 0,
        'financial_created' => 0,
        'financial_updated' => 0,
        'unmatched' => 0,
        'orders_normalized' => 0,
        'orders_with_ambiguous_match' => 0,
        'errors' => 0,
    ];

    public function __construct()
    {
        parent::__construct();
        $this->apiKey = (string) config('services.asaas.api_key');
        $this->apiUrl = (string) config('services.asaas.api_url'); // ex: https://api.asaas.com/v3
    }

    public function handle(): int
    {
        $test = (bool) $this->option('test');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $offset = (int) $this->option('fromOffset');

        if ($test) {
            $this->warn('MODO TESTE ATIVO: não gravará no banco.');
        }

        $payments = $this->fetchAllPayments($offset, $limit);

        $this->stats['payments_total'] = count($payments);
        $this->info("Total de cobranças carregadas do Asaas: {$this->stats['payments_total']}");

        $bar = $this->output->createProgressBar($this->stats['payments_total']);
        $bar->start();

        // 1) Importa/upserta financial
        foreach ($payments as $payment) {
            try {
                $this->upsertFinancialFromPayment($payment, $test);
            } catch (\Throwable $e) {
                $this->stats['errors']++;
                $this->logAsaasError($payment['id'] ?? null, 'import_payment_error', $e->getMessage(), $payment);
            } finally {
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        // 2) Normaliza os pedidos que têm cobrança Asaas vinculada (order_id não nulo)
        $this->normalizeOrdersFromFinancial($test);

        $this->newLine();
        $this->info('RELATÓRIO FINAL');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line("Cobranças carregadas: {$this->stats['payments_total']}");
        $this->line("Financial criados: {$this->stats['financial_created']}");
        $this->line("Financial atualizados: {$this->stats['financial_updated']}");
        $this->line("Unmatched (sem pedido): {$this->stats['unmatched']}");
        $this->line("Pedidos normalizados: {$this->stats['orders_normalized']}");
        $this->line("Pedidos com match ambíguo: {$this->stats['orders_with_ambiguous_match']}");
        $this->line("Erros: {$this->stats['errors']}");
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        return self::SUCCESS;
    }

    private function fetchAllPayments(int $startOffset, ?int $limitTotal): array
    {
        $all = [];
        $offset = $startOffset;
        $limitPerRequest = 100;

        do {
            $resp = $this->asaas()->get("{$this->apiUrl}/payments", [
                'offset' => $offset,
                'limit' => $limitPerRequest,
            ]);

            if (!$resp->successful()) {
                $this->logAsaasError(null, 'list_payments_error', $resp->body(), ['offset' => $offset]);
                break;
            }

            $data = $resp->json();
            $chunk = $data['data'] ?? [];
            if (empty($chunk)) break;

            $all = array_merge($all, $chunk);
            $offset += $limitPerRequest;

            if ($limitTotal && count($all) >= $limitTotal) {
                $all = array_slice($all, 0, $limitTotal);
                break;
            }

        } while (!empty($chunk));

        return $all;
    }

    private function upsertFinancialFromPayment(array $payment, bool $test): void
    {
        $asaasPaymentId = $payment['id'] ?? null;
        $asaasCustomerId = $payment['customer'] ?? null;

        if (!$asaasPaymentId || !$asaasCustomerId) {
            $this->markUnmatched($asaasPaymentId, $asaasCustomerId, null, 'missing_ids', $payment, $test);
            $this->stats['unmatched']++;
            return;
        }

        // Resolve order pelo customer_id (melhor) ou cpf (fallback).
        [$order, $cpf] = $this->resolveOrderForPayment($asaasCustomerId);

        if (!$order) {
            $this->markUnmatched($asaasPaymentId, $asaasCustomerId, $cpf, 'order_not_found', $payment, $test);
            $this->stats['unmatched']++;
            return;
        }

        $financialData = $this->mapFinancialData($payment, $order->id);

        $existing = Financial::where('asaas_payment_id', $asaasPaymentId)->first();

        if ($test) {
            $existing ? $this->stats['financial_updated']++ : $this->stats['financial_created']++;
            return;
        }

        if ($existing) {
            $existing->fill($financialData)->save();
            $this->stats['financial_updated']++;
            return;
        }

        Financial::create($financialData);
        $this->stats['financial_created']++;
    }

    /**
     * Retorna: [Order|null, cpf|null]
     * Resolve em ordem:
     * 1) client.asaas_customer_id
     * 2) cpf do customer no Asaas
     *
     * Se houver pedidos duplicados do mesmo client, escolhe determinístico:
     * - primeiro 'ativo'
     * - senão, o mais recente (updated_at desc)
     * E loga a ambiguidade.
     */
    private function resolveOrderForPayment(string $asaasCustomerId): array
    {
        $cpf = null;

        $client = Client::where('asaas_customer_id', $asaasCustomerId)->first();

        if (!$client) {
            $cpf = $this->getCpfFromAsaasCustomer($asaasCustomerId);

            if ($cpf) {
                $client = Client::where('cpf', $cpf)->first();

                if ($client && !$client->asaas_customer_id) {
                    // grava customer id para acelerar os próximos
                    $client->asaas_customer_id = $asaasCustomerId;
                    $client->save();
                }
            }
        }

        if (!$client) {
            return [null, $cpf];
        }

        // Pode ter duplicado. Vamos buscar TODOS do produto 4 (Uniodonto)
        $orders = Order::where('client_id', $client->id)
            ->where('product_id', 4)
            ->orderByRaw("CASE WHEN status = 'ativo' THEN 0 ELSE 1 END")
            ->orderByDesc('updated_at')
            ->get();

        if ($orders->isEmpty()) {
            return [null, $cpf];
        }

        if ($orders->count() > 1) {
            $this->stats['orders_with_ambiguous_match']++;

            // loga (pra você tratar depois)
            DB::table('asaas_logs')->insert([
                'action' => 'ambiguous_order_match',
                'asaas_id' => $asaasCustomerId,
                'entity_type' => 'customer',
                'request_data' => json_encode([
                    'client_id' => $client->id,
                    'orders_found' => $orders->pluck('id')->all(),
                ]),
                'status' => 'error',
                'error_message' => 'Mais de um order encontrado para o mesmo client/product. Escolhido determinístico (ativo > updated_at).',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return [$orders->first(), $cpf];
    }

    private function getCpfFromAsaasCustomer(string $asaasCustomerId): ?string
    {
        if (array_key_exists($asaasCustomerId, $this->customerCache)) {
            $customer = $this->customerCache[$asaasCustomerId];
        } else {
            $resp = $this->asaas()->get("{$this->apiUrl}/customers/{$asaasCustomerId}");
            $customer = $resp->successful() ? $resp->json() : null;
            $this->customerCache[$asaasCustomerId] = $customer;
        }

        if (!$customer) return null;

        $cpfCnpj = $customer['cpfCnpj'] ?? null;
        if (!$cpfCnpj) return null;

        $cpf = preg_replace('/\D/', '', $cpfCnpj);
        return $cpf ?: null;
    }

    private function mapFinancialData(array $payment, int $orderId): array
    {
        $value = (float) ($payment['value'] ?? 0);
        $due = $payment['dueDate'] ?? null;

        return [
            'order_id' => $orderId,
            'asaas_payment_id' => $payment['id'] ?? null,
            'asaas_customer_id' => $payment['customer'] ?? null,
            'value' => $value,
            'paid_value' => $payment['value'] ?? null,
            'charge_date' => $due ? (int) Carbon::parse($due)->day : null,
            'due_date' => $due ? Carbon::parse($due)->format('Y-m-d') : null,
            'payment_method' => $this->mapFinancialPaymentMethod($payment['billingType'] ?? null),
            'status' => $payment['status'] ?? 'PENDING',
            'external_reference' => $payment['externalReference'] ?? null,
            'invoice_url' => $payment['invoiceUrl'] ?? null,
            'bank_slip_url' => $payment['bankSlipUrl'] ?? null,
            'pix_qr_code' => data_get($payment, 'pix.payload'),
            'pix_qr_code_url' => data_get($payment, 'pix.qrCode.url'),
            'description' => $payment['description'] ?? null,
            'obs' => null,
            'created_at' => $payment['dateCreated'] ?? now(),
            'updated_at' => now(),
        ];
    }

    // financial.payment_method (enum que você tem)
    private function mapFinancialPaymentMethod(?string $billingType): string
    {
        return match ($billingType) {
            'PIX' => 'PIX',
            'CREDIT_CARD' => 'CREDIT_CARD',
            'DEBIT_CARD' => 'DEBIT_CARD',
            'BOLETO' => 'BOLETO',
            default => 'BOLETO',
        };
    }

    // orders.charge_type (você quer: BOLETO ou CARTAO quando for Asaas)
    private function mapOrderChargeType(?string $billingType): string
    {
        return match ($billingType) {
            'CREDIT_CARD', 'DEBIT_CARD' => 'CARTAO',
            default => 'BOLETO', // PIX cai aqui também como você pediu
        };
    }

    private function normalizeOrdersFromFinancial(bool $test): void
    {
        // Pega todos os orders que têm pelo menos 1 cobrança Asaas vinculada
        $orderIds = Financial::query()
            ->whereNotNull('order_id')
            ->whereNotNull('asaas_payment_id')
            ->distinct()
            ->pluck('order_id');

        if ($orderIds->isEmpty()) {
            $this->info('Nenhum pedido com cobranças Asaas vinculadas para normalizar.');
            return;
        }

        $this->info('Normalizando pedidos com base na última cobrança (paga ou última criada)...');
        $bar = $this->output->createProgressBar($orderIds->count());
        $bar->start();

        foreach ($orderIds as $orderId) {
            try {
                $this->normalizeOneOrder((int)$orderId, $test);
                $this->stats['orders_normalized']++;
            } catch (\Throwable $e) {
                $this->stats['errors']++;
                $this->logAsaasError((string)$orderId, 'normalize_order_error', $e->getMessage(), ['order_id' => $orderId]);
            } finally {
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
    }

    private function normalizeOneOrder(int $orderId, bool $test): void
    {
        $order = Order::find($orderId);
        if (!$order) return;

        // 1) pega última paga; se não tiver, pega última criada
        $lastPaid = Financial::where('order_id', $orderId)
            ->whereIn('status', $this->paidStatuses)
            ->orderByDesc('due_date')
            ->first();

        $lastAny = Financial::where('order_id', $orderId)
            ->orderByDesc('created_at')
            ->first();

        $ref = $lastPaid ?: $lastAny;
        if (!$ref) return;

        // Inadimplente: 3 meses sem pagar
        $cutoff = now()->subMonths(3);

        $refDate = null;
        if ($lastPaid && $lastPaid->updated_at) {
            // Melhor proxy pra "data do pagamento" no seu cenário atual (webhook atualiza)
            $refDate = Carbon::parse($lastPaid->updated_at);
        } elseif ($lastPaid && $lastPaid->due_date) {
            $refDate = Carbon::parse($lastPaid->due_date);
        } elseif ($lastAny && $lastAny->due_date) {
            $refDate = Carbon::parse($lastAny->due_date);
        } elseif ($lastAny && $lastAny->created_at) {
            $refDate = Carbon::parse($lastAny->created_at);
        }

        if ($order->status !== 'cancelado' && $refDate && $refDate->lt($cutoff)) {
            $order->status = 'inadimplente';
        }

        // 2) calcula total atual do pedido
        $base = (float) DB::table('order_prices')->where('order_id', $orderId)->value('product_value');
        if ($base <= 0) {
            // Sem preço base: não mexe no pedido para evitar desconto errado
            return;
        }

        $depsSum = (float) DB::table('order_aditionals_dependents')
            ->where('order_id', $orderId)
            ->sum('value');

        $titularAddSum = 0.0;
        if (Schema::hasTable('order_aditionals')) {
            $titularAddSum = (float) DB::table('order_aditionals')
                ->where('order_id', $orderId)
                ->sum('value');
        }

        $totalAtual = $base + $depsSum + $titularAddSum;

        // 3) valor de referência do Asaas
        $lastValue = (float) ($ref->value ?? 0);
        if ($lastValue <= 0) {
            // Sem valor no Asaas: não aplica desconto
            $lastValue = 0;
        }

        // 4) desconto
        $discountValue = $totalAtual > $lastValue ? round($totalAtual - $lastValue, 2) : 0.0;
        $discountType = $discountValue > 0 ? 'R$' : null;

        // 5) charge_date e charge_type (somente se for Asaas)
        $chargeDate = $ref->due_date ? (int) Carbon::parse($ref->due_date)->day : null;

        $isAsaas = !empty($ref->asaas_payment_id) || !empty($ref->asaas_customer_id);

        if ($test) return;

        $order->discount_type = $discountType;
        $order->discount_value = $discountValue > 0 ? $discountValue : null;
        $order->charge_date = $chargeDate ?? $order->charge_date;

        if ($isAsaas) {
            $orderChargeType = $this->mapOrderChargeType(
                $this->reverseMapFinancialToBillingType($ref->payment_method)
            );
            $order->charge_type = $orderChargeType; // EDP -> BOLETO/CARTAO
        }

        $order->save();
    }

    /**
     * Aqui é só para reaproveitar o que você já tem salvo em financial.payment_method.
     * Você pode simplificar e guardar billingType bruto em financial no futuro (recomendado),
     * mas por ora isso funciona.
     */
    private function reverseMapFinancialToBillingType(?string $paymentMethod): ?string
    {
        return match ($paymentMethod) {
            'CREDIT_CARD' => 'CREDIT_CARD',
            'DEBIT_CARD' => 'DEBIT_CARD',
            'PIX' => 'PIX',
            default => 'BOLETO',
        };
    }

    private function markUnmatched(?string $paymentId, ?string $customerId, ?string $cpf, string $reason, array $payload, bool $test): void
    {
        if ($test) return;

        DB::table('asaas_unmatched_payments')->updateOrInsert(
            ['asaas_payment_id' => $paymentId],
            [
                'asaas_customer_id' => $customerId,
                'cpf' => $cpf,
                'reason' => $reason,
                'payload' => json_encode($payload),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    private function logAsaasError(?string $asaasId, string $action, string $errorMessage, array $requestData): void
    {
        DB::table('asaas_logs')->insert([
            'action' => $action,
            'asaas_id' => $asaasId,
            'entity_type' => 'payment',
            'request_data' => json_encode($requestData),
            'status' => 'error',
            'error_message' => $errorMessage,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function asaas()
    {
        return Http::withHeaders([
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(60);
    }
}