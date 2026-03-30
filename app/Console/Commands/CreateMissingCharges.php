<?php

namespace App\Console\Commands;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class AsaasCreateMissingCharges extends Command
{
    protected $signature = 'asaas:create-missing-charges
        {--days=30 : Cria cobranças faltantes para os próximos N dias}
        {--test : Não cria no Asaas e não grava no banco}
        {--limit= : Limita quantidade de pedidos}
        {--only-order= : Processa apenas um order_id}
        {--include-card=0 : Se 1, tenta criar cobranças para CARTAO (CREDIT_CARD). Se 0, ignora CARTAO}
        {--only-status=ativo,inadimplente : Status de pedidos elegíveis}
        {--only-charge-types=BOLETO,CARTAO : Tipos de charge_type elegíveis}';

    protected $description = 'Cria cobranças faltantes no Asaas para os próximos N dias e grava em financial + financial_asaas + financial_logs';

    private string $apiKey;
    private string $apiUrl;

    private array $paidStatuses = ['RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH'];

    public function __construct()
    {
        parent::__construct();
        $this->apiKey = (string) config('services.asaas.api_key');
        $this->apiUrl = (string) config('services.asaas.api_url', 'https://api.asaas.com/v3');
    }

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $test = (bool) $this->option('test');
        $limit = $this->option('limit') ? (int)$this->option('limit') : null;
        $onlyOrder = $this->option('only-order') ? (int)$this->option('only-order') : null;
        $includeCard = ((int)$this->option('include-card')) === 1;

        $onlyStatus = array_filter(array_map('trim', explode(',', (string)$this->option('only-status'))));
        $onlyChargeTypes = array_filter(array_map('trim', explode(',', (string)$this->option('only-charge-types'))));

        if ($test) {
            $this->warn('MODO TESTE: não criará cobranças no Asaas e não gravará no banco.');
        }

        $start = Carbon::today('America/Sao_Paulo');
        $end = $start->copy()->addDays($days);

        $q = Order::query()
            ->select('orders.*', 'clients.asaas_customer_id')
            ->join('clients', 'clients.id', '=', 'orders.client_id')
            ->whereIn('orders.status', $onlyStatus)
            ->whereIn('orders.charge_type', $onlyChargeTypes);

        if ($onlyOrder) $q->where('orders.id', $onlyOrder);
        if ($limit) $q->limit($limit);

        $orders = $q->get();

        $this->info("Pedidos elegíveis: {$orders->count()} | Janela: {$start->toDateString()} → {$end->toDateString()}");

        $bar = $this->output->createProgressBar($orders->count());
        $bar->start();

