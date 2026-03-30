<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AsaasReconcile extends Command
{
    protected $signature = 'asaas:reconcile
        {--days=120 : Filtra localmente por dueDate (hoje ± N dias)}
        {--limit= : Limita total de payments processados}
        {--fromOffset=0 : Offset inicial na listagem}
        {--test : Não grava no banco}
        {--fetch-cpf=0 : Se 1, busca CPF do customer no Asaas quando não achar client por asaas_customer_id}';

    protected $description = 'Reconcilia Asaas x sistema: cria o que falta e atualiza somente diferenças em financial + financial_asaas + financial_logs';

    private string $apiKey;
    private string $apiUrl;

    private array $paidStatuses = ['RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH'];

    /** @var array<string, array|null> */
    private array $customerCache = [];

    public function __construct()
    {
        parent::__construct();
        $this->apiKey = (string) config('services.asaas.api_key');
        $this->apiUrl = (string) config('services.asaas.api_url', 'https://api.asaas.com/v3');
    }

    public function handle(): int
    {
        $days = (int)$this->option('days');
        $limit = $this->option('limit') ? (int)$this->option('limit') : null;
        $offset = (int)$this->option('fromOffset');
        $test = (bool)$this->option('test');
        $fetchCpf = ((int)$this->option('fetch-cpf')) === 1;

        if ($test) $this->warn('MODO TESTE: não gravará no banco.');

        $start = Carbon::today('America/Sao_Paulo')->subDays($days);
        $end = Carbon::today('America/Sao_Paulo')->addDays($days);

        $payments = $this->fetchAllPayments($offset, $limit);
        $this->info("Payments carregados do Asaas: " . count($payments));

        $created = 0; $updated = 0; $unmatched = 0; $skipped = 0; $errors = 0;

        $bar = $this->output->createProgressBar(count($payments));
        $bar->start();

        foreach ($payments as $p) {
            try {
                $dueStr = data_get($p, 'dueDate');
                if ($dueStr) {
                    $due = Carbon::parse($dueStr);
                    if (!$due->betweenIncluded($start, $end)) {
                        $skipped++;
                        $bar->advance();
                        continue;
                    }
                }

                $result = $this->reconcileOnePayment($p, $test, $fetchCpf);
                if ($result === 'created') $created++;
                elseif ($result === 'updated') $updated++;
                elseif ($result === 'unmatched') $unmatched++;
            } catch (\Throwable $e) {
                $errors++;
            } finally {
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['created', 'updated', 'unmatched', 'skipped', 'errors'],
            [[ $created, $updated, $unmatched, $skipped, $errors ]]
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
                'limit' => $limitPerRequest,
            ]);

            if (!$resp->successful()) {
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

    private function reconcileOnePayment(array $payment, bool $test, bool $fetchCpf): string
    {
        $asaasPaymentId  = (string) (data_get($payment, 'id') ?? '');
        $asaasCustomerId = (string) (data_get($payment, 'customer') ?? '');

        if ($asaasPaymentId === '' || $asaasCustomerId === '') {
            $this->markUnmatched($asaasPaymentId ?: null, $asaasCustomerId ?: null, null, 'missing_ids', $payment, $test);
            return 'unmatched';
        }

        $fa = DB::table('financial_asaas')->where('asaas_payment_id', $asaasPaymentId)->first();

        if ($fa) {
            $changed = $this->updateDiffs((int)$fa->financial_id, (int)$fa->id, $payment, $test);
            return $changed ? 'updated' : 'updated';
        }

        [$orderId, $cpf] = $this->resolveOrderId($payment, $asaasCustomerId, $fetchCpf);

        if (!$orderId) {
            $this->markUnmatched($asaasPaymentId, $asaasCustomerId, $cpf, 'order_not_found', $payment, $test);
            return 'unmatched';
        }

        if ($test) return 'created';

        DB::transaction(function () use ($orderId, $asaasPaymentId, $asaasCustomerId, $payment) {
            $financialId = $this->insertFinancial($orderId, $payment);
            $this->insertFinancialAsaas($financialId, $asaasPaymentId, $asaasCustomerId, $payment);

            DB::table('financial_logs')->insert([
                'financial_id' => $financialId,
                'provider' => 'ASAAS',
                'source_type' => 'IMPORT',
                'source_id' => null,
                'event_name' => 'ASAAS_IMPORTED',
                'old_status' => null,
                'new_status' => (string)(data_get($payment, 'status') ?? 'PENDING'),
                'message' => 'Registro criado via reconcile (importação noturna)',
                'payload' => json_encode([
                    'asaas_payment_id' => $asaasPaymentId,
                    'asaas_customer_id' => $asaasCustomerId,
                    'payment' => [
                        'status' => data_get($payment, 'status'),
                        'value' => data_get($payment, 'value'),
                        'billingType' => data_get($payment, 'billingType'),
                        'dueDate' => data_get($payment, 'dueDate'),
                        'externalReference' => data_get($payment, 'externalReference'),
                    ],
                ]),
                'event_date' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return 'created';
    }

    private function updateDiffs(int $financialId, int $financialAsaasId, array $payment, bool $test): bool
    {
        $status = (string) (data_get($payment, 'status') ?? 'PENDING');
        $isPaid = in_array($status, $this->paidStatuses, true);

        $value = (float) (data_get($payment, 'value') ?? 0);

        $dueStr = data_get($payment, 'dueDate');
        $due = $dueStr ? Carbon::parse($dueStr)->format('Y-m-d') : null;
        $chargeDate = $due ? (int) Carbon::parse($due)->day : null;

        $billingType = data_get($payment, 'billingType');
        $paymentMethod = $this->mapPaymentMethod($billingType);

        $current = DB::table('financial')->where('id', $financialId)->first();
        if (!$current) return false;

        $oldStatus = (string)$current->status;
        $incomingStatus = $status;

        $updates = [];

        if ($value > 0 && (float)$current->value !== $value) $updates['value'] = $value;
        if ($due && $current->due_date !== $due) $updates['due_date'] = $due;
        if ($chargeDate && (int)$current->charge_date !== $chargeDate) $updates['charge_date'] = $chargeDate;
        if ($paymentMethod && $current->payment_method !== $paymentMethod) $updates['payment_method'] = $paymentMethod;

        if ($current->status !== $status) $updates['status'] = $status;

        $newChargePaid = $isPaid ? 1 : 0;
        if ((int)$current->charge_paid !== $newChargePaid) $updates['charge_paid'] = $newChargePaid;

        $desiredPaidValue = $isPaid ? $value : null;
        $currentPaidValue = $current->paid_value !== null ? (float)$current->paid_value : null;
        if ($currentPaidValue !== $desiredPaidValue) $updates['paid_value'] = $desiredPaidValue;

        $fa = DB::table('financial_asaas')->where('id', $financialAsaasId)->first();
        $faUpdates = [];

        $ext = data_get($payment, 'externalReference');
        if ($ext && $fa && $fa->external_reference !== $ext) $faUpdates['external_reference'] = $ext;

        $inv = data_get($payment, 'invoiceUrl');
        if ($inv && $fa && $fa->invoice_url !== $inv) $faUpdates['invoice_url'] = $inv;

        $bs = data_get($payment, 'bankSlipUrl');
        if ($bs && $fa && $fa->bank_slip_url !== $bs) $faUpdates['bank_slip_url'] = $bs;

        $pixPayload = data_get($payment, 'pix.payload');
        if ($pixPayload && $fa && $fa->pix_qr_code !== $pixPayload) $faUpdates['pix_qr_code'] = $pixPayload;

        $pixUrl = data_get($payment, 'pix.qrCode.url');
        if ($pixUrl && $fa && $fa->pix_qr_code_url !== $pixUrl) $faUpdates['pix_qr_code_url'] = $pixUrl;

        $changedAnything = !empty($updates) || !empty($faUpdates);

        if (!$changedAnything) {
            return false;
        }

        $fieldsForMsg = implode(', ', array_keys($updates));
        $eventName = $this->eventNameForAsaas($oldStatus, $incomingStatus, true);

        if ($test) {
            return true;
        }

        DB::transaction(function () use ($financialId, $financialAsaasId, $updates, $faUpdates, $oldStatus, $incomingStatus, $eventName, $fieldsForMsg, $payment) {

            $updatesForLog = $updates;
            $updatesForLog['financial_asaas_updates'] = $faUpdates;

            if (!empty($updates)) {
                $updates['updated_at'] = now();
                DB::table('financial')->where('id', $financialId)->update($updates);
            }

            if (!empty($faUpdates)) {
                $faUpdates['updated_at'] = now();
                DB::table('financial_asaas')->where('id', $financialAsaasId)->update($faUpdates);
            }

            DB::table('financial_logs')->insert([
                'financial_id' => $financialId,
                'provider' => 'ASAAS',
                'source_type' => 'IMPORT',
                'source_id' => null,
                'event_name' => $eventName,
                'old_status' => $oldStatus !== $incomingStatus ? $oldStatus : null,
                'new_status' => $oldStatus !== $incomingStatus ? $incomingStatus : null,
                'message' => $oldStatus !== $incomingStatus
                    ? "Reconcile: status {$oldStatus} → {$incomingStatus} (campos: {$fieldsForMsg})"
                    : "Reconcile: atualização (campos: {$fieldsForMsg})",
                'payload' => json_encode([
                    'asaas_payment_id' => data_get($payment, 'id'),
                    'payment_snapshot' => [
                        'status' => data_get($payment, 'status'),
                        'value' => data_get($payment, 'value'),
                        'billingType' => data_get($payment, 'billingType'),
                        'dueDate' => data_get($payment, 'dueDate'),
                        'externalReference' => data_get($payment, 'externalReference'),
                    ],
                    'updates' => $updatesForLog,
                ]),
                'event_date' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return true;
    }

    private function eventNameForAsaas(string $oldStatus, string $newStatus, bool $changedAnything): string
    {
        if (!$changedAnything) return 'UPDATED';

        if ($oldStatus !== $newStatus) {
            return match ($newStatus) {
                'RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH' => 'ASAAS_PAYMENT_RECEIVED',
                'OVERDUE' => 'ASAAS_PAYMENT_OVERDUE',
                'REFUNDED' => 'ASAAS_PAYMENT_REFUNDED',
                default => 'STATUS_CHANGED',
            };
        }

        return 'UPDATED';
    }

    private function resolveOrderId(array $payment, string $asaasCustomerId, bool $fetchCpf): array
    {
        $cpf = null;

        // 1) externalReference: "order:123|..." ou "123"
        $ext = (string) (data_get($payment, 'externalReference') ?? '');
        if ($ext !== '') {
            if (preg_match('/order:(\d+)/', $ext, $m)) {
                $oid = (int)$m[1];
                if (DB::table('orders')->where('id', $oid)->exists()) return [$oid, null];
            }
            $digits = preg_replace('/\D+/', '', $ext);
            if ($digits) {
                $oid = (int)$digits;
                if (DB::table('orders')->where('id', $oid)->exists()) return [$oid, null];
            }
        }

        // 2) client por asaas_customer_id
        $client = Client::where('asaas_customer_id', $asaasCustomerId)->first();

        // 3) opcional: buscar cpf via API e localizar client por cpf
        if (!$client && $fetchCpf) {
            $cpf = $this->getCpfFromAsaasCustomer($asaasCustomerId);
            if ($cpf) {
                $client = Client::where('cpf', $cpf)->first();
                if ($client && !$client->asaas_customer_id) {
                    $client->asaas_customer_id = $asaasCustomerId;
                    $client->save();
                }
            }
        }

        if (!$client) return [null, $cpf];

        $order = Order::where('client_id', $client->id)
            ->orderByRaw("CASE WHEN status = 'ativo' THEN 0 ELSE 1 END")
            ->orderByDesc('updated_at')
            ->first();

        return [$order?->id, $cpf];
    }

    private function insertFinancial(int $orderId, array $payment): int
    {
        $dueStr = data_get($payment, 'dueDate');
        $due = $dueStr ? Carbon::parse($dueStr)->format('Y-m-d') : null;

        $status = (string)(data_get($payment, 'status') ?? 'PENDING');
        $isPaid = in_array($status, $this->paidStatuses, true);

        $value = (float)(data_get($payment, 'value') ?? 0);

        return (int) DB::table('financial')->insertGetId([
            'order_id' => $orderId,
            'value' => $value,
            'paid_value' => $isPaid ? $value : null,
            'charge_date' => $due ? (int) Carbon::parse($due)->day : null,
            'due_date' => $due,
            'payment_method' => $this->mapPaymentMethod(data_get($payment, 'billingType')),
            'description' => data_get($payment, 'description'),
            'obs' => 'Criado via reconcile',
            'charge_paid' => $isPaid ? 1 : 0,
            'status' => $status,
            'created_at' => data_get($payment, 'dateCreated') ?? now(),
            'updated_at' => now(),
        ]);
    }

    private function insertFinancialAsaas(int $financialId, string $asaasPaymentId, string $asaasCustomerId, array $payment): void
    {
        DB::table('financial_asaas')->insert([
            'financial_id' => $financialId,
            'asaas_payment_id' => $asaasPaymentId,
            'asaas_customer_id' => $asaasCustomerId,
            'external_reference' => data_get($payment, 'externalReference'),
            'invoice_url' => data_get($payment, 'invoiceUrl'),
            'bank_slip_url' => data_get($payment, 'bankSlipUrl'),
            'pix_qr_code' => data_get($payment, 'pix.payload'),
            'pix_qr_code_url' => data_get($payment, 'pix.qrCode.url'),
            'created_at' => now(),
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
                'created_at' => now(),
                'updated_at' => now(),
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