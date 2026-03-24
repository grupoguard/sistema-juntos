<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AsaasSyncFinancials extends Command
{
    protected $signature = 'asaas:sync-financials
        {--test : Não grava no banco}
        {--limit= : Limita quantidade total de cobranças}
        {--fromOffset=0 : Começa de um offset específico}
        {--onlyPaid=0 : Se 1, só processa cobranças pagas (RECEIVED/CONFIRMED/RECEIVED_IN_CASH)}';

    protected $description = 'Sincroniza cobranças do Asaas com financial + financial_asaas (upsert por asaas_payment_id)';

    private string $apiKey;
    private string $apiUrl;

    /** @var array<string, array|null> */
    private array $customerCache = [];

    private array $paidStatuses = ['RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH'];

    private array $stats = [
        'total_loaded' => 0,
        'processed' => 0,
        'created' => 0,
        'updated' => 0,
        'unmatched' => 0,
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
        $onlyPaid = ((int)$this->option('onlyPaid')) === 1;

        if ($test) {
            $this->warn('MODO TESTE: não gravará no banco.');
        }

        $payments = $this->fetchAllPayments($offset, $limit);
        $this->stats['total_loaded'] = count($payments);

        $this->info("Cobranças carregadas do Asaas: {$this->stats['total_loaded']}");

        $bar = $this->output->createProgressBar($this->stats['total_loaded']);
        $bar->start();

        foreach ($payments as $payment) {
            try {
                if ($onlyPaid && !in_array(($payment['status'] ?? ''), $this->paidStatuses, true)) {
                    $bar->advance();
                    continue;
                }

                $this->syncOnePayment($payment, $test);
                $this->stats['processed']++;
            } catch (\Throwable $e) {
                $this->stats['errors']++;
                $this->logAsaasError($payment['id'] ?? null, 'sync_financial_error', $e->getMessage(), $payment);
            } finally {
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['loaded', 'processed', 'created', 'updated', 'unmatched', 'errors'],
            [[
                $this->stats['total_loaded'],
                $this->stats['processed'],
                $this->stats['created'],
                $this->stats['updated'],
                $this->stats['unmatched'],
                $this->stats['errors'],
            ]]
        );

        return self::SUCCESS;
    }

    private function fetchAllPayments(int $startOffset, ?int $limitTotal): array
    {
        $all = [];
        $offset = $startOffset;
        $limitPerRequest = 100;

        while (true) {
            $resp = $this->asaas()->get("{$this->apiUrl}/payments", [
                'offset' => $offset,
                'limit'  => $limitPerRequest,
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
        }

        return $all;
    }

    private function syncOnePayment(array $payment, bool $test): void
    {
        $asaasPaymentId  = $payment['id'] ?? null;
        $asaasCustomerId = $payment['customer'] ?? null;

        if (!$asaasPaymentId || !$asaasCustomerId) {
            $this->markUnmatched($asaasPaymentId, $asaasCustomerId, null, 'missing_ids', $payment, $test);
            $this->stats['unmatched']++;
            return;
        }

        // Já existe?
        $existingAsaas = DB::table('financial_asaas')
            ->where('asaas_payment_id', $asaasPaymentId)
            ->first();

        if ($existingAsaas) {
            // Atualiza financial + financial_asaas
            $financialId = (int) $existingAsaas->financial_id;

            if (!$test) {
                DB::transaction(function () use ($financialId, $asaasPaymentId, $asaasCustomerId, $payment) {
                    $this->updateFinancialAndAsaas($financialId, $asaasPaymentId, $asaasCustomerId, $payment);
                });
            }

            $this->stats['updated']++;
            return;
        }

        // Não existe: resolve order_id
        [$order, $cpf] = $this->resolveOrderForPayment($payment, $asaasCustomerId);

        if (!$order) {
            $this->markUnmatched($asaasPaymentId, $asaasCustomerId, $cpf, 'order_not_found', $payment, $test);
            $this->stats['unmatched']++;
            return;
        }

        if ($test) {
            $this->stats['created']++;
            return;
        }

        DB::transaction(function () use ($order, $asaasPaymentId, $asaasCustomerId, $payment) {
            $financialId = $this->createFinancial($order->id, $payment);
            $this->createFinancialAsaas($financialId, $asaasPaymentId, $asaasCustomerId, $payment);
        });

        $this->stats['created']++;
    }

    /**
     * Resolve order:
     * 1) tenta externalReference (se você usar externalReference = order_id ou algo equivalente)
     * 2) tenta client.asaas_customer_id
     * 3) tenta CPF do customer no Asaas -> client.cpf
     * 4) escolhe order do client:
     *    - preferir status 'ativo'
     *    - senão o mais recente
     */
    private function resolveOrderForPayment(array $payment, string $asaasCustomerId): array
    {
        $cpf = null;

        // 1) externalReference (se for order_id numérico)
        $externalRef = $payment['externalReference'] ?? null;
        if ($externalRef) {
            $maybeId = preg_replace('/\D+/', '', (string)$externalRef);
            if ($maybeId) {
                $order = Order::find((int)$maybeId);
                if ($order) return [$order, null];
            }
        }

        // 2) client por asaas_customer_id
        $client = Client::where('asaas_customer_id', $asaasCustomerId)->first();

        // 3) cpf do customer no Asaas
        if (!$client) {
            $cpf = $this->getCpfFromAsaasCustomer($asaasCustomerId);
            if ($cpf) {
                $client = Client::where('cpf', $cpf)->first();

                // salva o asaas_customer_id no client (ajuda os próximos)
                if ($client && !$client->asaas_customer_id) {
                    $client->asaas_customer_id = $asaasCustomerId;
                    $client->save();
                }
            }
        }

        if (!$client) return [null, $cpf];

        $orders = Order::where('client_id', $client->id)
            ->orderByRaw("CASE WHEN status = 'ativo' THEN 0 ELSE 1 END")
            ->orderByDesc('updated_at')
            ->get();

        if ($orders->isEmpty()) return [null, $cpf];

        return [$orders->first(), $cpf];
    }

    private function createFinancial(int $orderId, array $payment): int
    {
        $dueDate = $payment['dueDate'] ?? null;
        $due = $dueDate ? Carbon::parse($dueDate)->format('Y-m-d') : null;

        $status = (string)($payment['status'] ?? 'PENDING');
        $isPaid = in_array($status, $this->paidStatuses, true);

        $value = (float)($payment['value'] ?? 0);

        $financialId = DB::table('financial')->insertGetId([
            'order_id' => $orderId,
            'value' => $value,
            'paid_value' => $isPaid ? $value : null,
            'charge_date' => $due ? (int) Carbon::parse($due)->day : null,
            'due_date' => $due,
            'payment_method' => $this->mapPaymentMethod($payment['billingType'] ?? null),
            'description' => $payment['description'] ?? null,
            'obs' => null,
            'charge_paid' => $isPaid ? 1 : 0,
            'status' => $status,
            'created_at' => $payment['dateCreated'] ?? now(),
            'updated_at' => now(),
        ]);

        return (int)$financialId;
    }

    private function createFinancialAsaas(int $financialId, string $asaasPaymentId, string $asaasCustomerId, array $payment): void
    {
        DB::table('financial_asaas')->insert([
            'financial_id' => $financialId,
            'asaas_payment_id' => $asaasPaymentId,
            'asaas_customer_id' => $asaasCustomerId,
            'external_reference' => $payment['externalReference'] ?? null,
            'invoice_url' => $payment['invoiceUrl'] ?? null,
            'bank_slip_url' => $payment['bankSlipUrl'] ?? null,
            'pix_qr_code' => data_get($payment, 'pix.payload'),
            'pix_qr_code_url' => data_get($payment, 'pix.qrCode.url'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function updateFinancialAndAsaas(int $financialId, string $asaasPaymentId, string $asaasCustomerId, array $payment): void
    {
        $dueDate = $payment['dueDate'] ?? null;
        $due = $dueDate ? Carbon::parse($dueDate)->format('Y-m-d') : null;

        $status = (string)($payment['status'] ?? 'PENDING');
        $isPaid = in_array($status, $this->paidStatuses, true);
        $value = (float)($payment['value'] ?? 0);

        // Atualiza financial
        DB::table('financial')->where('id', $financialId)->update([
            'value' => $value,
            'paid_value' => $isPaid ? $value : null,
            'charge_date' => $due ? (int) Carbon::parse($due)->day : null,
            'due_date' => $due,
            'payment_method' => $this->mapPaymentMethod($payment['billingType'] ?? null),
            'description' => $payment['description'] ?? null,
            'charge_paid' => $isPaid ? 1 : 0,
            'status' => $status,
            'updated_at' => now(),
        ]);

        // Atualiza financial_asaas
        DB::table('financial_asaas')->where('asaas_payment_id', $asaasPaymentId)->update([
            'financial_id' => $financialId, // mantém
            'asaas_customer_id' => $asaasCustomerId,
            'external_reference' => $payment['externalReference'] ?? null,
            'invoice_url' => $payment['invoiceUrl'] ?? null,
            'bank_slip_url' => $payment['bankSlipUrl'] ?? null,
            'pix_qr_code' => data_get($payment, 'pix.payload'),
            'pix_qr_code_url' => data_get($payment, 'pix.qrCode.url'),
            'updated_at' => now(),
        ]);
    }

    private function mapPaymentMethod(?string $billingType): string
    {
        return match ($billingType) {
            'PIX' => 'PIX',
            'CREDIT_CARD' => 'CREDIT_CARD',
            'DEBIT_CARD' => 'DEBIT_CARD',
            'BOLETO' => 'BOLETO',
            default => 'BOLETO',
        };
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

        $cpf = preg_replace('/\D/', '', (string)$cpfCnpj);
        return $cpf ?: null;
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
            'entity_type' => 'asaas_sync',
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