        foreach ($orders as $order) {
            try {
                $this->processOrder($order, $start, $end, $test, $includeCard);
            } catch (\Throwable $e) {
                $this->error("Erro no order {$order->id}: {$e->getMessage()}");
            } finally {
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Concluído.');
        return self::SUCCESS;
    }

    private function processOrder($order, Carbon $start, Carbon $end, bool $test, bool $includeCard): void
    {
        $chargeType = strtoupper((string)$order->charge_type);

        // Se não quiser card agora, ignora
        if (!$includeCard && $chargeType === 'CARTAO') {
            return;
        }

        $customerId = $order->asaas_customer_id ?? null;
        if (!$customerId) {
            // Não tem como criar cobrança no Asaas sem customer id
            $this->logMissingCustomerId((int)$order->id, (int)$order->client_id);
            return;
        }

        $day = (int)($order->charge_date ?? 0);

        // Se charge_date inválido (ou 33 que você usa como placeholder), não inventa vencimento
        if ($day < 1 || $day > 31) {
            $this->logInvalidChargeDate((int)$order->id, $order->charge_date);
            return;
        }

        $dueDates = $this->generateDueDatesBetween($start, $end, $day);

        foreach ($dueDates as $due) {

            // 1) Se já existe cobrança Asaas para esse order+due_date, não cria
            $existsAsaas = DB::table('financial')
                ->join('financial_asaas', 'financial_asaas.financial_id', '=', 'financial.id')
                ->where('financial.order_id', $order->id)
                ->whereDate('financial.due_date', $due->toDateString())
                ->exists();

            if ($existsAsaas) {
                continue;
            }

            // 2) Se já existe um financial (mesmo sem asaas) no mesmo due_date e não está cancelado,
            // por segurança NÃO cria outra cobrança automaticamente (evita duplicação).
            $existsAny = DB::table('financial')
                ->where('order_id', $order->id)
                ->whereDate('due_date', $due->toDateString())
                ->whereNotIn('status', ['CANCELED', 'REFUNDED'])
                ->exists();

            if ($existsAny) {
                continue;
            }

            $amount = $this->calculateOrderAmount((int)$order->id);

            if ($amount <= 0) {
                $this->logInvalidOrderAmount((int)$order->id, $due->toDateString(), $amount);
                continue;
            }

            // billingType para o Asaas
            $billingType = $this->mapBillingTypeFromOrderChargeType($chargeType);

            // Se tentar cartão sem token/suporte, isso pode falhar. Você escolhe habilitar com --include-card=1.
            // A criação de cartão exige dados adicionais (token/cartão). Se falhar, você verá no log de erro.
            $externalReference = "order:{$order->id}|due:{$due->format('Y-m-d')}";

            $payload = [
                'customer' => $customerId,
                'billingType' => $billingType,
                'value' => $amount,
                'dueDate' => $due->format('Y-m-d'),
                'description' => "Mensalidade (Pedido {$order->id})",
                'externalReference' => $externalReference,
            ];

            if ($test) {
                $this->line("DRY-RUN: criaria cobrança order {$order->id} due {$due->toDateString()} value {$amount} billingType {$billingType}");
                continue;
            }

            $resp = $this->asaas()->post("{$this->apiUrl}/payments", $payload);

            if (!$resp->successful()) {
                $this->logCreatePaymentFailed((int)$order->id, $payload, $resp->status(), $resp->body());
                continue;
            }

            $payment = $resp->json();

            DB::transaction(function () use ($order, $due, $payment, $billingType) {
                $status = (string)($payment['status'] ?? 'PENDING');
                $value = (float)($payment['value'] ?? 0);
                $isPaid = in_array($status, $this->paidStatuses, true);

                $financialId = DB::table('financial')->insertGetId([
                    'order_id' => $order->id,
                    'value' => $value,
                    'paid_value' => $isPaid ? $value : null,
                    'charge_date' => (int)$due->day,
                    'due_date' => $due->format('Y-m-d'),
                    'payment_method' => $this->mapPaymentMethod($billingType),
                    'description' => $payment['description'] ?? null,
                    'obs' => 'Criado automaticamente (create-missing-charges)',
                    'charge_paid' => $isPaid ? 1 : 0,
                    'status' => $status,
                    'created_at' => $payment['dateCreated'] ?? now(),
                    'updated_at' => now(),
                ]);

                DB::table('financial_asaas')->insert([
                    'financial_id' => $financialId,
                    'asaas_payment_id' => $payment['id'] ?? null,
                    'asaas_customer_id' => $payment['customer'] ?? null,
                    'external_reference' => $payment['externalReference'] ?? null,
                    'invoice_url' => $payment['invoiceUrl'] ?? null,
                    'bank_slip_url' => $payment['bankSlipUrl'] ?? null,
                    'pix_qr_code' => data_get($payment, 'pix.payload'),
                    'pix_qr_code_url' => data_get($payment, 'pix.qrCode.url'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('financial_logs')->insert([
                    'financial_id' => $financialId,
                    'provider' => 'ASAAS',
                    'source_type' => 'API',
                    'source_id' => null,
                    'event_name' => 'ASAAS_PAYMENT_CREATED',
                    'old_status' => null,
                    'new_status' => $status,
                    'message' => "Cobrança criada no Asaas via rotina automática. due_date={$due->format('Y-m-d')}",
                    'payload' => json_encode([
                        'asaas_payment_id' => $payment['id'] ?? null,
                        'asaas_customer_id' => $payment['customer'] ?? null,
                        'billingType' => $payment['billingType'] ?? $billingType,
                        'value' => $value,
                        'dueDate' => $payment['dueDate'] ?? $due->format('Y-m-d'),
                        'externalReference' => $payment['externalReference'] ?? null,
                    ]),
                    'event_date' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
        }
    }

    private function generateDueDatesBetween(Carbon $start, Carbon $end, int $day): array
    {
        $dates = [];
        $cursor = $start->copy()->startOfMonth();
        $endMonth = $end->copy()->startOfMonth();

        while ($cursor->lte($endMonth)) {
            $d = $cursor->copy()->day(min($day, $cursor->daysInMonth));
            if ($d->betweenIncluded($start, $end)) {
                $dates[] = $d;
            }
            $cursor->addMonth();
        }

        return $dates;
    }

    private function calculateOrderAmount(int $orderId): float
    {
        $base = (float) DB::table('order_prices')->where('order_id', $orderId)->value('product_value');

        $depsSum = (float) DB::table('order_aditionals_dependents')
            ->where('order_id', $orderId)
            ->sum('value');

        $titularAddSum = 0.0;
        if (Schema::hasTable('order_aditionals')) {
            $titularAddSum = (float) DB::table('order_aditionals')
                ->where('order_id', $orderId)
                ->sum('value');
        }

        $total = $base + $depsSum + $titularAddSum;

        $order = DB::table('orders')->where('id', $orderId)->first();
        if ($order && $order->discount_type && $order->discount_value) {
            if ($order->discount_type === 'R$') {
                $total -= (float)$order->discount_value;
            } elseif ($order->discount_type === '%') {
                $total -= ($total * ((float)$order->discount_value / 100));
            }
        }

        return round(max($total, 0), 2);
    }

    private function mapBillingTypeFromOrderChargeType(string $chargeType): string
    {
        return match ($chargeType) {
            'CARTAO' => 'CREDIT_CARD',
            default => 'BOLETO',
        };
    }

    private function mapPaymentMethod(string $billingType): string
    {
        return match ($billingType) {
            'PIX' => 'PIX',
            'CREDIT_CARD' => 'CREDIT_CARD',
            'DEBIT_CARD' => 'DEBIT_CARD',
            'BOLETO' => 'BOLETO',
            default => 'BOLETO',
        };
    }

    private function logMissingCustomerId(int $orderId, int $clientId): void
    {
        // Sem customer_id não dá pra criar cobrança
        DB::table('asaas_unmatched_payments')->updateOrInsert(
            ['asaas_payment_id' => "missing_customer|order:{$orderId}"],
            [
                'asaas_customer_id' => null,
                'cpf' => null,
                'reason' => 'missing_asaas_customer_id',
                'payload' => json_encode(['order_id' => $orderId, 'client_id' => $clientId]),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    private function logInvalidChargeDate(int $orderId, $chargeDate): void
    {
        DB::table('asaas_unmatched_payments')->updateOrInsert(
            ['asaas_payment_id' => "invalid_charge_date|order:{$orderId}"],
            [
                'asaas_customer_id' => null,
                'cpf' => null,
                'reason' => 'invalid_charge_date',
                'payload' => json_encode(['order_id' => $orderId, 'charge_date' => $chargeDate]),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    private function logInvalidOrderAmount(int $orderId, string $dueDate, float $amount): void
    {
        DB::table('asaas_unmatched_payments')->updateOrInsert(
            ['asaas_payment_id' => "invalid_amount|order:{$orderId}|due:{$dueDate}"],
            [
                'asaas_customer_id' => null,
                'cpf' => null,
                'reason' => 'invalid_order_amount',
                'payload' => json_encode(['order_id' => $orderId, 'due_date' => $dueDate, 'amount' => $amount]),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    private function logCreatePaymentFailed(int $orderId, array $payload, int $statusCode, string $body): void
    {
        DB::table('asaas_unmatched_payments')->updateOrInsert(
            ['asaas_payment_id' => "create_failed|order:{$orderId}|due:" . ($payload['dueDate'] ?? 'unknown')],
            [
                'asaas_customer_id' => $payload['customer'] ?? null,
                'cpf' => null,
                'reason' => 'asaas_create_payment_failed',
                'payload' => json_encode(['order_id' => $orderId, 'payload' => $payload, 'http_status' => $statusCode, 'response' => $body]),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    private function asaas()
    {
        return Http::withHeaders([
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(60);
    }
}