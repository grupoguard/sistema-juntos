<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AsaasWebhookController extends Controller
{
    private array $paidStatuses = ['RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH'];

    public function handle(Request $request)
    {
        $expectedToken = (string) config('services.asaas.webhook_token');
        $incomingToken = (string) $request->header('asaas-access-token');

        if ($expectedToken !== '' && !hash_equals($expectedToken, $incomingToken)) {
            return response()->json(['ok' => false, 'error' => 'invalid_token'], 401);
        }

        $payload = $request->all();

        $eventId = data_get($payload, 'id');
        $eventType = data_get($payload, 'event');
        $payment = data_get($payload, 'payment', []);
        $asaasPaymentId = data_get($payment, 'id');
        $asaasCustomerId = data_get($payment, 'customer');

        if (!$eventId || !$eventType) {
            return response()->json(['ok' => false, 'error' => 'invalid_event'], 400);
        }

        $already = DB::table('asaas_webhook_events')
            ->where('event_id', $eventId)
            ->exists();

        if ($already) {
            return response()->json(['ok' => true, 'duplicated' => true], 200);
        }

        DB::transaction(function () use (
            $eventId,
            $eventType,
            $asaasPaymentId,
            $asaasCustomerId,
            $payload,
            $payment
        ) {
            DB::table('asaas_webhook_events')->insert([
                'event_id' => $eventId,
                'event_type' => $eventType,
                'asaas_payment_id' => $asaasPaymentId,
                'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (!$asaasPaymentId) {
                return;
            }

            $fa = DB::table('financial_asaas')
                ->where('asaas_payment_id', $asaasPaymentId)
                ->first();

            if (!$fa) {
                $exists = DB::table('asaas_unmatched_payments')
                    ->where('asaas_payment_id', $asaasPaymentId)
                    ->exists();

                DB::table('asaas_unmatched_payments')->updateOrInsert(
                    ['asaas_payment_id' => $asaasPaymentId],
                    [
                        'asaas_customer_id' => $asaasCustomerId,
                        'cpf' => null,
                        'reason' => 'webhook_payment_not_found',
                        'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                        'updated_at' => now(),
                    ] + ($exists ? [] : ['created_at' => now()])
                );

                return;
            }

            $financialId = (int) $fa->financial_id;

            $current = DB::table('financial')->where('id', $financialId)->first();
            if (!$current) {
                return;
            }

            $newStatus = (string) (data_get($payment, 'status') ?? $current->status ?? 'PENDING');
            $newValue = data_get($payment, 'value');
            $paidValueFromAsaas = data_get($payment, 'paidValue');

            $dueDateStr = data_get($payment, 'dueDate');
            $newDueDate = $dueDateStr ? Carbon::parse($dueDateStr)->format('Y-m-d') : null;
            $newChargeDate = $newDueDate ? (int) Carbon::parse($newDueDate)->day : null;

            $billingType = data_get($payment, 'billingType');
            $newPaymentMethod = $this->mapPaymentMethod($billingType);

            $isPaid = in_array($newStatus, $this->paidStatuses, true);

            $updates = [];

            if ($current->status !== $newStatus) {
                $updates['status'] = $newStatus;
            }

            if ($newValue !== null && (float) $current->value !== (float) $newValue) {
                $updates['value'] = (float) $newValue;
            }

            if ($newDueDate && $current->due_date !== $newDueDate) {
                $updates['due_date'] = $newDueDate;
            }

            if ($newChargeDate && (int) $current->charge_date !== (int) $newChargeDate) {
                $updates['charge_date'] = $newChargeDate;
            }

            if ($newPaymentMethod !== null && $current->payment_method !== $newPaymentMethod) {
                $updates['payment_method'] = $newPaymentMethod;
            }

            $desiredChargePaid = $isPaid ? 1 : 0;
            if ((int) $current->charge_paid !== $desiredChargePaid) {
                $updates['charge_paid'] = $desiredChargePaid;
            }

            $desiredPaidValue = $isPaid
                ? (float) ($paidValueFromAsaas ?? $newValue ?? $current->value)
                : null;

            $currentPaidValue = $current->paid_value !== null ? (float) $current->paid_value : null;
            if ($currentPaidValue !== $desiredPaidValue) {
                $updates['paid_value'] = $desiredPaidValue;
            }

            if (array_key_exists('description', $payment) && $current->description !== data_get($payment, 'description')) {
                $updates['description'] = data_get($payment, 'description');
            }

            $oldStatus = (string) $current->status;
            $changedAnything = !empty($updates);

            if ($changedAnything) {
                $updates['updated_at'] = now();
                DB::table('financial')->where('id', $financialId)->update($updates);
            }

            $faUpdates = [];

            $externalReference = data_get($payment, 'externalReference');
            if ($externalReference && $fa->external_reference !== $externalReference) {
                $faUpdates['external_reference'] = $externalReference;
            }

            $invoiceUrl = data_get($payment, 'invoiceUrl');
            if ($invoiceUrl && $fa->invoice_url !== $invoiceUrl) {
                $faUpdates['invoice_url'] = $invoiceUrl;
            }

            $bankSlipUrl = data_get($payment, 'bankSlipUrl');
            if ($bankSlipUrl && $fa->bank_slip_url !== $bankSlipUrl) {
                $faUpdates['bank_slip_url'] = $bankSlipUrl;
            }

            $pixQrCode = data_get($payment, 'pixQrCode') ?? data_get($payment, 'pix.payload');
            if ($pixQrCode && $fa->pix_qr_code !== $pixQrCode) {
                $faUpdates['pix_qr_code'] = $pixQrCode;
            }

            $pixQrCodeUrl = data_get($payment, 'pixQrCodeUrl') ?? data_get($payment, 'pix.qrCode.url');
            if ($pixQrCodeUrl && $fa->pix_qr_code_url !== $pixQrCodeUrl) {
                $faUpdates['pix_qr_code_url'] = $pixQrCodeUrl;
            }

            if ($asaasCustomerId && $fa->asaas_customer_id !== $asaasCustomerId) {
                $faUpdates['asaas_customer_id'] = $asaasCustomerId;
            }

            if (!empty($faUpdates)) {
                $faUpdates['updated_at'] = now();
                DB::table('financial_asaas')->where('id', $fa->id)->update($faUpdates);
            }

            DB::table('financial_logs')->insert([
                'financial_id' => $financialId,
                'provider' => 'ASAAS',
                'source_type' => 'WEBHOOK',
                'source_id' => null,
                'event_name' => $this->eventNameForWebhook($eventType, $oldStatus, $newStatus, $changedAnything),
                'old_status' => $oldStatus !== $newStatus ? $oldStatus : null,
                'new_status' => $oldStatus !== $newStatus ? $newStatus : null,
                'message' => $this->messageForWebhook($eventType, $oldStatus, $newStatus, $changedAnything),
                'payload' => json_encode([
                    'event_id' => $eventId,
                    'event_type' => $eventType,
                    'asaas_payment_id' => $asaasPaymentId,
                    'financial_updates' => $updates,
                    'financial_asaas_updates' => $faUpdates,
                    'payment' => $payment,
                ], JSON_UNESCAPED_UNICODE),
                'event_date' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return response()->json(['ok' => true], 200);
    }

    private function mapPaymentMethod(?string $billingType): ?string
    {
        return match ($billingType) {
            'PIX' => 'PIX',
            'CREDIT_CARD' => 'CREDIT_CARD',
            'DEBIT_CARD' => 'DEBIT_CARD',
            'BOLETO' => 'BOLETO',
            'UNDEFINED' => 'BOLETO',
            default => null,
        };
    }

    private function eventNameForWebhook(
        string $eventType,
        string $oldStatus,
        string $newStatus,
        bool $changedAnything
    ): string {
        return match ($eventType) {
            'PAYMENT_RECEIVED', 'PAYMENT_CONFIRMED' => 'ASAAS_PAYMENT_RECEIVED',
            'PAYMENT_OVERDUE' => 'ASAAS_PAYMENT_OVERDUE',
            'PAYMENT_DELETED' => 'ASAAS_PAYMENT_DELETED',
            'PAYMENT_RESTORED' => 'ASAAS_PAYMENT_RESTORED',
            'PAYMENT_REFUNDED', 'PAYMENT_PARTIALLY_REFUNDED', 'PAYMENT_REFUND_IN_PROGRESS', 'PAYMENT_REFUND_DENIED' => 'ASAAS_PAYMENT_REFUNDED',
            'PAYMENT_RECEIVED_IN_CASH_UNDONE' => 'ASAAS_PAYMENT_RECEIVED_IN_CASH_UNDONE',
            'PAYMENT_BANK_SLIP_CANCELLED' => 'ASAAS_PAYMENT_BANK_SLIP_CANCELLED',
            default => $oldStatus !== $newStatus ? 'STATUS_CHANGED' : ($changedAnything ? 'UPDATED' : 'WEBHOOK_RECEIVED'),
        };
    }

    private function messageForWebhook(
        string $eventType,
        string $oldStatus,
        string $newStatus,
        bool $changedAnything
    ): string {
        if ($oldStatus !== $newStatus) {
            return "Webhook Asaas: {$eventType} | status {$oldStatus} → {$newStatus}";
        }

        if ($changedAnything) {
            return "Webhook Asaas: {$eventType} | atualização de cobrança";
        }

        return "Webhook Asaas: {$eventType} | evento recebido sem alteração material local";
    }
}